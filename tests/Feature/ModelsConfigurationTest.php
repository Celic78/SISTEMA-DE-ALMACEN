<?php

namespace Tests\Feature;

use App\Models\Aprobacion;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\EvidenciaCierre;
use App\Models\Existencia;
use App\Models\HistorialEstado;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\MovimientoMaterial;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\ReporteFinal;
use App\Models\Rol;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\Subcategoria;
use App\Models\TareaOrden;
use App\Models\User;
use App\Models\UsuarioRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_models_have_correct_table_and_primary_keys(): void
    {
        $this->assertSame('planteles', (new Plantel)->getTable());
        $this->assertSame('id_plantel', (new Plantel)->getKeyName());

        $this->assertSame('edificios', (new Edificio)->getTable());
        $this->assertSame('id_edificio', (new Edificio)->getKeyName());

        $this->assertSame('espacios', (new Espacio)->getTable());
        $this->assertSame('id_espacio', (new Espacio)->getKeyName());

        $this->assertSame('users', (new User)->getTable());
        $this->assertSame('id', (new User)->getKeyName());

        $this->assertSame('roles', (new Rol)->getTable());
        $this->assertSame('id_rol', (new Rol)->getKeyName());

        $this->assertSame('usuario_rol', (new UsuarioRol)->getTable());

        $this->assertSame('categorias_solicitud', (new CategoriaSolicitud)->getTable());
        $this->assertSame('id_categoria', (new CategoriaSolicitud)->getKeyName());

        $this->assertSame('subcategorias', (new Subcategoria)->getTable());
        $this->assertSame('id_subcategoria', (new Subcategoria)->getKeyName());

        $this->assertSame('matrices_prioridad', (new MatrizPrioridad)->getTable());
        $this->assertSame('id_prioridad', (new MatrizPrioridad)->getKeyName());

        $this->assertSame('solicitudes', (new Solicitud)->getTable());
        $this->assertSame('id_solicitud', (new Solicitud)->getKeyName());

        $this->assertSame('ordenes_trabajo', (new OrdenTrabajo)->getTable());
        $this->assertSame('id_orden', (new OrdenTrabajo)->getKeyName());

        $this->assertSame('tareas_orden', (new TareaOrden)->getTable());
        $this->assertSame('id_tarea', (new TareaOrden)->getKeyName());

        $this->assertSame('aprobaciones', (new Aprobacion)->getTable());
        $this->assertSame('id_aprobacion', (new Aprobacion)->getKeyName());

        $this->assertSame('secciones_almacen', (new SeccionAlmacen)->getTable());
        $this->assertSame('id_seccion', (new SeccionAlmacen)->getKeyName());

        $this->assertSame('materiales', (new Material)->getTable());
        $this->assertSame('id_material', (new Material)->getKeyName());

        $this->assertSame('existencias', (new Existencia)->getTable());
        $this->assertSame('id_existencia', (new Existencia)->getKeyName());

        $this->assertSame('movimientos_material', (new MovimientoMaterial)->getTable());
        $this->assertSame('id_movimiento', (new MovimientoMaterial)->getKeyName());

        $this->assertSame('historial_estados', (new HistorialEstado)->getTable());
        $this->assertSame('id_historial', (new HistorialEstado)->getKeyName());

        $this->assertSame('evidencias_cierre', (new EvidenciaCierre)->getTable());
        $this->assertSame('id_evidencia', (new EvidenciaCierre)->getKeyName());

        $this->assertSame('bitacora_sistema', (new BitacoraSistema)->getTable());
        $this->assertSame('id_bitacora', (new BitacoraSistema)->getKeyName());

        $this->assertSame('reportes_finales', (new ReporteFinal)->getTable());
        $this->assertSame('id_reporte', (new ReporteFinal)->getKeyName());
    }

    public function test_relationships_work_with_persisted_records(): void
    {
        $plantel = Plantel::create([
            'nombre' => 'Plantel Central',
            'direccion' => 'Av. Universidad 123',
        ]);
        $this->assertDatabaseHas('planteles', ['nombre' => 'Plantel Central']);

        $edificio = $plantel->edificios()->create([
            'nombre' => 'Edificio A',
            'descripcion_espacios' => 'Aulas y laboratorios',
        ]);
        $this->assertEquals($plantel->id_plantel, $edificio->plantel->id_plantel);

        $espacio = $edificio->espacios()->create([
            'nombre_espacio' => 'Laboratorio de Cómputo 1',
            'tipo_espacio' => 'Laboratorio',
            'piso' => 'Planta Baja',
        ]);
        $this->assertEquals($edificio->id_edificio, $espacio->edificio->id_edificio);

        $user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@alumnos.edu.mx',
            'password' => 'secret123',
            'email_tipo' => 'alumno',
            'id_espacio' => $espacio->id_espacio,
        ]);
        $this->assertEquals($espacio->id_espacio, $user->espacio->id_espacio);

        $rol = Rol::create([
            'nombre_rol' => 'Tecnico',
        ]);
        $user->roles()->attach($rol->id_rol, [
            'turno_tecnico' => 'Matutino',
            'especialidad_tecnico' => 'Electricidad',
        ]);
        $this->assertTrue($user->hasRol('Tecnico'));

        $categoria = CategoriaSolicitud::create([
            'nombre_categoria' => 'Electricidad',
            'descripcion' => 'Fallas eléctricas',
        ]);

        $subcategoria = $categoria->subcategorias()->create([
            'nombre' => 'Contacto dañado',
        ]);
        $this->assertEquals($categoria->id_categoria, $subcategoria->categoria->id_categoria);

        $prioridad = MatrizPrioridad::create([
            'nivel' => 'Alta',
            'tiempo_respuesta_hrs' => 24,
        ]);

        $solicitud = Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Cortocircuito en aula',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);
        $this->assertEquals($user->id, $solicitud->usuario->id);
        $this->assertEquals($espacio->id_espacio, $solicitud->espacio->id_espacio);

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $user->id,
            'monto_estimado' => 250.50,
            'requiere_aprobacion' => false,
            'estado' => 'abierta',
        ]);
        $this->assertEquals($solicitud->id_solicitud, $orden->solicitud->id_solicitud);
        $this->assertEquals($user->id, $orden->tecnico->id);

        $tarea = $orden->tareas()->create([
            'descripcion_tarea' => 'Reemplazar cableado y pastilla térmica',
            'completada' => false,
        ]);
        $this->assertEquals($orden->id_orden, $tarea->ordenTrabajo->id_orden);

        $seccion = SeccionAlmacen::create([
            'nombre_seccion' => 'Pasillo Eléctrico',
            'ubicacion_almacen' => 'Rack 3-B',
        ]);

        $material = $seccion->materiales()->create([
            'nombre_material' => 'Cable Calibre 12',
            'especificacion' => 'Cobre uso rudo',
            'unidad_medida' => 'Metros',
            'codigo_qr_barras' => 'CBL-CAL12-001',
        ]);
        $this->assertEquals($seccion->id_seccion, $material->seccion->id_seccion);

        $existencia = $material->existencia()->create([
            'cantidad_disponible' => 150.00,
            'stock_minimo' => 20.00,
        ]);
        $this->assertEquals($material->id_material, $existencia->material->id_material);

        $movimiento = $material->movimientos()->create([
            'id_orden' => $orden->id_orden,
            'id_usuario_almacen' => $user->id,
            'tipo_movimiento' => 'salida',
            'cantidad' => 10.00,
            'fecha_movimiento' => now(),
        ]);
        $this->assertEquals($material->id_material, $movimiento->material->id_material);
        $this->assertEquals($orden->id_orden, $movimiento->ordenTrabajo->id_orden);
    }
}
