<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PasswordRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidPasswords')]
    public function test_server_rejects_invalid_student_passwords(string $password): void
    {
        $response = $this->post(route('register.store'), $this->registrationData([
            'password' => $password,
            'password_confirmation' => $password,
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'ana.lopez@alumno.mx']);
    }

    /** @return array<string, array{0: string}> */
    public static function invalidPasswords(): array
    {
        return [
            'ascending sequence' => ['M*1234567t*'],
            'descending sequence' => ['Ab*987654z'],
            'repeated digits' => ['Ab!0000x'],
            'without uppercase' => ['abcdefg1!'],
            'without lowercase' => ['ABCDEF1!'],
            'without number' => ['Abcdefg!'],
            'without special character' => ['Abcdefg1'],
            'too short' => ['A!b72m'],
        ];
    }

    public function test_registration_accepts_valid_password_and_creates_pending_student(): void
    {
        $password = 'A!b72mQ';
        $this->post(route('register.store'), $this->registrationData([
            'password' => $password,
            'password_confirmation' => $password,
        ]))->assertRedirectToRoute('login');

        $user = User::where('email', 'ana.lopez@alumno.mx')->firstOrFail();
        $this->assertSame('alumno', $user->email_tipo);
        $this->assertSame('pendiente', $user->estado_registro);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertTrue($user->hasRol('Alumno'));
    }

    public function test_registration_rejects_wrong_confirmation(): void
    {
        $this->post(route('register.store'), $this->registrationData([
            'password_confirmation' => 'A!b72mX',
        ]))->assertSessionHasErrors('password');
    }

    public function test_registration_rejects_privileged_role_in_direct_request(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData([
            'rol' => 'coordinador',
        ]));

        $response->assertSessionHasErrors('rol');
        $this->assertDatabaseMissing('users', ['email' => 'ana.lopez@alumno.mx']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ana.lopez@alumno.mx']);

        $this->post(route('register.store'), $this->registrationData())
            ->assertSessionHasErrors('email');
    }

    /** @param array<string, mixed> $overrides */
    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'nombres' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Garcia',
            'email' => 'ana.lopez@alumno.mx',
            'password' => 'A!b72mQ',
            'password_confirmation' => 'A!b72mQ',
        ], $overrides);
    }
}
