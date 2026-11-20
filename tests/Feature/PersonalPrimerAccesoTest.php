<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonalPrimerAccesoTest extends TestCase
{
    use RefreshDatabase;

    public static function perfiles(): array
    {
        return [
            ['tecnico', 'cuadrillas.index'],
            ['profesor', 'reportes.index'],
            ['almacen', 'almacen.index'],
        ];
    }

    #[DataProvider('perfiles')]
    public function test_el_personal_debe_sustituir_su_password_temporal(string $rol, string $destino): void
    {
        $this->seed(DatabaseSeeder::class);
        $coordinador = User::where('email_tipo', 'coordinador')->firstOrFail();
        $email = 'persona.nueva@'.$rol.'.mx';
        $temporal = 'Temporal2026!';
        $personal = 'Personal2026!';

        $this->actingAs($coordinador)->get(route('coordinacion.registros'))
            ->assertOk()->assertSee('Crear cuenta de personal');
        $this->post(route('coordinacion.personal.store'), [
            'name' => 'Persona Nueva',
            'email' => $email,
            'rol' => $rol,
            'password' => $temporal,
        ])->assertRedirectToRoute('coordinacion.registros');
        $this->post(route('logout'));

        $this->post(route('login.store'), ['email' => $email, 'password' => $temporal])
            ->assertRedirectToRoute('password.initial.edit');
        $this->get(route($destino))->assertRedirectToRoute('password.initial.edit');
        $this->get(route('password.initial.edit'))->assertOk()->assertSee('Crea tu contraseña');
        $this->post(route('password.initial.update'), [
            'password' => $temporal,
            'password_confirmation' => $temporal,
        ])->assertSessionHasErrors('password');

        $this->post(route('password.initial.update'), [
            'password' => $personal,
            'password_confirmation' => $personal,
        ])->assertRedirectToRoute('login');
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => $email, 'password' => $temporal])
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $email, 'password' => $personal])
            ->assertRedirectToRoute($destino);
        $this->get(route($destino))->assertOk();
    }

    public function test_un_alumno_no_puede_crear_cuentas_de_personal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $alumno = User::where('email_tipo', 'alumno')->firstOrFail();

        $this->actingAs($alumno)->post(route('coordinacion.personal.store'), [
            'name' => 'Persona Nueva',
            'email' => 'persona.nueva@tecnico.mx',
            'rol' => 'tecnico',
            'password' => 'Temporal2026!',
        ])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'persona.nueva@tecnico.mx']);
    }
}
