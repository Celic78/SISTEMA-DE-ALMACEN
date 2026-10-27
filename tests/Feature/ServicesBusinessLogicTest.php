<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\DuplicateSolicitudException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidInstitutionalEmailException;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\Rol;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\AlmacenService;
use App\Services\AuthService;
use App\Services\CoordinacionService;
use App\Services\EvidenciaService;
use App\Services\SolicitudService;
use App\Services\TecnicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServicesBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;
    protected SolicitudService $solicitudService;
    protected CoordinacionService $coordinacionService;
    protected TecnicoService $tecnicoService;
    protected AlmacenService $almacenService;
    protected EvidenciaService $evidenciaService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AuthService::class);
        $this->solicitudService = app(SolicitudService::class);
        $this->coordinacionService = app(CoordinacionService::class);
        $this->tecnicoService = app(TecnicoService::class);
        $this->almacenService = app(AlmacenService::class);
        $this->evidenciaService = app(EvidenciaService::class);
    }

    // ==========================================
    // 1. LÓGICA DE AUTENTICACIÓN Y CONTROL DE ACCESO
    // ==========================================

    public function test_auth_service_blocks_mismatched_email_and_role_before_password(): void
    {
        $this->expectException(InvalidInstitutionalEmailException::class);
        $this->expectExceptionMessage('Error, los datos ingresados no coinciden');

        // Alumno email attempting to log in as Técnico
        $this->authService->validarCoincidenciaCorreoRol(
            'juan.perez@alumnos.univ.edu.mx',
            'Técnico'
        );
    }

    public function test_auth_service_authenticates_and_resolves_rbac_route(): void
    {
        $user = User::factory()->create([
            'email' => 'coordinacion@univ.edu.mx',
            'password' => 'secret123',
            'email_tipo' => 'coordinador',
        ]);
        $rol = Rol::create(['nombre_rol' => 'Coordinador']);
        $user->roles()->attach($rol->id_rol);

        $resultado = $this->authService->autenticar(
            'coordinacion@univ.edu.mx',
            'secret123',
            'Coordinador'
        );

        $this->assertSame($user->id, $resultado['user']->id);
        $this->assertStringContainsString('coordinacion', $resultado['redirect_url']);
    }

    // ==========================================
    // 2. LÓGICA DEL MÓDULO DE SOLICITANTES
    // ==========================================

    public function test_solicitud_service_enforces_hierarchical_location(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Central', 'direccion' => 'Av. Universidad']);
        $edificio1 = $plantel->edificios()->create(['nombre' => 'Edificio 1']);
        $edificio2 = $plantel->edificios()->create(['nombre' => 'Edificio 2']);

        $espacioEdificio2 = $edificio2->espacios()->create([
            'nombre_espacio' => 'Laboratorio Cómputo 2',
            'tipo_espacio' => 'Laboratorio',
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Ubicación inválida');

        // Trying to assign space from Edificio 2 to Edificio 1
        $this->solicitudService->validarUbicacionJerarquica(
            $edificio1->id_edificio,
            $espacioEdificio2->id_espacio
        );
    }

    public function test_solicitud_service_prevents_duplicate_reports_per_user(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Norte', 'direccion' => 'Av. Norte']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio A']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Aula 101', 'tipo_espacio' => 'Aula']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Plomería']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Media', 'tiempo_respuesta_hrs' => 24]);

        $usuario = User::factory()->create(['email_tipo' => 'alumno']);

        // First report succeeds
        $solicitud1 = $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Fuga de agua en lavabo',
        ], $usuario);

        $this->assertInstanceOf(Solicitud::class, $solicitud1);

        // Second identical report from the same user while first is active fails
        $this->expectException(DuplicateSolicitudException::class);

        $this->solicitudService->crearSolicitud([
            'id_edificio' => $edificio->id_edificio,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'descripcion_falla' => 'Mismo reporte repetido',
        ], $usuario);
    }

    // ==========================================
    // 3. LÓGICA DEL MÓDULO DE COORDINACIÓN
    // ==========================================

    public function test_coordinacion_consolidates_duplicates_and_cascades_resolution(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Sur', 'direccion' => 'Av. Sur']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Baño Hombres', 'tipo_espacio' => 'Baño']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Plomería']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Alta', 'tiempo_respuesta_hrs' => 12]);

        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // 3 users report the same physical failure
        $padre = Solicitud::create([
            'id_usuario' => $user1->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Fuga de agua en baño',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $hija1 = Solicitud::create([
            'id_usuario' => $user2->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Hay mucha agua tirándose en el baño',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $hija2 = Solicitud::create([
            'id_usuario' => $user3->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Fuga masiva de tubería',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        // Consolidate duplicates under parent
        $afectadas = $this->coordinacionService->consolidarReportesDuplicados(
            $padre,
            [$hija1->id_solicitud, $hija2->id_solicitud],
            $coordinador
        );

        $this->assertSame(2, $afectadas);
        $this->assertSame($padre->id_solicitud, $hija1->fresh()->id_solicitud_padre);
        $this->assertSame('agrupada', $hija1->fresh()->estado);

        // When parent cascades resolution, children are synchronized
        $this->coordinacionService->sincronizarEstadoSolicitudesHijas($padre, 'resuelta', $coordinador);
        $this->assertSame('resuelta', $hija1->fresh()->estado);
        $this->assertSame('resuelta', $hija2->fresh()->estado);
    }

    public function test_coordinacion_evaluates_high_risk_and_budget_thresholds(): void
    {
        // 1. Cost >= 15,000 requires approval
        $this->assertTrue(
            $this->coordinacionService->evaluarRequiereAprobacion(15000.00)
        );

        // 2. High risk keyword ('altura') requires approval
        $this->assertTrue(
            $this->coordinacionService->evaluarRequiereAprobacion(200.00, false, 'Reparación de luminaria en altura con andamio')
        );

        // 3. Simple low-risk repair does not require approval
        $this->assertFalse(
            $this->coordinacionService->evaluarRequiereAprobacion(450.00, false, 'Cambio de manija de puerta')
        );
    }

    // ==========================================
    // 4. LÓGICA DEL MÓDULO DE TÉCNICOS
    // ==========================================

    public function test_tecnico_service_enforces_closing_evidence_and_task_completion(): void
    {
        $plantel = Plantel::create(['nombre' => 'Campus Tech', 'direccion' => 'Av. Tech']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio C']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Taller Electrónico', 'tipo_espacio' => 'Taller']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Electricidad']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Alta', 'tiempo_respuesta_hrs' => 12]);

        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $solicitud = Solicitud::create([
            'id_usuario' => $tecnico->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Pastilla termomagnética botada',
            'fecha_hallazgo' => now(),
            'estado' => 'asignada',
        ]);

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnico->id,
            'monto_estimado' => 500.00,
            'requiere_aprobacion' => false,
            'estado' => 'en_proceso',
        ]);

        // Add a pending task
        $tarea = $orden->tareas()->create([
            'descripcion_tarea' => 'Revisar centro de carga',
            'completada' => false,
        ]);

        // REGLA: Cannot close without evidence
        try {
            $this->tecnicoService->cerrarOrdenFormal($orden, $tecnico, now(), []);
            $this->fail('Debería rechazar cierre sin evidencias.');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('evidencia', $e->getMessage());
        }

        // REGLA: Cannot close with incomplete tasks
        try {
            $this->tecnicoService->cerrarOrdenFormal($orden, $tecnico, now(), [
                ['tipo' => 'fotografia', 'url_archivo' => '/evidencias/foto1.jpg', 'descripcion' => 'Trabajo concluido'],
            ]);
            $this->fail('Debería rechazar cierre con tareas pendientes.');
        } catch (BusinessRuleException $e) {
            $this->assertStringContainsString('tareas pendientes', $e->getMessage());
        }

        // Complete the task and close formally
        $tarea->update(['completada' => true]);

        $ordenCerrada = $this->tecnicoService->cerrarOrdenFormal(
            $orden,
            $tecnico,
            now(),
            [
                ['tipo' => 'fotografia', 'url_archivo' => '/evidencias/foto1.jpg', 'descripcion' => 'Breaker cambiado'],
            ],
            'Se reemplazó pastilla de 30A y se equilibraron cargas'
        );

        $this->assertSame('resuelta', $ordenCerrada->estado);
        $this->assertSame('resuelta', $solicitud->fresh()->estado);
        $this->assertDatabaseHas('evidencias_cierre', ['id_orden' => $orden->id_orden]);
        $this->assertDatabaseHas('reportes_finales', ['id_orden' => $orden->id_orden]);
    }

    // ==========================================
    // 5. LÓGICA DEL MÓDULO DE ALMACÉN E INVENTARIO
    // ==========================================

    public function test_almacen_blocks_dispatch_for_unapproved_order(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $solicitud = $this->crearSolicitudBase($usuarioAlmacen);

        $ordenRequiereAprobacion = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $usuarioAlmacen->id,
            'monto_estimado' => 20000.00,
            'requiere_aprobacion' => true,
            'estado' => 'requiere_aprobacion',
        ]);

        $seccion = SeccionAlmacen::create(['nombre_seccion' => 'Pasillo 1', 'ubicacion_almacen' => 'A-1']);
        $material = $seccion->materiales()->create([
            'nombre_material' => 'Cable Calibre 10',
            'especificacion' => 'Uso rudo',
            'unidad_medida' => 'Metros',
            'codigo_qr_barras' => 'CBL-10',
        ]);
        $material->existencia()->create([
            'cantidad_disponible' => 100.00,
            'stock_minimo' => 10.00,
        ]);

        $this->expectException(ApprovalRequiredException::class);

        $this->almacenService->despacharMateriales($ordenRequiereAprobacion, $usuarioAlmacen, [
            ['id_material' => $material->id_material, 'cantidad' => 20.00],
        ]);
    }

    public function test_almacen_discounts_stock_in_real_time_and_records_kardex(): void
    {
        $usuarioAlmacen = User::factory()->create(['email_tipo' => 'almacen']);
        $solicitud = $this->crearSolicitudBase($usuarioAlmacen);

        $ordenAprobada = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $usuarioAlmacen->id,
            'monto_estimado' => 500.00,
            'requiere_aprobacion' => false,
            'estado' => 'abierta',
        ]);

        $seccion = SeccionAlmacen::create(['nombre_seccion' => 'Herramientas', 'ubicacion_almacen' => 'B-2']);
        $material = $seccion->materiales()->create([
            'nombre_material' => 'Cinta de Aislar 3M',
            'especificacion' => 'Vinil negra',
            'unidad_medida' => 'Piezas',
            'codigo_qr_barras' => 'CNT-3M',
        ]);
        $existencia = $material->existencia()->create([
            'cantidad_disponible' => 15.00,
            'stock_minimo' => 5.00,
        ]);

        // 1. Stock insufficient prevents dispatch
        try {
            $this->almacenService->despacharMateriales($ordenAprobada, $usuarioAlmacen, [
                ['id_material' => $material->id_material, 'cantidad' => 30.00],
            ]);
            $this->fail('Debería arrojar excepción por stock insuficiente.');
        } catch (InsufficientStockException $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        // 2. Valid dispatch discounts stock in real time
        $movimientos = $this->almacenService->despacharMateriales($ordenAprobada, $usuarioAlmacen, [
            ['id_material' => $material->id_material, 'cantidad' => 5.00],
        ]);

        $this->assertCount(1, $movimientos);
        $this->assertEquals(10.00, (float) $existencia->fresh()->cantidad_disponible);

        // Validar Kardex de movimiento y Bitácora
        $this->assertDatabaseHas('movimientos_material', [
            'id_material' => $material->id_material,
            'id_orden' => $ordenAprobada->id_orden,
            'tipo_movimiento' => 'salida',
            'cantidad' => 5.00,
        ]);

        $this->assertDatabaseHas('bitacora_sistema', [
            'accion' => 'DESPACHO_MATERIAL_ALMACEN',
        ]);
    }

    // ==========================================
    // 6. LÓGICA DEL MÓDULO DE EVIDENCIAS
    // ==========================================

    public function test_evidencia_service_stores_closing_evidence_file_and_validates_mime(): void
    {
        Storage::fake('public');

        $solicitud = $this->crearSolicitudBase();
        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $solicitud->id_usuario,
            'estado' => 'en_proceso',
        ]);

        $fakePhoto = UploadedFile::fake()->image('reparacion_final.jpg', 640, 480);

        $guardadas = $this->evidenciaService->registrarEvidenciasCierre($orden, [
            $fakePhoto,
        ], 'Evidencia de lámpara sustituida');

        $this->assertCount(1, $guardadas);
        $this->assertSame('fotografia', $guardadas->first()->tipo);
        $this->assertDatabaseHas('evidencias_cierre', [
            'id_orden' => $orden->id_orden,
            'tipo' => 'fotografia',
            'descripcion' => 'Evidencia de lámpara sustituida',
        ]);
    }

    public function test_evidencia_service_rejects_disallowed_mime_types(): void
    {
        Storage::fake('public');

        $fakeExecutable = UploadedFile::fake()->create('script_peligroso.exe', 500, 'application/x-msdownload');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Formato de archivo no permitido');

        $this->evidenciaService->almacenarArchivo($fakeExecutable);
    }

    /**
     * Helper to create a base Solicitud for testing.
     */
    protected function crearSolicitudBase(?User $usuario = null): Solicitud
    {
        $plantel = Plantel::firstOrCreate(['nombre' => 'Plantel Base'], ['direccion' => 'Calle Principal']);
        $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->firstOrCreate(['nombre_espacio' => 'Espacio 1'], ['tipo_espacio' => 'Aula']);
        $categoria = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'General']);
        $prioridad = MatrizPrioridad::firstOrCreate(['nivel' => 'Media'], ['tiempo_respuesta_hrs' => 24]);
        $user = $usuario ?? User::factory()->create();

        return Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Falla general de prueba',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);
    }
}
