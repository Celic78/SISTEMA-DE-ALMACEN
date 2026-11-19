<?php

namespace App\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function autenticar(Request $request): RedirectResponse
    {
        $datos = $this->credenciales($request);

        try {
            $resultado = $this->authService->autenticar($datos['email'], $datos['password']);
        } catch (BusinessRuleException $exception) {
            throw ValidationException::withMessages(['email' => $exception->getMessage()]);
        }

        Auth::login($resultado['user']);
        $request->session()->regenerate();
        $request->session()->put('rol_activo', $resultado['rol_activo']);

        return redirect()->to($resultado['redirect_url']);
    }

    public function registrar(Request $request): RedirectResponse
    {
        $datos = $this->credenciales($request, true);

        DB::transaction(function () use ($datos): void {
            $rol = Rol::firstOrCreate(['nombre_rol' => ucfirst($datos['rol'])]);
            $usuario = new User([
                'name' => $datos['name'],
                'email' => mb_strtolower($datos['email']),
                'password' => $datos['password'],
                'email_tipo' => $datos['rol'],
            ]);
            $usuario->estado_registro = 'pendiente';
            $usuario->save();
            $usuario->roles()->attach($rol->getKey());
        });

        return redirect()->route('login')->with('status', 'Solicitud enviada a Coordinación.');
    }

    public function enviarRecuperacion(Request $request): RedirectResponse
    {
        $datos = $request->validate(['email' => ['required', 'string', 'max:254']]);
        $email = mb_strtolower($datos['email']);

        if ($this->authService->obtenerTipoCuentaDesdeEmail($email) !== null) {
            Password::sendResetLink(['email' => $email]);
        }

        return back()->with('status', 'Si el correo está registrado, recibirás un enlace de recuperación.');
    }

    public function restablecer(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email', 'max:254'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);
        $datos['email'] = mb_strtolower($datos['email']);
        $estado = Password::reset($datos, function (User $usuario, string $password): void {
            $usuario->password = $password;
            $usuario->setRememberToken(Str::random(60));
            $usuario->save();
        });

        if ($estado !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => 'El enlace de recuperación no es válido o ha caducado.']);
        }

        return redirect()->route('login')->with('status', 'Contraseña actualizada.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function credenciales(Request $request, bool $registro = false): array
    {
        $mensaje = 'Error, los datos ingresados no coinciden';
        $reglas = [
            'email' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string', 'max:72'],
        ];

        if ($registro) {
            $reglas['rol'] = ['required', 'string', 'in:alumno,profesor,coordinador,tecnico,almacen'];
            $reglas['name'] = ['required', 'string', 'max:255'];
            $reglas['password'] = ['required', 'string', 'min:8', 'max:72', 'confirmed'];
        }

        $datos = $request->validate($reglas, ['email.*' => $mensaje, 'rol.*' => $mensaje]);

        if (! $registro) {
            return $datos;
        }

        try {
            $this->authService->validarCoincidenciaCorreoRol($datos['email'], $datos['rol']);
        } catch (BusinessRuleException $exception) {
            throw ValidationException::withMessages(['email' => $exception->getMessage()]);
        }

        if (User::where('email', mb_strtolower($datos['email']))->exists()) {
            throw ValidationException::withMessages(['email' => 'El correo ya está registrado.']);
        }

        return $datos;
    }
}
