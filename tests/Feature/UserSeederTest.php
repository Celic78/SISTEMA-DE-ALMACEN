<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use App\Services\AuthService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_seeder_crea_la_cantidad_exacta_de_usuarios_por_rol(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1 Coordinador
        $coordinadores = User::where('email_tipo', 'coordinador')->get();
        $this->assertCount(1, $coordinadores);
        $coord = $coordinadores->first();
        $this->assertTrue($coord->isCoordinador());
        $this->assertTrue(Hash::check(env('SEED_COORDINADOR_PASSWORD', 'Coordinador123!'), $coord->password));

        // 3 Personal de Almacén
        $almacenistas = User::where('email_tipo', 'almacen')->get();
        $this->assertCount(3, $almacenistas);
        foreach ($almacenistas as $alm) {
            $this->assertTrue($alm->isAlmacen());
            $this->assertTrue(Hash::check(env('SEED_ALMACEN_PASSWORD', 'Almacen123!'), $alm->password));
        }

        // 20 Cuadrilla Técnica
        $tecnicos = User::where('email_tipo', 'tecnico')->get();
        $this->assertCount(20, $tecnicos);
        $turnos = [];
        $especialidades = [];
        foreach ($tecnicos as $tec) {
            $this->assertTrue($tec->isTecnico());
            $this->assertTrue(Hash::check(env('SEED_TECNICO_PASSWORD', 'Tecnico123!'), $tec->password));

            $rol = $tec->roles->firstWhere('nombre_rol', 'Tecnico');
            $this->assertNotNull($rol);
            $this->assertNotEmpty($rol->pivot->turno_tecnico);
            $this->assertNotEmpty($rol->pivot->especialidad_tecnico);

            $turnos[$rol->pivot->turno_tecnico] = ($turnos[$rol->pivot->turno_tecnico] ?? 0) + 1;
            $especialidades[$rol->pivot->especialidad_tecnico] = ($especialidades[$rol->pivot->especialidad_tecnico] ?? 0) + 1;
        }
        $this->assertEquals(10, $turnos['Matutino'] ?? 0);
        $this->assertEquals(10, $turnos['Vespertino'] ?? 0);

        // 30 Maestros / Profesores
        $profesores = User::where('email_tipo', 'profesor')->get();
        $this->assertCount(30, $profesores);
        foreach ($profesores as $prof) {
            $this->assertTrue($prof->hasRol('Profesor'));
            $this->assertTrue(Hash::check(env('SEED_MAESTRO_PASSWORD', 'Profesor123!'), $prof->password));
        }

        // 200 Alumnos
        $alumnos = User::where('email_tipo', 'alumno')->get();
        $this->assertCount(200, $alumnos);
        foreach ($alumnos as $alu) {
            $this->assertTrue($alu->hasRol('Alumno'));
            $this->assertTrue(Hash::check(env('SEED_ALUMNO_PASSWORD', 'Alumno123!'), $alu->password));
        }
    }

    public function test_usuarios_semilla_pueden_autenticarse_con_auth_service(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Autenticación de Coordinador
        $resultadoCoord = $this->authService->autenticar(
            env('SEED_COORDINADOR_EMAIL', 'coordinacion@univ.edu.mx'),
            env('SEED_COORDINADOR_PASSWORD', 'Coordinador123!'),
            'Coordinador'
        );
        $this->assertNotEmpty($resultadoCoord['redirect_url']);

        // Autenticación de Almacén
        $resultadoAlm = $this->authService->autenticar(
            env('SEED_ALMACEN_1_EMAIL', 'almacen@univ.edu.mx'),
            env('SEED_ALMACEN_1_PASSWORD', 'Almacen123!'),
            'Almacén'
        );
        $this->assertNotEmpty($resultadoAlm['redirect_url']);

        // Autenticación de Técnico
        $resultadoTec = $this->authService->autenticar(
            'tecnico1@tecnicos.univ.edu.mx',
            env('SEED_TECNICO_PASSWORD', 'Tecnico123!'),
            'Técnico'
        );
        $this->assertNotEmpty($resultadoTec['redirect_url']);

        // Autenticación de Profesor
        $resultadoProf = $this->authService->autenticar(
            'profesor1@profesores.univ.edu.mx',
            env('SEED_MAESTRO_PASSWORD', 'Profesor123!'),
            'Profesor'
        );
        $this->assertNotEmpty($resultadoProf['redirect_url']);

        // Autenticación de Alumno
        $resultadoAlu = $this->authService->autenticar(
            'alumno1@alumnos.univ.edu.mx',
            env('SEED_ALUMNO_PASSWORD', 'Alumno123!'),
            'Alumno'
        );
        $this->assertNotEmpty($resultadoAlu['redirect_url']);
    }
}
