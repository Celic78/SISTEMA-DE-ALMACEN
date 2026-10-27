<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\HistorialEstado;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\ReporteFinal;
use App\Models\Solicitud;
use App\Models\TareaOrden;
use App\Models\User;
use App\Services\TecnicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TecnicoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TecnicoService $tecnicoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tecnicoService = app(TecnicoService::class);
    }

    public function test_iniciar_orden_con_compromiso_actualiza_fecha_inicio_estado_y_sla(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'monto_estimado' => 450.00,
            'requiere_aprobacion' => false,
            'estado' => 'asignada',
        ]);

        $fechaInicio = Carbon::create(2026, 10, 27, 8, 30, 0);

        $ordenActualizada = $this->tecnicoService->iniciarOrdenConCompromiso(
            $orden,
            $tecnico,
            4, // 4 horas de SLA estimado
            $fechaInicio,
            'Se inicia diagnóstico del cableado en sitio'
        );

        $this->assertSame('en_proceso', $ordenActualizada->estado);
        $this->assertEquals($fechaInicio, $ordenActualizada->fecha_inicio);
        $this->assertSame('en_proceso', $solicitud->fresh()->estado);

        $this->assertDatabaseHas('historial_estados', [
            'id_orden' => $orden->id_orden,
            'estado_nuevo' => 'en_proceso',
            'id_usuario_cambio' => $tecnico->id,
        ]);

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $tecnico->id,
            'accion' => 'ORDEN_INICIADA_SLA',
        ]);
    }

    public function test_iniciar_orden_lanza_excepcion_si_orden_requiere_aprobacion_y_no_esta_aprobada(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'monto_estimado' => 19500.00,
            'requiere_aprobacion' => true,
            'estado' => 'requiere_aprobacion',
        ]);

        $this->expectException(ApprovalRequiredException::class);
        $this->expectExceptionMessage('requiere aprobación previa de Coordinación');

        $this->tecnicoService->iniciarOrdenConCompromiso(
            $orden,
            $tecnico,
            6
        );
    }

    public function test_iniciar_orden_rechaza_tecnico_ajeno_no_asignado(): void
    {
        $tecnicoAsignado = User::factory()->create(['email_tipo' => 'tecnico']);
        $tecnicoInvasor = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnicoAsignado->id,
            'monto_estimado' => 300.00,
            'requiere_aprobacion' => false,
            'estado' => 'asignada',
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('No está asignado como técnico responsable');

        $this->tecnicoService->iniciarOrdenConCompromiso(
            $orden,
            $tecnicoInvasor,
            3
        );
    }

    public function test_cerrar_orden_formal_exige_evidencias_y_rechaza_cierre_vacio(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'estado' => 'en_proceso',
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Es obligatorio adjuntar al menos una evidencia');

        // Intento de cerrar únicamente presionando el botón sin evidencias
        $this->tecnicoService->cerrarOrdenFormal(
            $orden,
            $tecnico,
            now(),
            []
        );
    }

    public function test_cerrar_orden_formal_rechaza_cierre_con_tareas_pendientes(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'estado' => 'en_proceso',
        ]);

        $orden->tareas()->create([
            'descripcion_tarea' => 'Prueba de voltaje final',
            'completada' => false, // Tarea pendiente
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('tareas pendientes');

        $this->tecnicoService->cerrarOrdenFormal(
            $orden,
            $tecnico,
            now(),
            [
                ['tipo' => 'fotografia', 'url_archivo' => 'https://example.com/foto1.jpg'],
            ]
        );
    }

    public function test_cerrar_orden_formal_exitoso_cambia_estado_a_resuelta_y_sincroniza_solicitud(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'estado' => 'en_proceso',
        ]);

        $tarea = $orden->tareas()->create([
            'descripcion_tarea' => 'Cambio de balastro',
            'completada' => true,
        ]);

        $fechaFin = Carbon::create(2026, 10, 27, 12, 0, 0);

        $ordenCerrada = $this->tecnicoService->cerrarOrdenFormal(
            $orden,
            $tecnico,
            $fechaFin,
            [
                [
                    'tipo' => 'fotografia',
                    'url_archivo' => 'https://storage.univ.edu.mx/evidencias/cierre_01.jpg',
                    'descripcion' => 'Foto de lámpara funcionando',
                ],
            ],
            'Trabajo concluido en su totalidad. Lámparas operativas.'
        );

        $this->assertSame('resuelta', $ordenCerrada->estado);
        $this->assertEquals($fechaFin, $ordenCerrada->fecha_fin);
        $this->assertSame('resuelta', $solicitud->fresh()->estado);

        $this->assertDatabaseHas('evidencias_cierre', [
            'id_orden' => $orden->id_orden,
            'tipo' => 'fotografia',
        ]);

        $this->assertDatabaseHas('reportes_finales', [
            'id_orden' => $orden->id_orden,
            'resumen_ejecutivo' => 'Trabajo concluido en su totalidad. Lámparas operativas.',
        ]);

        $this->assertDatabaseHas('historial_estados', [
            'id_orden' => $orden->id_orden,
            'estado_nuevo' => 'resuelta',
            'id_usuario_cambio' => $tecnico->id,
        ]);
    }

    public function test_toggle_tarea_permite_marcar_y_desmarcar_checklist(): void
    {
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'estado' => 'en_proceso',
        ]);

        $tarea = $this->tecnicoService->agregarTarea($orden, 'Revisar fusibles');
        $this->assertFalse($tarea->completada);

        $tareaCompletada = $this->tecnicoService->toggleTarea($tarea, $tecnico, true);
        $this->assertTrue($tareaCompletada->completada);
    }

    protected function crearSolicitudPrueba(): Solicitud
    {
        $plantel = Plantel::firstOrCreate(['nombre' => 'Plantel Central'], ['direccion' => 'Calle 1']);
        $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->firstOrCreate(
            ['nombre_espacio' => 'Laboratorio 1'],
            ['tipo_espacio' => 'Laboratorio', 'piso' => 'Planta Baja']
        );
        $categoria = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Electricidad']);
        $prioridad = MatrizPrioridad::firstOrCreate(['nivel' => 'Media'], ['tiempo_respuesta_hrs' => 24]);
        $user = User::factory()->create();

        return Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Cortocircuito en mesa 4',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);
    }
}
