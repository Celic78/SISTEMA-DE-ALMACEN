<?php

namespace Tests\Feature;

use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\OrdenTrabajo;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\CoordinacionService;
use App\Services\SolicitudService;
use Database\Seeders\InventarioDemoSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_records_replacements_loans_and_returns(): void
    {
        $this->freezeSecond();
        $this->seed(InventarioDemoSeeder::class);
        $almacenista = User::factory()->create(['email_tipo' => 'almacen']);
        $mueble = Material::where('codigo_qr_barras', 'DEMO-MOBILIARIO-1')->sole();
        $herramienta = Material::where('codigo_qr_barras', 'DEMO-HERRAMIENTAS-1')->sole();
        $salidas = route('almacen.movimientos', ['tipo' => 'salida']);

        $this->actingAs($almacenista)->get(route('almacen.index', ['categoria' => 'mobiliario']))
            ->assertOk()->assertSee('Reposición');
        $this->get(route('almacen.index', ['categoria' => 'herramientas']))
            ->assertOk()->assertSee('Préstamos');
        $this->get(route('almacen.material.operacion', ['material' => $mueble, 'tipo' => 'salida']))
            ->assertOk()->assertSee('Reposición')->assertDontSee('name="prestado_a"', false);
        $this->get(route('almacen.material.operacion', ['material' => $herramienta, 'tipo' => 'salida']))
            ->assertOk()->assertSee('name="prestado_a"', false);

        $mueble->existencia()->update(['cantidad_reservada' => 12]);
        $this->post(route('almacen.material.salida', $mueble), ['cantidad' => 3, 'motivo' => 'Cambio de sillas'])
            ->assertSessionHasErrors('cantidad');
        $this->assertDatabaseCount('movimientos_material', 0);
        $this->post(route('almacen.material.salida', $mueble), ['cantidad' => 2, 'motivo' => 'Cambio de sillas'])
            ->assertRedirect($salidas);
        $this->assertDatabaseHas('existencias', ['id_material' => $mueble->id_material, 'cantidad_disponible' => 12, 'cantidad_reservada' => 12]);

        $this->post(route('almacen.material.salida', $herramienta), ['cantidad' => 1, 'motivo' => 'Reparar una puerta'])
            ->assertSessionHasErrors('prestado_a');
        $this->post(route('almacen.material.salida', $herramienta), [
            'cantidad' => 1, 'motivo' => 'Reparar una puerta', 'prestado_a' => 'Pablo Cruz',
        ])->assertRedirect($salidas);
        $this->assertDatabaseHas('existencias', ['id_material' => $herramienta->id_material, 'cantidad_disponible' => 7]);
        $this->get($salidas)->assertOk()->assertSee('Salida')->assertSee('Cambio de sillas')
            ->assertSee('Reparar una puerta')->assertSee('Pablo Cruz')->assertSee('Registrar devolución');

        $prestamo = MovimientoMaterial::where('clase_salida', 'prestamo')->sole();
        $devolver = route('almacen.movimientos.devolver', $prestamo);
        $this->post($devolver)->assertRedirect($salidas);
        $this->assertDatabaseHas('movimientos_material', [
            'id_movimiento' => $prestamo->id_movimiento, 'devuelto_en' => now()->toDateTimeString(),
        ]);
        $this->get($salidas)->assertOk()->assertDontSee('Registrar devolución');
        $this->get(route('almacen.movimientos', ['tipo' => 'entrada']))->assertOk()->assertSee('Devolución del préstamo');
        $this->post($devolver)->assertSessionHasErrors('prestamo');
        $this->assertDatabaseHas('existencias', ['id_material' => $herramienta->id_material, 'cantidad_disponible' => 8]);
        $this->assertDatabaseCount('movimientos_material', 3);
    }

    public function test_warehouse_categories_offer_inventory_actions_and_private_profile(): void
    {
        $this->seed(InventarioDemoSeeder::class);
        $almacenista = User::factory()->create(['email_tipo' => 'almacen']);
        $this->actingAs($almacenista);

        foreach (SeccionAlmacen::CATEGORIAS as $clave => $categoria) {
            $url = route('almacen.index', ['categoria' => $clave]);
            $this->get($url)->assertOk()->assertSee($categoria['etiqueta'])
                ->assertSee('Agregar material')->assertSee('Eliminar material')
                ->assertSee('aria-controls="workspace-menu"', false)
                ->assertDontSee('warehouse-sidebar', false)
                ->assertDontSee('Papelería')
                ->assertDontSee($almacenista->email)
                ->assertViewHas('materiales', fn ($materiales): bool => $materiales->total() === 5
                    && $materiales->every(fn (Material $material): bool => $material->seccion->nombre_seccion === $categoria['nombre']));
        }

        $insumos = route('almacen.index', ['categoria' => 'insumos']);
        $this->from($insumos)->post(route('almacen.material.store'), [
            'nombre_material' => 'Cuaderno de prueba',
            'id_seccion' => SeccionAlmacen::where('nombre_seccion', 'Insumos')->value('id_seccion'),
            'unidad_medida' => 'pieza',
        ])->assertRedirect($insumos)->assertSessionHasNoErrors();
        $nuevo = Material::where('nombre_material', 'Cuaderno de prueba')->sole();
        $this->get($insumos)->assertOk()->assertSee($nuevo->nombre_material);
        $this->from($insumos)->delete(route('almacen.material.destroy', $nuevo))->assertRedirect($insumos)->assertSessionHasNoErrors();
        $this->assertModelMissing($nuevo);

        $conStock = Material::where('codigo_qr_barras', 'DEMO-INSUMOS-1')->sole();
        $this->delete(route('almacen.material.destroy', $conStock))->assertSessionHasErrors('material');
        $this->assertModelExists($conStock);
        $this->get(route('almacen.requisiciones'))->assertOk();
        $this->get(route('perfil.show'))->assertOk()->assertSee($almacenista->email)->assertSee('Almacén');
    }

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
        $this->freezeSecond();
        $this->seed();
        Storage::fake('public');
        $alumna = User::where('email', 'ana.torres@alumno.mx')->firstOrFail();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $tecnico = User::where('email', 'pablo.cruz@tecnico.mx')->firstOrFail();
        $imagen = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aXwAAAABJRU5ErkJggg==');
        $hoy = now('America/Mexico_City');
        $edificio = Edificio::where('nombre', 'Edificio 1')->firstOrFail();
        $aula = $edificio->espacios()->where('nombre_espacio', 'Aula 1')->firstOrFail();
        $laboratorio = Espacio::where('nombre_espacio', 'Laboratorio de cómputo 1')->firstOrFail();
        $datos = [
            'id_edificio' => $edificio->getKey(),
            'id_espacio' => $aula->getKey(),
            'id_categoria' => CategoriaSolicitud::firstOrFail()->getKey(),
            'descripcion_falla' => 'La puerta del aula no cierra correctamente.',
            'fecha_hallazgo' => $hoy->copy()->subDays(7)->format('Y-m-d').'T07:00',
        ];

        $this->actingAs($alumna)->get(route('reportes.create'))->assertOk()
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()')
            ->assertSee('data-open-camera', false)
            ->assertSee('data-evidence-previews', false)
            ->assertSee('Describe la falla del inmueble')
            ->assertSee('class="report-calendar-disabled"', false)
            ->assertSee('aria-current="date"', false)
            ->assertSee('name="hora_hallazgo"', false)
            ->assertDontSee('name="id_categoria"', false);
        foreach ([$hoy->copy()->subDays(8), $hoy->copy()->addDays(2)] as $fechaInvalida) {
            $this->post(route('reportes.store'), array_replace($datos, [
                'fecha_hallazgo' => $fechaInvalida->format('Y-m-d').'T12:00',
                'evidencia' => UploadedFile::fake()->createWithContent('falla.png', $imagen),
            ]))->assertSessionHasErrors('fecha_hallazgo');
        }
        $this->post(route('reportes.store'), array_replace($datos, [
            'id_espacio' => $laboratorio->getKey(),
            'evidencia' => UploadedFile::fake()->createWithContent('falla.png', $imagen),
        ]))->assertSessionHasErrors('id_espacio');
        foreach (['06:59', '19:01'] as $horaInvalida) {
            $this->post(route('reportes.store'), array_replace($datos, [
                'fecha_hallazgo' => $hoy->toDateString(),
                'hora_hallazgo' => $horaInvalida,
                'evidencia' => UploadedFile::fake()->createWithContent('falla.png', $imagen),
            ]))->assertSessionHasErrors('hora_hallazgo');
        }
        $this->assertDatabaseCount('solicitudes', 0);
        $this->post(route('reportes.store'), array_replace($datos, [
            'fecha_hallazgo' => $hoy->copy()->subDays(7)->toDateString(),
            'hora_hallazgo' => '07:00',
            'evidencia' => UploadedFile::fake()->createWithContent('falla.png', $imagen),
        ]))->assertSessionHasNoErrors()->assertRedirect(route('reportes.index'));
        $this->post(route('reportes.store'), $datos + [
            'evidencia' => UploadedFile::fake()->createWithContent('otra.png', $imagen),
        ])->assertSessionHasErrors('reporte');

        $orden = OrdenTrabajo::firstOrFail();
        $this->assertSame($aula->getKey(), $orden->solicitud->id_espacio);
        $rutaFoto = parse_url($orden->solicitud->evidencia_url, PHP_URL_PATH);
        $this->get(route('reportes.index'))->assertOk()
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertSee('src="'.$rutaFoto.'"', false);
        $this->assertSame(CategoriaSolicitud::INMUEBLE, $orden->solicitud->categoria->nombre_categoria);
        $this->actingAs($coordinadora)->get(route('coordinacion.registros'))->assertOk()
            ->assertSee('pending-notification-reports', false)
            ->assertSee('Reporte por revisar')
            ->assertSee(route('coordinacion.index', ['estado' => 'nuevas']));
        $this->actingAs($coordinadora)->post(route('coordinacion.ordenes.asignar', $orden), [
            'id_tecnico' => $tecnico->getKey(),
            'id_prioridad' => $orden->solicitud->id_prioridad,
            'alto_riesgo' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('requiere_aprobacion', $orden->refresh()->estado);

        $inicio = ['id_orden' => $orden->getKey(), 'horas_estimadas_sla' => 6];
        $this->actingAs($tecnico)->post(route('cuadrillas.estimar'), $inicio)->assertStatus(409);
        $this->actingAs($coordinadora)->post(route('coordinacion.ordenes.decidir', $orden), [
            'decision' => 'aprobar',
            'motivo' => 'Revisión de seguridad completada.',
        ])->assertSessionHasNoErrors();
        $this->actingAs($tecnico)->post(route('cuadrillas.estimar'), $inicio)
            ->assertSessionHasNoErrors()->assertRedirect(route('cuadrillas.index', ['estado' => 'proceso']));
        $this->assertDatabaseHas('ordenes_trabajo', ['id_orden' => $orden->getKey(), 'estado' => 'en_proceso', 'horas_estimadas_sla' => 6]);
        $this->actingAs($alumna)->get(route('reportes.estado'))->assertOk()
            ->assertSeeInOrder(['Reporte enviado', 'Técnico asignado', 'En mantenimiento', 'Solucionado y terminado'])
            ->assertSee('Tiempo estimado: 6 h');
        $this->actingAs($tecnico)->get(route('cuadrillas.index', ['estado' => 'proceso']))->assertOk()
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()')
            ->assertSee('data-open-camera', false)
            ->assertSee('src="'.$rutaFoto.'"', false)
            ->assertSee('La puerta del aula no cierra correctamente.');
        $this->post(route('cuadrillas.actualizar'), [
            'id_orden' => $orden->getKey(),
            'estado' => 'resuelta',
            'evidencias' => [UploadedFile::fake()->createWithContent('solucion.png', $imagen)],
        ])->assertSessionHasNoErrors()->assertRedirect(route('cuadrillas.index', ['estado' => 'terminadas']));
        $this->assertDatabaseHas('ordenes_trabajo', ['id_orden' => $orden->getKey(), 'fecha_fin' => now()->toDateTimeString()]);
        $rutaCierre = parse_url($orden->evidenciasCierre()->sole()->url_archivo, PHP_URL_PATH);
        $this->get(route('cuadrillas.index', ['estado' => 'terminadas']))->assertOk()
            ->assertSee('Evidencia de cierre')->assertSee('src="'.$rutaCierre.'"', false)->assertSee(now()->format('d/m/Y H:i'));
        $this->actingAs($alumna)->get(route('reportes.estado'))->assertOk()->assertSee('src="'.$rutaCierre.'"', false);
        $this->actingAs($coordinadora)->patch(route('coordinacion.ordenes.costo', $orden), [
            'costo_real' => 125.50,
            'justificacion_costo_real' => 'Se reemplazó el cable de alimentación.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('125.50', $orden->refresh()->costo_real);
        $this->get(route('coordinacion.index', ['estado' => 'terminadas']))->assertOk()->assertSee('$125.50');
    }

    public function test_coordination_manages_an_individual_inventory_item_in_its_space(): void
    {
        $this->seed();
        $coordinadora = User::where('email_tipo', 'coordinador')->firstOrFail();
        $alumna = User::where('email_tipo', 'alumno')->firstOrFail();
        $edificio = Edificio::where('nombre', 'Edificio 1')->firstOrFail();
        $espacio = $edificio->espacios()->where('nombre_espacio', 'Aula 1')->firstOrFail();
        $otroEspacio = $edificio->espacios()->where('nombre_espacio', 'Aula 2')->firstOrFail();
        $lista = route('coordinacion.bienes.index', $espacio);
        $alta = route('coordinacion.bienes.store', $espacio);
        $datos = [
            'codigo_inventario' => 'a1-mesa-01',
            'nombre' => 'Mesa de trabajo',
            'tipo' => 'Mobiliario',
            'ubicacion_interna' => 'Junto a la pared norte',
            'marca' => 'Marca de ejemplo',
            'modelo' => 'Mesa 01',
            'numero_serie' => 'SER-123',
            'estado' => 'bueno',
            'observaciones' => 'Cubierta de madera.',
        ];

        $this->actingAs($alumna)->get($lista)->assertForbidden();
        $this->post($alta, $datos)->assertForbidden();
        $this->actingAs($coordinadora)->post($alta, $datos)->assertRedirect($lista)->assertSessionHasNoErrors();
        $bien = $espacio->bienes()->sole();
        $this->assertSame('A1-MESA-01', $bien->codigo_inventario);
        $this->get($lista)->assertOk()->assertSee('Mesa de trabajo')->assertSee('Junto a la pared norte')
            ->assertSee('SER-123')->assertSee('Cubierta de madera.');
        $this->post(route('coordinacion.bienes.store', $otroEspacio), $datos)
            ->assertSessionHasErrors('codigo_inventario');
        $this->get(route('coordinacion.bienes.edit', [$otroEspacio, $bien]))->assertNotFound();
        $this->patch(route('coordinacion.bienes.update', [$espacio, $bien]), array_replace($datos, ['estado' => 'baja']))
            ->assertRedirect($lista)->assertSessionHasNoErrors();
        $this->get($lista)->assertOk()->assertSee('Dado de baja');
    }

    public function test_report_uses_the_selected_inventory_unit_or_an_unlisted_item(): void
    {
        $this->seed();
        Storage::fake('public');
        $alumna = User::where('email_tipo', 'alumno')->firstOrFail();
        $edificio = Edificio::where('nombre', 'Edificio 1')->firstOrFail();
        $aula = $edificio->espacios()->where('nombre_espacio', 'Aula 1')->firstOrFail();
        $otraAula = $edificio->espacios()->where('nombre_espacio', 'Aula 2')->firstOrFail();
        $ficha = ['nombre' => 'Pupitre', 'tipo' => 'Mobiliario', 'ubicacion_interna' => 'Fila 1', 'estado' => 'bueno'];
        $bien = $aula->bienes()->create($ficha + ['codigo_inventario' => 'PUP-01']);
        $otroBien = $otraAula->bienes()->create($ficha + ['codigo_inventario' => 'PUP-02']);
        $foto = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aXwAAAABJRU5ErkJggg==');
        $datos = [
            'id_edificio' => $edificio->getKey(),
            'id_espacio' => $aula->getKey(),
            'fecha_hallazgo' => now('America/Mexico_City')->format('Y-m-d').'T10:00',
            'descripcion_falla' => 'El pupitre tiene la cubierta floja.',
        ];

        $this->actingAs($alumna)->getJson(route('reportes.bienes', $aula))
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.codigo_inventario', 'PUP-01');
        $this->post(route('reportes.store'), $datos + [
            'bien' => 'Pupitre',
            'id_bien_espacio' => $otroBien->getKey(),
            'evidencia' => UploadedFile::fake()->createWithContent('evidencia.png', $foto),
        ])->assertSessionHasErrors('id_bien_espacio');
        $this->post(route('reportes.store'), $datos + [
            'bien' => 'Pupitre',
            'id_bien_espacio' => $bien->getKey(),
            'evidencia' => UploadedFile::fake()->createWithContent('evidencia.png', $foto),
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('reportes.index');
        $this->assertDatabaseHas('solicitudes', ['id_bien_espacio' => $bien->getKey(), 'bien_reportado' => 'Pupitre · PUP-01']);

        $this->post(route('reportes.store'), array_replace($datos, [
            'descripcion_falla' => 'La banca tiene una pata suelta.',
            'bien' => '__otro__',
            'nombre_bien' => 'Banca',
            'numero_bien' => '12',
            'evidencia' => UploadedFile::fake()->createWithContent('evidencia.png', $foto),
        ]))->assertSessionHasNoErrors()->assertRedirectToRoute('reportes.index');
        $this->assertDatabaseHas('solicitudes', ['id_bien_espacio' => null, 'bien_reportado' => 'Banca · 12']);
    }

    public function test_coordination_can_add_a_building_and_lab_to_the_report_catalog(): void
    {
        $this->seed();
        $coordinadora = User::where('email_tipo', 'coordinador')->firstOrFail();
        $alumna = User::where('email_tipo', 'alumno')->firstOrFail();
        $edificioDatos = ['nombre' => 'Edificio 6'];

        $this->actingAs($alumna)->post(route('coordinacion.ubicaciones.edificios'), $edificioDatos)
            ->assertForbidden();
        $this->actingAs($coordinadora)->post(route('coordinacion.ubicaciones.edificios'), $edificioDatos)
            ->assertRedirectToRoute('coordinacion.ubicaciones')->assertSessionHasNoErrors();
        $edificio = Edificio::where('nombre', 'Edificio 6')->firstOrFail();
        $this->post(route('coordinacion.ubicaciones.espacios'), [
            'id_edificio' => $edificio->getKey(),
            'nombre_espacio' => 'Laboratorio 1',
            'tipo_espacio' => 'Laboratorio',
        ])->assertRedirectToRoute('coordinacion.ubicaciones')->assertSessionHasNoErrors();

        $this->actingAs($alumna)->get(route('reportes.create'))->assertOk()
            ->assertSee('Edificio 6 · Laboratorio 1');
    }

    public function test_coordinator_can_manage_technical_profiles(): void
    {
        $this->seed();
        $coordinadora = User::where('email', 'clara.ruiz@coordinador.mx')->firstOrFail();
        $tecnico = User::where('email', 'pablo.cruz@tecnico.mx')->firstOrFail();
        $datos = ['turno_tecnico' => 'vespertino'];

        $this->actingAs($tecnico)->patch(route('coordinacion.cuadrillas.actualizar', $tecnico), $datos)->assertForbidden();
        $this->actingAs($coordinadora)->get(route('coordinacion.cuadrillas'))->assertOk()->assertSee($tecnico->name)->assertDontSee('Especialidad');
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
            ->assertSee('Iluminación intermitente')->assertSee('Una lámpara apagada')->assertDontSee('Falla en otra ubicación')
            ->assertSee('name="principal"', false);

        [$principal, $duplicado, $otro] = Solicitud::orderBy('id_solicitud')->get()->all();
        $ruta = route('coordinacion.ordenes.vincular', $duplicado->ordenTrabajo);
        $bandeja = route('coordinacion.index', ['estado' => 'duplicados']);
        $this->from($bandeja)->post($ruta, ['principal' => $otro->getKey()])
            ->assertRedirect($bandeja)->assertSessionHasErrors('principal');
        $this->assertNull($duplicado->refresh()->id_solicitud_padre);
        $this->post($ruta, ['principal' => $principal->getKey()])
            ->assertRedirect($bandeja)->assertSessionHasNoErrors();
        $this->assertEquals($principal->getKey(), $duplicado->refresh()->id_solicitud_padre);
        $this->assertSame('cancelada_duplicada', $duplicado->ordenTrabajo->estado);
    }

    public function test_coordination_reviews_and_separates_automatically_grouped_reports(): void
    {
        $this->seed();
        $servicio = app(SolicitudService::class);
        $solicitantes = User::where('email_tipo', 'alumno')->orderBy('id')->take(2)->get();
        $coordinadora = User::where('email_tipo', 'coordinador')->firstOrFail();
        $edificio = Edificio::where('nombre', 'Edificio 1')->firstOrFail();
        $datos = [
            'id_edificio' => $edificio->getKey(),
            'ubicacion_libre' => 'Edificio 1 · Aula 1',
            'id_espacio' => $edificio->espacios()->where('nombre_espacio', 'Aula 1')->firstOrFail()->getKey(),
            'id_categoria' => CategoriaSolicitud::where('nombre_categoria', CategoriaSolicitud::INMUEBLE)->firstOrFail()->getKey(),
        ];
        $principal = $servicio->crearSolicitud($datos + ['descripcion_falla' => 'La puerta del aula no cierra.'], $solicitantes[0]);
        $duplicado = $servicio->crearSolicitud($datos + ['descripcion_falla' => 'LA PUERTA  DEL AULA NO CIERRA.'], $solicitantes[1]);
        $distinto = $servicio->crearSolicitud($datos + ['descripcion_falla' => 'La ventana del aula no abre.'], $solicitantes[1]);

        $this->assertEquals($principal->getKey(), $duplicado->id_solicitud_padre);
        $this->assertSame('cancelada_duplicada', $duplicado->ordenTrabajo->estado);
        $this->assertNull($distinto->id_solicitud_padre);
        $bandeja = route('coordinacion.index', ['estado' => 'duplicados']);
        $separar = route('coordinacion.ordenes.desvincular', [$principal->ordenTrabajo, $duplicado]);

        $this->actingAs($solicitantes[0])->delete($separar)->assertForbidden();
        $this->actingAs($coordinadora)->get($bandeja)->assertOk()
            ->assertSee('2 reportes de esta incidencia')->assertSee('LA PUERTA  DEL AULA NO CIERRA.')
            ->assertSee('Separar reporte #'.$duplicado->getKey());
        $otraOrden = route('coordinacion.ordenes.desvincular', [$distinto->ordenTrabajo, $duplicado]);
        $this->from($bandeja)->delete($otraOrden)->assertRedirect($bandeja)->assertSessionHasErrors('duplicado');
        $this->delete($separar)->assertRedirect($bandeja)->assertSessionHasNoErrors();

        $duplicado->refresh();
        $this->assertNull($duplicado->id_solicitud_padre);
        $this->assertSame('pendiente', $duplicado->estado);
        $this->assertSame('abierta', $duplicado->ordenTrabajo->estado);
        $this->get($bandeja)->assertOk()->assertDontSee('2 reportes de esta incidencia')
            ->assertSee('Agregar duplicado a este reporte');
        $agregar = route('coordinacion.ordenes.duplicados.agregar', $principal->ordenTrabajo);
        $this->post($agregar, ['duplicado' => $principal->ordenTrabajo->getKey()])
            ->assertSessionHasErrors('duplicado');
        $this->post($agregar, ['duplicado' => $distinto->ordenTrabajo->getKey()])
            ->assertRedirect($bandeja)->assertSessionHasNoErrors();
        $this->assertEquals($principal->getKey(), $distinto->refresh()->id_solicitud_padre);
        $this->get($bandeja)->assertOk()
            ->assertSee('2 reportes de esta incidencia')
            ->assertSee('Separar reporte #'.$distinto->getKey());
    }

    public function test_coordination_can_assign_additional_cost_and_technical_support(): void
    {
        $this->seed();
        $coordinadora = User::where('email_tipo', 'coordinador')->firstOrFail();
        $tecnico = User::where('email_tipo', 'tecnico')->firstOrFail();
        $reporte = app(SolicitudService::class)->crearSolicitud([
            'ubicacion_libre' => 'Aula 1',
            'id_categoria' => CategoriaSolicitud::where('nombre_categoria', CategoriaSolicitud::INMUEBLE)->firstOrFail()->getKey(),
            'descripcion_falla' => 'La puerta del aula no cierra.',
        ], $coordinadora);
        $orden = $reporte->ordenTrabajo;
        $asignar = route('coordinacion.ordenes.asignar', $orden);
        $datos = [
            'id_tecnico' => $tecnico->getKey(),
            'id_prioridad' => $reporte->id_prioridad,
            'costo_extra' => '1',
            'tecnico_extra' => '1',
        ];

        $this->actingAs($coordinadora)->get(route('coordinacion.index'))->assertOk()
            ->assertSee('name="monto_extra"', false)->assertSee('name="nombre_tecnico_apoyo"', false)
            ->assertDontSee('name="monto_estimado"', false);
        $this->post($asignar, $datos)
            ->assertSessionHasErrors(['monto_extra', 'nombre_tecnico_apoyo']);
        $this->assertSame('abierta', $orden->refresh()->estado);
        $this->post($asignar, $datos + ['monto_extra' => '25.75', 'nombre_tecnico_apoyo' => 'Apoyo externo del plantel'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('ordenes_trabajo', [
            'id_orden' => $orden->getKey(),
            'id_tecnico_apoyo' => null,
            'nombre_tecnico_apoyo' => 'Apoyo externo del plantel',
            'monto_estimado' => '25.75',
            'monto_extra' => '25.75',
            'estado' => 'requiere_aprobacion',
        ]);
        $this->get(route('coordinacion.index'))->assertOk()
            ->assertSee('Costo adicional: $25.75')->assertSee('Costo estimado total: $25.75');
        $this->actingAs($tecnico)->get(route('cuadrillas.index'))->assertOk()->assertSee('Apoyo externo del plantel');
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
            'horas_estimadas_sla' => 3,
        ];

        $this->actingAs($tecnico)->get(route('cuadrillas.index'))->assertOk()->assertSee('Se debe reemplazar un cable.');
        $this->get(route('cuadrillas.index', ['estado' => 'proceso']))->assertOk()->assertDontSee('Se debe reemplazar un cable.');
        $this->post(route('cuadrillas.material', $orden), array_replace($requisicion, ['cantidad' => 11]))
            ->assertSessionHasErrors('cantidad');
        $this->assertSame('asignada', $orden->refresh()->estado);
        $this->post(route('cuadrillas.material', $orden), $requisicion)
            ->assertSessionHasNoErrors()->assertRedirect(route('cuadrillas.index', ['estado' => 'proceso']));
        $this->assertSame('en_proceso', $orden->refresh()->estado);
        $this->get(route('cuadrillas.index'))->assertOk()->assertDontSee('Se debe reemplazar un cable.');
        $this->get(route('cuadrillas.index', ['estado' => 'proceso']))->assertOk()->assertSee('Se debe reemplazar un cable.');
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
        $this->get(route('almacen.movimientos', ['tipo' => 'salida']))->assertOk()
            ->assertSee('Cable de prueba')->assertViewHas('movimientos', fn ($movimientos): bool => $movimientos->total() === 1
                && $movimientos->first()->tipo_movimiento === 'salida');
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
