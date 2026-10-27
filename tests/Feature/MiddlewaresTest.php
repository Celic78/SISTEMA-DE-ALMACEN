<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Aprobacion;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MiddlewaresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register temporary test routes using the registered middleware aliases
        Route::get('/test-role-tecnico', function () {
            return response()->json(['status' => 'ok']);
        })->middleware('role:Tecnico');

        Route::get('/test-role-multiple', function () {
            return response()->json(['status' => 'ok']);
        })->middleware('role:Coordinador,Almacen');

        Route::get('/test-email-tipo', function () {
            return response()->json(['status' => 'ok']);
        })->middleware('email.tipo:alumno,profesor');

        Route::get('/test-orden-tecnico/{orden}', function ($orden) {
            $id = $orden instanceof OrdenTrabajo ? $orden->id_orden : (int) $orden;

            return response()->json(['orden' => $id]);
        })->middleware('tecnico.asignado');

        Route::get('/test-solicitud-owner/{solicitud}', function (Solicitud $solicitud) {
            return response()->json(['solicitud' => $solicitud->id_solicitud]);
        })->middleware('solicitud.propietario');

        Route::post('/test-orden-material/{orden}', function (OrdenTrabajo $orden) {
            return response()->json(['despachado' => true]);
        })->middleware('orden.aprobada.material');

        Route::post('/test-bitacora-audit', function () {
            return response()->json(['saved' => true]);
        })->middleware('bitacora');

        Route::get('/test-security-headers', function () {
            return response()->json(['secure' => true]);
        })->middleware('security.headers');

        Route::get('/test-force-json', function () {
            return response()->json(['json' => true]);
        })->middleware('force.json');
    }

    public function test_ensure_user_has_role_rejects_unauthenticated_request(): void
    {
        $response = $this->getJson('/test-role-tecnico');
        $response->assertStatus(401);
    }

    public function test_ensure_user_has_role_blocks_unauthorized_user(): void
    {
        $user = User::factory()->create();
        $rol = Rol::create(['nombre_rol' => 'Alumno']);
        $user->roles()->attach($rol->id_rol);

        $response = $this->actingAs($user)->getJson('/test-role-tecnico');
        $response->assertStatus(403);
    }

    public function test_ensure_user_has_role_allows_user_with_required_role(): void
    {
        $user = User::factory()->create();
        $rol = Rol::create(['nombre_rol' => 'Tecnico']);
        $user->roles()->attach($rol->id_rol);

        $response = $this->actingAs($user)->getJson('/test-role-tecnico');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    public function test_ensure_user_has_role_allows_administrator_bypass(): void
    {
        $user = User::factory()->create();
        $adminRol = Rol::create(['nombre_rol' => 'Administrador']);
        $user->roles()->attach($adminRol->id_rol);

        // Access route restricted to Tecnico
        $response = $this->actingAs($user)->getJson('/test-role-tecnico');
        $response->assertStatus(200);
    }

    public function test_ensure_user_has_role_supports_multiple_roles(): void
    {
        $user = User::factory()->create();
        $rol = Rol::create(['nombre_rol' => 'Almacen']);
        $user->roles()->attach($rol->id_rol);

        $response = $this->actingAs($user)->getJson('/test-role-multiple');
        $response->assertStatus(200);
    }

    public function test_ensure_email_tipo_validates_correct_institutional_account(): void
    {
        $studentUser = User::factory()->create([
            'email_tipo' => 'alumno',
        ]);

        $response = $this->actingAs($studentUser)->getJson('/test-email-tipo');
        $response->assertStatus(200);

        $coordinatorUser = User::factory()->create([
            'email_tipo' => 'coordinador',
        ]);

        $forbiddenResponse = $this->actingAs($coordinatorUser)->getJson('/test-email-tipo');
        $forbiddenResponse->assertStatus(403);
    }

    public function test_ensure_tecnico_asignado_allows_assigned_technician_and_blocks_others(): void
    {
        $plantel = Plantel::create(['nombre' => 'Plantel Sur', 'direccion' => 'Calle 1']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio B']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Aula 101', 'tipo_espacio' => 'Aula', 'piso' => 'Planta Baja']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Redes']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Media', 'tiempo_respuesta_hrs' => 48]);

        $solicitante = User::factory()->create(['id_espacio' => $espacio->id_espacio]);
        $tecnicoAsignado = User::factory()->create();
        $tecnicoAjeno = User::factory()->create();

        $solicitud = Solicitud::create([
            'id_usuario' => $solicitante->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Sin conexión ethernet',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        $orden = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $tecnicoAsignado->id,
            'monto_estimado' => 100.00,
            'requiere_aprobacion' => false,
            'estado' => 'abierta',
        ]);

        // Assigned technician should succeed
        $responseOk = $this->actingAs($tecnicoAsignado)->getJson("/test-orden-tecnico/{$orden->id_orden}");
        $responseOk->assertStatus(200);
        $responseOk->assertJson(['orden' => $orden->id_orden]);

        // Unassigned technician should be rejected
        $responseForbidden = $this->actingAs($tecnicoAjeno)->getJson("/test-orden-tecnico/{$orden->id_orden}");
        $responseForbidden->assertStatus(403);

        // Coordinator supervisor can inspect any order
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $responseCoord = $this->actingAs($coordinador)->getJson("/test-orden-tecnico/{$orden->id_orden}");
        $responseCoord->assertStatus(200);
    }

    public function test_ensure_solicitud_ownership_allows_owner_and_supervisors(): void
    {
        $plantel = Plantel::create(['nombre' => 'Plantel Norte', 'direccion' => 'Calle 2']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio C']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Aula 202', 'tipo_espacio' => 'Aula', 'piso' => 'Piso 2']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Iluminación']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Baja', 'tiempo_respuesta_hrs' => 72]);

        $propietario = User::factory()->create();
        $otroUsuario = User::factory()->create();

        $solicitud = Solicitud::create([
            'id_usuario' => $propietario->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Lámpara fundida',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        // Owner can access
        $this->actingAs($propietario)
            ->getJson("/test-solicitud-owner/{$solicitud->id_solicitud}")
            ->assertStatus(200);

        // Another user is blocked
        $this->actingAs($otroUsuario)
            ->getJson("/test-solicitud-owner/{$solicitud->id_solicitud}")
            ->assertStatus(403);
    }

    public function test_ensure_orden_aprobada_para_material_enforces_approval_rules(): void
    {
        $plantel = Plantel::create(['nombre' => 'Plantel Poniente', 'direccion' => 'Calle 3']);
        $edificio = $plantel->edificios()->create(['nombre' => 'Edificio D']);
        $espacio = $edificio->espacios()->create(['nombre_espacio' => 'Taller 1', 'tipo_espacio' => 'Taller', 'piso' => 'Planta Baja']);
        $categoria = CategoriaSolicitud::create(['nombre_categoria' => 'Estructural']);
        $prioridad = MatrizPrioridad::create(['nivel' => 'Alta', 'tiempo_respuesta_hrs' => 24]);

        $usuario = User::factory()->create();
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);

        $solicitud = Solicitud::create([
            'id_usuario' => $usuario->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Reparación de techo',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        // Order requiring approval, currently unapproved
        $ordenRequiereAprobacion = $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $usuario->id,
            'monto_estimado' => 18000.00,
            'requiere_aprobacion' => true,
            'estado' => 'abierta',
        ]);

        $this->actingAs($usuario)
            ->postJson("/test-orden-material/{$ordenRequiereAprobacion->id_orden}")
            ->assertStatus(422)
            ->assertJson(['requiere_aprobacion' => true]);

        // When approval is granted, material operation is unblocked
        Aprobacion::create([
            'id_orden' => $ordenRequiereAprobacion->id_orden,
            'id_coordinador' => $coordinador->id,
            'estado' => 'aprobada',
            'motivo' => 'Presupuesto autorizado por dirección',
        ]);

        $this->actingAs($usuario)
            ->postJson("/test-orden-material/{$ordenRequiereAprobacion->id_orden}")
            ->assertStatus(200);

        // Order NOT requiring approval is unblocked from the beginning
        $ordenSimple = Solicitud::create([
            'id_usuario' => $usuario->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Cambio de tornillo',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ])->ordenTrabajo()->create([
            'id_tecnico' => $usuario->id,
            'monto_estimado' => 50.00,
            'requiere_aprobacion' => false,
            'estado' => 'abierta',
        ]);

        $this->actingAs($usuario)
            ->postJson("/test-orden-material/{$ordenSimple->id_orden}")
            ->assertStatus(200);
    }

    public function test_registrar_bitacora_sistema_logs_mutations_and_sanitizes_passwords(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/test-bitacora-audit', [
                'motivo' => 'Ajuste de inventario',
                'password' => 'secret_should_not_be_logged',
                '_token' => 'csrf_token_value',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('bitacora_sistema', [
            'id_usuario' => $user->id,
        ]);

        $bitacora = BitacoraSistema::where('id_usuario', $user->id)->latest('id_bitacora')->first();
        $this->assertNotNull($bitacora);
        $this->assertStringNotContainsString('secret_should_not_be_logged', $bitacora->descripcion);
        $this->assertStringContainsString('Ajuste de inventario', $bitacora->descripcion);
    }

    public function test_security_headers_middleware_attaches_headers(): void
    {
        $response = $this->get('/test-security-headers');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_force_json_response_sets_json_headers(): void
    {
        $response = $this->get('/test-force-json');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }
}
