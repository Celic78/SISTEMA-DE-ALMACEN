<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RememberAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_remember_creates_laravel_recaller_cookie(): void
    {
        $this->createUser();

        $response = $this->post(route('login.store'), [
            'email' => 'ana.lopez@alumno.mx',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirectToRoute('reportes.index')
            ->assertCookieNotExpired(Auth::guard('web')->getRecallerName());
        $this->assertTrue(session('recordar_sesion'));
    }

    public function test_login_without_remember_keeps_only_the_normal_session(): void
    {
        $this->createUser();

        $response = $this->post(route('login.store'), [
            'email' => 'ana.lopez@alumno.mx',
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('reportes.index')
            ->assertCookieMissing(Auth::guard('web')->getRecallerName());
        $this->assertFalse(session('recordar_sesion'));
    }

    public function test_logout_removes_session_and_forgets_the_recaller_cookie(): void
    {
        $this->createUser();
        $loginResponse = $this->post(route('login.store'), [
            'email' => 'ana.lopez@alumno.mx',
            'password' => 'password',
            'remember' => '1',
        ]);
        $recaller = collect($loginResponse->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === Auth::guard('web')->getRecallerName());

        $this->withCookie(Auth::guard('web')->getRecallerName(), $recaller->getValue())
            ->post(route('logout'))
            ->assertRedirectToRoute('home')
            ->assertCookieExpired(Auth::guard('web')->getRecallerName());
        $this->assertGuest();
        $this->get(route('home'))->assertDontSee('Ir a mi panel');
    }

    public function test_panel_ignores_a_stale_coordinator_role_in_the_session(): void
    {
        $alumno = $this->createUser();

        $this->actingAs($alumno)
            ->withSession(['rol_activo' => 'coordinador'])
            ->get(route('dashboard'))
            ->assertRedirectToRoute('reportes.index');
    }

    public function test_invalid_or_pending_account_cannot_use_the_panel(): void
    {
        $alumno = $this->createUser(['estado_registro' => 'pendiente']);

        $this->actingAs($alumno)
            ->get(route('dashboard'))
            ->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    /** @param array<string, mixed> $overrides */
    private function createUser(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'email' => 'ana.lopez@alumno.mx',
            'password' => 'password',
            'email_tipo' => 'alumno',
            'estado_registro' => 'aprobado',
        ], $overrides));
        $user->roles()->attach(Rol::create(['nombre_rol' => 'Alumno'])->getKey());

        return $user;
    }
}
