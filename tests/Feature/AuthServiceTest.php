<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Exceptions\InvalidInstitutionalEmailException;
use App\Models\Rol;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_validar_coincidencia_correo_rol_permite_alumno_con_correo_valido(): void
    {
        $valido = $this->authService->validarCoincidenciaCorreoRol(
            'maria.gonzalez@alumnos.univ.edu.mx',
            'Alumno'
        );

        $this->assertTrue($valido);
    }

    public function test_validar_coincidencia_correo_rol_permite_profesor_con_correo_valido(): void
    {
        $valido = $this->authService->validarCoincidenciaCorreoRol(
            'roberto.gomez@profesores.univ.edu.mx',
            'Profesor'
        );

        $this->assertTrue($valido);
    }

    public function test_validar_coincidencia_correo_rol_permite_coordinador_con_correo_valido(): void
    {
        $valido = $this->authService->validarCoincidenciaCorreoRol(
            'coordinacion@univ.edu.mx',
            'Coordinador'
        );

        $this->assertTrue($valido);
    }

    public function test_validar_coincidencia_correo_rol_permite_tecnico_con_correo_valido(): void
    {
        $valido = $this->authService->validarCoincidenciaCorreoRol(
            'carlos.mantenimiento@tecnicos.univ.edu.mx',
            'Técnico'
        );

        $this->assertTrue($valido);
    }

    public function test_validar_coincidencia_correo_rol_permite_almacen_con_correo_valido(): void
    {
        $valido = $this->authService->validarCoincidenciaCorreoRol(
            'almacen@univ.edu.mx',
            'Almacén'
        );

        $this->assertTrue($valido);
    }

    public function test_validar_coincidencia_correo_rol_lanza_excepcion_si_alumno_intenta_rol_tecnico(): void
    {
        $this->expectException(InvalidInstitutionalEmailException::class);
        $this->expectExceptionMessage('Error, los datos ingresados no coinciden');

        // Regla: estudiante intentando ingresar como Técnico
        $this->authService->validarCoincidenciaCorreoRol(
            'juan.perez@alumnos.univ.edu.mx',
            'Técnico'
        );
    }

    public function test_validar_coincidencia_correo_rol_lanza_excepcion_con_correo_externo(): void
    {
        $this->expectException(InvalidInstitutionalEmailException::class);
        $this->expectExceptionMessage('Error, los datos ingresados no coinciden');

        $this->authService->validarCoincidenciaCorreoRol(
            'usuario.personal@gmail.com',
            'Alumno'
        );
    }

    public function test_autenticar_exitoso_y_retorna_usuario_con_ruta_rbac(): void
    {
        $password = 'PasswordSeguro123!';
        $user = User::factory()->create([
            'email' => 'laura.coord@admin.univ.edu.mx',
            'password' => $password,
            'email_tipo' => 'coordinador',
        ]);
        $rol = Rol::create(['nombre_rol' => 'Coordinador']);
        $user->roles()->attach($rol->id_rol);

        $resultado = $this->authService->autenticar(
            'laura.coord@admin.univ.edu.mx',
            $password,
            'Coordinador'
        );

        $this->assertSame($user->id, $resultado['user']->id);
        $this->assertStringContainsString('coordinacion', $resultado['redirect_url']);
        $this->assertSame('Coordinador', $resultado['rol_activo']);
    }

    public function test_autenticar_falla_si_contrasena_es_incorrecta(): void
    {
        User::factory()->create([
            'email' => 'docente.ciencias@profesores.univ.edu.mx',
            'password' => 'claveCorrecta',
            'email_tipo' => 'profesor',
        ]);

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Credenciales inválidas');

        $this->authService->autenticar(
            'docente.ciencias@profesores.univ.edu.mx',
            'claveErronea',
            'Profesor'
        );
    }

    public function test_determinar_ruta_destino_rbac_para_cada_rol(): void
    {
        $alumno = User::factory()->create(['email_tipo' => 'alumno']);
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);
        $almacen = User::factory()->create(['email_tipo' => 'almacen']);
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);

        $this->assertStringContainsString('reportes', $this->authService->determinarRutaDestino($alumno, 'Alumno'));
        $this->assertStringContainsString('cuadrillas', $this->authService->determinarRutaDestino($tecnico, 'Técnico'));
        $this->assertStringContainsString('almacen', $this->authService->determinarRutaDestino($almacen, 'Almacén'));
        $this->assertStringContainsString('coordinacion', $this->authService->determinarRutaDestino($coordinador, 'Coordinador'));
    }
}
