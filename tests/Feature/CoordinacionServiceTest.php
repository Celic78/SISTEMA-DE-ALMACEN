<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Aprobacion;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\HistorialEstado;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\CoordinacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CoordinacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CoordinacionService $coordinacionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coordinacionService = app(CoordinacionService::class);
    }

    public function test_consolidar_reportes_duplicados_vincula_hijas_a_solicitud_padre(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Central', 'direccion' => 'Av. Universidad']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->create([
            'nombre_espacio' => 'Baño Hombres Planta Baja',
            'tipo_espacio' => 'Baño',
            'piso' => 'Planta Baja',
        ]);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Plomería']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Alta', 'tiempo_respuesta_hrs' => 12]);

        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $usuarioA = User::factory()->create();
        $usuarioB = User::factory()->create();
        $usuarioC = User::factory()->create();

        // 3 reportes que corresponden a la misma incidencia física
        $padre = Solicitud::create([
            'id_usuario' => $usuarioA->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Fuga de agua en lavamanos central',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $hija1 = Solicitud::create([
            'id_usuario' => $usuarioB->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Hay mucha agua tirándose en el baño de hombres',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $hija2 = Solicitud::create([
            'id_usuario' => $usuarioC->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Tubería rota en baño 1',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $totalConsolidados = $this->coordinacionService->consolidarReportesDuplicados(
            $padre,
            [$hija1->id_solicitud, $hija2->id_solicitud],
            $coordinador
        );

        $this->assertSame(2, $totalConsolidados);
        $this->assertSame($padre->id_solicitud, $hija1->fresh()->id_solicitud_padre);
        $this->assertSame('agrupada', $hija1->fresh()->estado);
        $this->assertSame($padre->id_solicitud, $hija2->fresh()->id_solicitud_padre);
        $this->assertSame('agrupada', $hija2->fresh()->estado);

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $coordinador->id,
            'accion' => 'CONSOLIDACION_DUPLICADOS',
        ]);
    }

    public function test_sincronizar_estado_solicitudes_hijas_propaga_estado_en_cadena(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Norte', 'direccion' => 'Av. Norte']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio A']);
        $espacio = $edificio->espacios()->create([
            'nombre_espacio' => 'Aula Magna',
            'tipo_espacio' => 'Auditorio',
            'piso' => 'Planta Baja',
        ]);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Climatización']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Media', 'tiempo_respuesta_hrs' => 24]);
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);

        $padre = Solicitud::create([
            'id_usuario' => $coordinador->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Aire acondicionado no enfría',
            'fecha_hallazgo' => now(),
            'estado' => 'en_proceso',
        ]);

        $hija = Solicitud::create([
            'id_usuario' => User::factory()->create()->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'id_solicitud_padre' => $padre->id_solicitud,
            'descripcion_falla' => 'Mucho calor en aula magna',
            'fecha_hallazgo' => now(),
            'estado' => 'agrupada',
        ]);

        // Propagación en cadena cuando la orden principal se resuelve
        $actualizadas = $this->coordinacionService->sincronizarEstadoSolicitudesHijas(
            $padre,
            'resuelta',
            $coordinador,
            'Reparación de compresor completada'
        );

        $this->assertSame(1, $actualizadas);
        $this->assertSame('resuelta', $hija->fresh()->estado);
    }

    public function test_evaluar_requiere_aprobacion_por_monto_limite_financiero(): void
    {
        // Superior o igual a $15,000 requiere visto bueno formal
        $this->assertTrue($this->coordinacionService->evaluarRequiereAprobacion(15000.00));
        $this->assertTrue($this->coordinacionService->evaluarRequiereAprobacion(32500.00));

        // Menor a $15,000 no requiere aprobación a menos que sea de alto riesgo
        $this->assertFalse($this->coordinacionService->evaluarRequiereAprobacion(14999.99));
        $this->assertFalse($this->coordinacionService->evaluarRequiereAprobacion(500.00));
    }

    public function test_evaluar_requiere_aprobacion_por_palabras_clave_alto_riesgo(): void
    {
        // Maniobras en altura
        $this->assertTrue($this->coordinacionService->evaluarRequiereAprobacion(
            300.00,
            false,
            'Reparación de luminaria en altura usando andamios en gimnasio'
        ));

        // Mantenimiento de transformadores / alta tensión
        $this->assertTrue($this->coordinacionService->evaluarRequiereAprobacion(
            1200.00,
            false,
            'Revisión de transformador de la subestación eléctrica'
        ));

        // Booleano explícito
        $this->assertTrue($this->coordinacionService->evaluarRequiereAprobacion(100.00, true));
    }

    public function test_aprobar_orden_crea_aprobacion_actualiza_orden_y_bitacora(): void
    {
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $solicitud = $this->crearSolicitudPrueba();

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => null,
            'monto_estimado' => 18000.00,
            'requiere_aprobacion' => true,
            'estado' => 'requiere_aprobacion',
        ]);

        $aprobacion = $this->coordinacionService->aprobarOrden(
            $orden,
            $coordinador,
            'Presupuesto y protocolo de seguridad avalados por Dirección'
        );

        $this->assertInstanceOf(Aprobacion::class, $aprobacion);
        $this->assertSame('aprobada', $aprobacion->estado);
        $this->assertSame('aprobada', $orden->fresh()->estado);

        $this->assertDatabaseHas('historial_estados', [
            'id_orden' => $orden->id_orden,
            'estado_nuevo' => 'aprobada',
            'id_usuario_cambio' => $coordinador->id,
        ]);

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $coordinador->id,
            'accion' => 'ORDEN_APROBADA',
        ]);
    }

    public function test_determinar_turno_actual_segun_horario(): void
    {
        $horaMatutina = Carbon::create(2026, 10, 27, 9, 30, 0); // 9:30 AM
        $horaVespertina = Carbon::create(2026, 10, 27, 15, 0, 0); // 3:00 PM
        $horaNocturna = Carbon::create(2026, 10, 27, 22, 0, 0); // 10:00 PM

        $this->assertSame('Matutino', $this->coordinacionService->determinarTurnoActual($horaMatutina));
        $this->assertSame('Vespertino', $this->coordinacionService->determinarTurnoActual($horaVespertina));
        $this->assertNull($this->coordinacionService->determinarTurnoActual($horaNocturna));
    }

    public function test_obtener_tecnicos_disponibles_filtra_por_especialidad_y_turno(): void
    {
        $rolTecnico = Rol::create(['nombre_rol' => 'Tecnico']);

        // Técnico electricista matutino
        $tecnicoElecMat = User::factory()->create();
        $tecnicoElecMat->roles()->attach($rolTecnico->id_rol, [
            'especialidad_tecnico' => 'Electricidad',
            'turno_tecnico' => 'Matutino',
        ]);

        // Técnico electricista vespertino
        $tecnicoElecVesp = User::factory()->create();
        $tecnicoElecVesp->roles()->attach($rolTecnico->id_rol, [
            'especialidad_tecnico' => 'Electricidad',
            'turno_tecnico' => 'Vespertino',
        ]);

        // Técnico plomero matutino
        $tecnicoPlomMat = User::factory()->create();
        $tecnicoPlomMat->roles()->attach($rolTecnico->id_rol, [
            'especialidad_tecnico' => 'Plomería',
            'turno_tecnico' => 'Matutino',
        ]);

        // Consulta: Electricidad en turno Matutino
        $disponibles = $this->coordinacionService->obtenerTecnicosDisponibles('Electricidad', 'Matutino');

        $this->assertCount(1, $disponibles);
        $this->assertTrue($disponibles->contains('id', $tecnicoElecMat->id));
        $this->assertFalse($disponibles->contains('id', $tecnicoElecVesp->id));
        $this->assertFalse($disponibles->contains('id', $tecnicoPlomMat->id));
    }

    protected function crearSolicitudPrueba(): Solicitud
    {
        $plantel = Plantel::firstOrCreate(['nombre' => 'Plantel Test'], ['direccion' => 'Calle 1']);
        $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio Test']);
        $espacio = $edificio->espacios()->firstOrCreate(
            ['nombre_espacio' => 'Espacio Test'],
            ['tipo_espacio' => 'Aula', 'piso' => 'Planta Baja']
        );
        $categoria = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Electricidad']);
        $prioridad = MatrizPrioridad::firstOrCreate(['nivel' => 'Alta'], ['tiempo_respuesta_hrs' => 12]);
        $user = User::factory()->create();

        return Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Falla de prueba para aprobación',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);
    }
}
