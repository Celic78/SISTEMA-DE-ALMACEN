<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Exceptions\DuplicateSolicitudException;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\SolicitudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SolicitudServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SolicitudService $solicitudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->solicitudService = app(SolicitudService::class);
    }

    public function test_obtener_espacios_por_edificio_retorna_solo_espacios_de_ese_edificio(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Central', 'direccion' => 'Av. Universidad 123']);
        $edificio1 = $plantel->edificios()->create(['nombre' => 'Edificio 1']);
        $edificio2 = $plantel->edificios()->create(['nombre' => 'Edificio 2']);

        $edificio1->espacios()->create(['nombre_espacio' => 'Aula 101', 'tipo_espacio' => 'Aula', 'piso' => 'Piso 1']);
        $edificio1->espacios()->create(['nombre_espacio' => 'Aula 102', 'tipo_espacio' => 'Aula', 'piso' => 'Piso 1']);
        $edificio2->espacios()->create(['nombre_espacio' => 'Cafetería', 'tipo_espacio' => 'Cafetería', 'piso' => 'Planta Baja']);

        $espaciosEdificio1 = $this->solicitudService->obtenerEspaciosPorEdificio($edificio1->id_edificio);

        $this->assertCount(2, $espaciosEdificio1);
        $this->assertTrue($espaciosEdificio1->contains('nombre_espacio', 'Aula 101'));
        $this->assertTrue($espaciosEdificio1->contains('nombre_espacio', 'Aula 102'));
        $this->assertFalse($espaciosEdificio1->contains('nombre_espacio', 'Cafetería'));
    }

    public function test_validar_ubicacion_jerarquica_permite_espacio_valido(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Norte', 'direccion' => 'Av. Norte 500']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio Ciencias']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Lab Química', 'tipo_espacio' => 'Laboratorio', 'piso' => 'Planta Baja']);

        $esValido = $this->solicitudService->validarUbicacionJerarquica(
            $edificio->id_edificio,
            $espacio->id_espacio
        );

        $this->assertTrue($esValido);
    }

    public function test_validar_ubicacion_jerarquica_rechaza_espacio_de_otro_edificio(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Poniente', 'direccion' => 'Periférico Sur']);
        $edificioA = $plantel->edificios()->create(['nombre' => 'Edificio A']);
        $edificioB = $plantel->edificios()->create(['nombre' => 'Edificio B']);

        $espacioDeB = $edificioB->espacios()->create(['nombre_espacio' => 'Auditorio', 'tipo_espacio' => 'Auditorio', 'piso' => 'Planta Baja']);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Ubicación inválida');

        // Intento de asociar espacio de Edificio B con Edificio A
        $this->solicitudService->validarUbicacionJerarquica(
            $edificioA->id_edificio,
            $espacioDeB->id_espacio
        );
    }

    public function test_crear_solicitud_guarda_exitosamente_y_genera_orden_de_trabajo_inicial(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Oriente', 'direccion' => 'Av. Oriente']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio 3']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Baño Damas Piso 1', 'tipo_espacio' => 'Baño', 'piso' => 'Piso 1']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Plomería']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Alta', 'tiempo_respuesta_hrs' => 12]);
        $alumno = User::factory()->create(['email_tipo' => 'alumno']);

        $solicitud = $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Fuga de agua en lavabo 2',
        ], $alumno);

        $this->assertInstanceOf(Solicitud::class, $solicitud);
        $this->assertSame('pendiente', $solicitud->estado);
        $this->assertDatabaseHas('solicitudes', [
            'id_solicitud' => $solicitud->id_solicitud,
            'id_usuario' => $alumno->id,
            'descripcion_falla' => 'Fuga de agua en lavabo 2',
        ]);

        // Verifica que se haya generado la orden de trabajo inicial vinculada
        $this->assertDatabaseHas('ordenes_trabajo', [
            'id_solicitud' => $solicitud->id_solicitud,
            'estado' => 'abierta',
        ]);

        // Verifica auditoría en bitácora de sistema
        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $alumno->id,
            'accion' => 'SOLICITUD_CREADA',
        ]);
    }

    public function test_crear_solicitud_aplica_regla_anti_duplicados_para_mismo_usuario_y_ubicacion(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Central', 'direccion' => 'Av. Principal']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio 2']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Aula 204', 'tipo_espacio' => 'Aula', 'piso' => 'Piso 2']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Electricidad']);
        $profesor = User::factory()->create(['email_tipo' => 'profesor']);

        // Primer reporte
        $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Falla en el proyector y contactos sin luz',
        ], $profesor);

        // Segundo reporte idéntico mientras el primero sigue pendiente
        $this->expectException(DuplicateSolicitudException::class);
        $this->expectExceptionMessage('Ya cuenta con un reporte registrado');

        $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Proyector sigue sin encender',
        ], $profesor);
    }

    public function test_solicitud_permite_nuevo_reporte_si_el_anterior_ya_fue_resuelto(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Sur', 'direccion' => 'Av. Universidad']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio Taller']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Taller Soldadura', 'tipo_espacio' => 'Taller', 'piso' => 'Planta Baja']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Mantenimiento General']);
        $alumno = User::factory()->create(['email_tipo' => 'alumno']);

        // Reporte previo resuelto en el pasado
        Solicitud::create([
            'id_usuario' => $alumno->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Puerta trabada resuelta hace semanas',
            'fecha_hallazgo' => now()->subWeeks(2),
            'estado' => 'resuelta',
        ]);

        // Un nuevo reporte debe permitirse ya que el anterior no está activo
        $nuevaSolicitud = $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Nueva falla en bisagra de puerta',
        ], $alumno);

        $this->assertInstanceOf(Solicitud::class, $nuevaSolicitud);
    }

    public function test_crear_solicitud_procesa_archivo_de_evidencia_adjunta(): void
    {
        Storage::fake('public');

        $plantel = Plantel::create(['nombre' => 'Campus Virtual', 'direccion' => 'Online']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio 5']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Sala 1', 'tipo_espacio' => 'Sala', 'piso' => 'Planta Baja']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Iluminación']);
        $usuario = User::factory()->create();

        $archivoFoto = UploadedFile::fake()->image('falla_lampara.png', 800, 600);

        $solicitud = $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Lámpara parpadeando',
            'archivo_evidencia' => $archivoFoto,
        ], $usuario);

        $this->assertNotNull($solicitud->evidencia_url);
        $this->assertStringContainsString('evidencias/solicitudes', $solicitud->evidencia_url);
    }
}
