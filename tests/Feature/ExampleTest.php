<?php

namespace Tests\Feature;

use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Material;
use App\Models\OrdenTrabajo;
use App\Models\SeccionAlmacen;
use App\Models\User;
use App\Services\CoordinacionService;
use App\Services\SolicitudService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_shows_the_public_portal_before_login(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('SIRE-M')->assertSee('Sistema de Reportes Escolares y Mantenimiento')->assertSee('Avisos de mantenimiento')
            ->assertSee(route('servicios'))
            ->assertDontSee('Reporte de fallas');
        $this->get(route('servicios'))->assertOk()->assertSee('Mantenimiento de inmuebles')->assertSee('Pasillos, accesos y áreas comunes')->assertDontSee('Mantenimiento Eléctrico y Electrónico');
        $this->get(route('reportes.create'))->assertRedirect(route('login'));
        $this->get(route('reportes.estado'))->assertRedirect(route('login'));
    }

    public function test_registration_rejects_a_role_submitted_by_the_client(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'nombres' => 'Ana', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Reyes',
            'email' => 'ana.torres@alumno.mx',
            'rol' => 'profesor',
            'password' => 'MantrescPrueba123!',
            'password_confirmation' => 'MantrescPrueba123!',
        ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('users', ['email' => 'ana.torres@alumno.mx']);
    }

    public function test_only_approved_users_can_sign_in(): void
    {
        $this->seed();
        $email = 'nueva.alumna@alumno.mx';
        $password = 'MantrescPrueba123!';

        $this->post(route('register.store'), [
            'nombres' => 'Ana María', 'apellido_paterno' => 'Torres', 'apellido_materno' => 'Reyes',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertRedirect(route('login'));

        $this->post(route('login.store'), ['email' => $email, 'password' => $password])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $alumna = User::where('email', $email)->firstOrFail();
        $this->assertSame('Ana María Torres Reyes', $alumna->name);
        $this->actingAs($coordinadora)->get(route('coordinacion.registros'))->assertOk()
            ->assertSee('pending-notification-access', false)
            ->assertSee('Solicitud de acceso pendiente')
            ->assertDontSee('pending-notification-reports', false);
        $this->post(route('coordinacion.registros.decidir', $alumna), ['decision' => 'aprobado']);
        $this->get(route('coordinacion.registros'))->assertDontSee('pending-notification-access', false);
        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->post(route('login.store'), ['email' => $email, 'password' => $password])
            ->assertRedirect(route('reportes.index'));
        $this->assertAuthenticatedAs($alumna);
        $this->get(route('almacen.index'))->assertForbidden();
    }

    public function test_demo_user_can_sign_in_without_selecting_a_role(): void
    {
        $this->seed();

        $this->get(route('login'))->assertOk()->assertDontSee('Rol institucional');
        $this->post(route('login.store'), [
            'email' => 'ana.torres@alumno.mx',
            'password' => 'MantrescDemo2026!',
        ])->assertRedirect(route('reportes.index'));
        $this->assertAuthenticated();
    }

    public function test_registration_shows_required_name_fields_and_password_controls(): void
    {
        $this->get(route('register'))->assertOk()
            ->assertSee('Solo los alumnos pueden registrarse.')
            ->assertSee('name="nombres"', false)
            ->assertSee('name="apellido_paterno"', false)
            ->assertSee('name="apellido_materno"', false)
            ->assertSee('aria-controls="access-password"', false)
            ->assertSee('aria-controls="access-password-confirmation"', false)
            ->assertDontSee('name="rol"', false);

        $this->post(route('register.store'), [
            'email' => 'nueva.alumna@alumno.mx',
            'password' => 'MantrescPrueba123!',
            'password_confirmation' => 'MantrescPrueba123!',
        ])->assertSessionHasErrors(['nombres', 'apellido_paterno', 'apellido_materno']);
    }

    public function test_registration_rejects_a_staff_email(): void
    {
        $this->post(route('register.store'), [
            'nombres' => 'Ana',
            'apellido_paterno' => 'Torres',
            'apellido_materno' => 'Reyes',
            'email' => 'ana.torres@tecnico.mx',
            'password' => 'MantrescPrueba123!',
            'password_confirmation' => 'MantrescPrueba123!',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'ana.torres@tecnico.mx']);
    }

    public function test_a_report_can_be_assigned_and_started_only_after_required_approval(): void
    {
        $this->seed();
        Storage::fake('public');
        $alumna = User::where('email', 'ana.torres@alumno.mx')->firstOrFail();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $tecnico = User::where('email', 'pablo.cruz@tecnico.mx')->firstOrFail();
        $imagen = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aXwAAAABJRU5ErkJggg==');
        $datos = [
            'ubicacion_libre' => 'Edificio A, salón 204',
            'id_categoria' => CategoriaSolicitud::firstOrFail()->getKey(),
            'descripcion_falla' => 'La puerta del aula no cierra correctamente.',
            'fecha_hallazgo' => '2026-11-17T09:00',
        ];

        $this->actingAs($alumna)->get(route('reportes.create'))->assertOk()
            ->assertSee('Describe la falla del inmueble')
            ->assertDontSee('name="id_categoria"', false);
        $this->post(route('reportes.store'), $datos + [
            'evidencia' => UploadedFile::fake()->createWithContent('falla.png', $imagen),
        ])->assertSessionHasNoErrors()->assertRedirect(route('reportes.index'));
        $this->post(route('reportes.store'), $datos + [
            'evidencia' => UploadedFile::fake()->createWithContent('otra.png', $imagen),
        ])->assertSessionHasErrors('reporte');

        $orden = OrdenTrabajo::firstOrFail();
        $this->assertSame(CategoriaSolicitud::INMUEBLE, $orden->solicitud->categoria->nombre_categoria);
        $this->actingAs($coordinadora)->get(route('coordinacion.registros'))->assertOk()
            ->assertSee('pending-notification-reports', false)
            ->assertSee('Reporte por revisar')
            ->assertSee(route('coordinacion.index', ['estado' => 'nuevas']));
        $this->actingAs($coordinadora)->post(route('coordinacion.ordenes.asignar', $orden), [
            'id_tecnico' => $tecnico->getKey(),
            'id_prioridad' => $orden->solicitud->id_prioridad,
            'monto_estimado' => 100,
            'alto_riesgo' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('requiere_aprobacion', $orden->refresh()->estado);

        $inicio = ['id_orden' => $orden->getKey(), 'horas_estimadas_sla' => 6];
        $this->actingAs($tecnico)->post(route('cuadrillas.estimar'), $inicio)->assertStatus(409);
        $this->actingAs($coordinadora)->post(route('coordinacion.ordenes.decidir', $orden), [
            'decision' => 'aprobar',
            'motivo' => 'Revisión de seguridad completada.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($tecnico)->post(route('cuadrillas.estimar'), $inicio)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('ordenes_trabajo', ['id_orden' => $orden->getKey(), 'estado' => 'en_proceso', 'horas_estimadas_sla' => 6]);
        $this->actingAs($alumna)->get(route('reportes.estado'))->assertOk()
            ->assertSeeInOrder(['Reporte enviado', 'Técnico asignado', 'En mantenimiento', 'Solucionado y terminado'])
            ->assertSee('Tiempo estimado: 6 h');
        $this->actingAs($tecnico)->get(route('cuadrillas.index', ['estado' => 'proceso']))->assertOk()
            ->assertSee('La puerta del aula no cierra correctamente.');
        $this->post(route('cuadrillas.actualizar'), [
            'id_orden' => $orden->getKey(),
            'estado' => 'resuelta',
            'evidencias' => [UploadedFile::fake()->createWithContent('solucion.png', $imagen)],
        ])->assertSessionHasNoErrors();
        $this->get(route('cuadrillas.index', ['estado' => 'terminadas']))->assertOk()->assertSee('Evidencia de cierre');
        $this->actingAs($coordinadora)->patch(route('coordinacion.ordenes.costo', $orden), [
            'costo_real' => 125.50,
            'justificacion_costo_real' => 'Se reemplazó el cable de alimentación.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('125.50', $orden->refresh()->costo_real);
        $this->get(route('coordinacion.index', ['estado' => 'terminadas']))->assertOk()->assertSee('$125.50');
    }

    public function test_coordinator_can_manage_technical_profiles(): void
    {
        $this->seed();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $tecnico = User::where('email', 'pablo.cruz@tecnico.mx')->firstOrFail();
        $datos = ['especialidad_tecnico' => 'Electrónica', 'turno_tecnico' => 'vespertino'];

        $this->actingAs($tecnico)->patch(route('coordinacion.cuadrillas.actualizar', $tecnico), $datos)->assertForbidden();
        $this->actingAs($coordinadora)->get(route('coordinacion.cuadrillas'))->assertOk()->assertSee($tecnico->name);
        $this->patch(route('coordinacion.cuadrillas.actualizar', $tecnico), $datos)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuario_rol', ['id_usuario' => $tecnico->getKey(), ...$datos]);
    }

    public function test_duplicate_filter_matches_reports_from_the_same_location_and_category(): void
    {
        $this->seed();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $servicio = app(SolicitudService::class);
        $categoria = CategoriaSolicitud::firstOrFail()->getKey();

        foreach (['Iluminación intermitente' => 'Laboratorio 3', 'Una lámpara apagada' => 'laboratorio 3', 'Falla en otra ubicación' => 'Edificio B'] as $descripcion => $ubicacion) {
            $servicio->crearSolicitud([
                'ubicacion_libre' => $ubicacion,
                'id_categoria' => $categoria,
                'descripcion_falla' => $descripcion,
            ], $coordinadora);
        }

        $this->actingAs($coordinadora)->get(route('coordinacion.index', ['estado' => 'duplicados']))->assertOk()
            ->assertSee('Iluminación intermitente')->assertSee('Una lámpara apagada')->assertDontSee('Falla en otra ubicación');
    }

    public function test_material_can_be_reserved_dispatched_once_and_partially_returned(): void
    {
        $this->seed();

        $almacenista = User::where('email', 'rosa.silva@almacen.mx')->firstOrFail();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $tecnico = User::where('email', 'pablo.cruz@tecnico.mx')->firstOrFail();

        $this->actingAs($almacenista)->post(route('almacen.material.store'), [
            'nombre_material' => 'Cable de prueba',
            'id_seccion' => SeccionAlmacen::firstOrFail()->getKey(),
            'unidad_medida' => 'metro',
        ])->assertSessionHasNoErrors();
        $material = Material::firstOrFail();
        $this->post(route('almacen.entrada', $material), ['cantidad' => 10, 'motivo' => 'Carga inicial'])
            ->assertSessionHasNoErrors();

        $solicitud = app(SolicitudService::class)->crearSolicitud([
            'ubicacion_libre' => 'Laboratorio 3',
            'id_categoria' => CategoriaSolicitud::firstOrFail()->getKey(),
            'descripcion_falla' => 'Se debe reemplazar un cable.',
        ], $coordinadora);

        $orden = $solicitud->ordenTrabajo()->firstOrFail();
        app(CoordinacionService::class)->asignarTecnicoAOrden($orden, $tecnico, $coordinadora);
        $requisicion = [
            'id_material' => $material->getKey(),
            'cantidad' => 4,
            'recoleccion' => now()->addDay()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($tecnico)->post(route('cuadrillas.material', $orden), $requisicion)->assertSessionHasNoErrors();
        $this->post(route('cuadrillas.material', $orden), $requisicion)->assertSessionHasErrors([
            'id_material' => 'Ya existe una requisición pendiente de este material para la orden.',
        ]);

        $registro = BitacoraSistema::where('accion', 'REQUISICION_MATERIALES_ALMACEN')->sole();
        $entrega = ['entrega' => 'Cuatro metros entregados en ventanilla.'];
        $this->actingAs($almacenista)->post(route('almacen.requisiciones.despacho', $registro), $entrega)->assertRedirect();
        $this->post(route('almacen.requisiciones.despacho', $registro), $entrega)->assertStatus(409);
        $this->post(route('almacen.requisiciones.devolucion', $registro), [
            'cantidad' => 1,
            'motivo' => 'Se usaron tres metros y se devolvió el sobrante.',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('existencias', [
            'id_material' => $material->getKey(),
            'cantidad_disponible' => 7,
            'cantidad_reservada' => 0,
        ]);
        $this->actingAs($coordinadora)->get(route('coordinacion.index'))->assertOk()
            ->assertSee('Cable de prueba · 3.00 metro');
    }

    public function test_recurring_maintenance_creates_one_order_per_occurrence(): void
    {
        $this->seed();

        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $fecha = now()->addDay()->startOfMinute();
        $this->actingAs($coordinadora)->get(route('coordinacion.registros'))->assertOk()
            ->assertSeeInOrder(['Solicitudes de acceso', 'Crear cuenta de personal'])
            ->assertDontSee('Publicar aviso de mantenimiento');
        $this->get(route('coordinacion.avisos.index'))->assertOk()
            ->assertSee('Publicar aviso de mantenimiento')
            ->assertDontSee('Crear cuenta de personal')->assertDontSee('Electrónico');
        $aviso = [
            'titulo' => 'Revisión mensual de instalaciones',
            'fecha' => $fecha->format('Y-m-d\TH:i'),
            'ubicacion' => 'Aulas del edificio A',
            'intervalo_dias' => 30,
        ];
        $this->from(route('coordinacion.avisos.index'))->post(route('coordinacion.avisos'), $aviso + [
            'id_categoria' => CategoriaSolicitud::where('nombre_categoria', 'Electrónico')->firstOrFail()->getKey(),
        ])->assertSessionHasErrors('id_categoria');
        $this->assertDatabaseCount('avisos_mantenimiento', 0);

        $this->post(route('coordinacion.avisos'), $aviso + [
            'id_categoria' => CategoriaSolicitud::where('nombre_categoria', CategoriaSolicitud::INMUEBLE)->firstOrFail()->getKey(),
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('coordinacion.avisos.index');
        $this->get(route('home'))->assertSee('Revisión mensual de instalaciones');

        $this->travelTo($fecha->copy()->addMinute(), function (): void {
            $this->artisan('mantenimiento:generar-preventivas')->assertExitCode(0);
            $this->artisan('mantenimiento:generar-preventivas')->assertExitCode(0);
            $this->assertDatabaseCount('ordenes_trabajo', 1);
            $this->assertDatabaseCount('avisos_mantenimiento', 2);
        });
    }

    public function test_institutional_password_recovery_issues_a_reset_link(): void
    {
        $this->seed();
        Notification::fake();

        $alumna = User::where('email', 'ana.torres@alumno.mx')->firstOrFail();
        $this->get(route('login'))->assertSee('¿Olvidaste tu contraseña?');
        $this->post(route('password.email'), ['email' => $alumna->email])->assertRedirect();
        Notification::assertSentTo($alumna, ResetPassword::class);
    }
}
