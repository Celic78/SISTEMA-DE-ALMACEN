<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\InsufficientStockException;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\AlmacenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlmacenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AlmacenService $almacenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->almacenService = app(AlmacenService::class);
    }

    public function test_registrar_preaviso_coordinacion_guarda_notificacion_en_bitacora(): void
    {
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $orden = $this->crearOrdenPrueba();

        $this->almacenService->registrarPreavisoCoordinacion(
            $orden,
            $coordinador,
            now()->addDay(),
            'Se requiere tener listas 3 cubetas de pintura vinílica y brochas'
        );

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $coordinador->id,
            'accion' => 'PRENOTIFICACION_DESPACHO_ALMACEN',
        ]);
    }

    public function test_despachar_materiales_bloquea_orden_que_requiere_aprobacion_no_aprobada(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $orden = $this->crearOrdenPrueba(montoEstimado: 25000.00, requiereAprobacion: true, estado: 'requiere_aprobacion');

        $material = $this->crearMaterialConStock('Cable Uso Rudo 3x10', 50.00);

        $this->expectException(ApprovalRequiredException::class);
        $this->expectExceptionMessage('requiere visto bueno previo de Coordinación');

        $this->almacenService->despacharMateriales($orden, $usuarioAlmacen, [
            ['id_material' => $material->id_material, 'cantidad' => 15.00],
        ]);
    }

    public function test_despachar_materiales_permite_despacho_si_orden_fue_aprobada(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        // Orden aprobada formalmente
        $orden = $this->crearOrdenPrueba(montoEstimado: 25000.00, requiereAprobacion: true, estado: 'aprobada');
        $material = $this->crearMaterialConStock('Pintura Blanca Comex', 20.00);

        $movimientos = $this->almacenService->despacharMateriales($orden, $usuarioAlmacen, [
            ['id_material' => $material->id_material, 'cantidad' => 5.00],
        ]);

        $this->assertCount(1, $movimientos);
        $this->assertEquals(15.00, (float) $material->existencia->fresh()->cantidad_disponible);
    }

    public function test_despachar_materiales_falla_por_stock_insuficiente(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $orden = $this->crearOrdenPrueba();
        $material = $this->crearMaterialConStock('Llave Stilson 14 pulg', 2.00); // Solo 2 disponibles

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Stock insuficiente para');

        // Intento de solicitar 5 cuando solo hay 2
        $this->almacenService->despacharMateriales($orden, $usuarioAlmacen, [
            ['id_material' => $material->id_material, 'cantidad' => 5.00],
        ]);
    }

    public function test_despachar_materiales_descuenta_existencias_en_tiempo_real_y_registra_kardex(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $orden = $this->crearOrdenPrueba();

        $cemento = $this->crearMaterialConStock('Bulto Cemento Gris 50kg', 40.00, stockMinimo: 10.00);
        $arena = $this->crearMaterialConStock('Metro Arena de Río', 12.00, stockMinimo: 2.00);

        $movimientos = $this->almacenService->despacharMateriales($orden, $usuarioAlmacen, [
            ['id_material' => $cemento->id_material, 'cantidad' => 10.00],
            ['id_material' => $arena->id_material, 'cantidad' => 2.00],
        ]);

        $this->assertCount(2, $movimientos);

        // Descuentos en tiempo real
        $this->assertEquals(30.00, (float) $cemento->existencia->fresh()->cantidad_disponible);
        $this->assertEquals(10.00, (float) $arena->existencia->fresh()->cantidad_disponible);

        // Registros en Kardex (movimientos_material)
        $this->assertDatabaseHas('movimientos_material', [
            'id_material' => $cemento->id_material,
            'id_orden' => $orden->id_orden,
            'tipo_movimiento' => 'salida',
            'cantidad' => 10.00,
        ]);

        $this->assertDatabaseHas('movimientos_material', [
            'id_material' => $arena->id_material,
            'id_orden' => $orden->id_orden,
            'tipo_movimiento' => 'salida',
            'cantidad' => 2.00,
        ]);
    }

    public function test_registrar_entrada_stock_incrementa_existencias_y_registra_movimiento_entrada(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $material = $this->crearMaterialConStock('Lámpara LED 60W', 10.00);

        $movimiento = $this->almacenService->registrarEntradaStock(
            $material->id_material,
            25.00,
            $usuarioAlmacen,
            'Compra de reposición semestral con proveedor'
        );

        $this->assertSame('entrada', $movimiento->tipo_movimiento);
        $this->assertEquals(25.00, (float) $movimiento->cantidad);
        $this->assertEquals(35.00, (float) $material->existencia->fresh()->cantidad_disponible);

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $usuarioAlmacen->id,
            'accion' => 'ENTRADA_STOCK_ALMACEN',
        ]);
    }

    public function test_obtener_materiales_bajo_stock_minimo_detecta_alertas(): void
    {
        // Material con existencia crítica (3 <= stock_minimo 5)
        $critico = $this->crearMaterialConStock('Cloro concentrado 20L', 3.00, stockMinimo: 5.00);

        // Material con existencia suficiente (50 > stock_minimo 10)
        $abundante = $this->crearMaterialConStock('Rollo Cable Calibre 12', 50.00, stockMinimo: 10.00);

        $alertas = $this->almacenService->obtenerMaterialesBajoStockMinimo();

        $this->assertTrue($alertas->contains('id_material', $critico->id_material));
        $this->assertFalse($alertas->contains('id_material', $abundante->id_material));
    }

    protected function crearOrdenPrueba(float $montoEstimado = 200.00, bool $requiereAprobacion = false, string $estado = 'abierta'): OrdenTrabajo
    {
        $plantel = Plantel::firstOrCreate(['nombre' => 'Plantel Central'], ['direccion' => 'Calle 1']);
        $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->firstOrCreate(
            ['nombre_espacio' => 'Almacén General'],
            ['tipo_espacio' => 'Almacén', 'piso' => 'Planta Baja']
        );
        $categoria = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Insumos']);
        $prioridad = MatrizPrioridad::firstOrCreate(['nivel' => 'Media'], ['tiempo_respuesta_hrs' => 24]);
        $user = User::factory()->create();

        $solicitud = Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Solicitud de materiales para mantenimiento',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        return $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $user->id,
            'monto_estimado' => $montoEstimado,
            'requiere_aprobacion' => $requiereAprobacion,
            'estado' => $estado,
        ]);
    }

    protected function crearMaterialConStock(string $nombre, float $disponible, float $stockMinimo = 5.00): Material
    {
        $seccion = SeccionAlmacen::firstOrCreate(
            ['nombre_seccion' => 'Pasillo General'],
            ['ubicacion_almacen' => 'Rack A']
        );

        $material = $seccion->materiales()->create([
            'nombre_material' => $nombre,
            'especificacion' => 'Insumo institucional',
            'unidad_medida' => 'Piezas',
            'codigo_qr_barras' => 'COD-'.strtoupper(uniqid()),
        ]);

        $material->existencia()->create([
            'cantidad_disponible' => $disponible,
            'stock_minimo' => $stockMinimo,
        ]);

        return $material;
    }
}
