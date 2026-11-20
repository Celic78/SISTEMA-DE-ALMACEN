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
use Illuminate\Support\Facades\Hash;
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

        if ($resultado['user']->debe_cambiar_password) {
            $request->session()->put('acceso_temporal', true);

            return redirect()->route('password.initial.edit');
        }

        return redirect()->to($resultado['redirect_url']);
    }

    public function crearPersonal(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRol('Coordinador'), 403);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:254'],
            'rol' => ['required', 'in:tecnico,profesor,almacen'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
        ]);
        $email = mb_strtolower($datos['email']);
        try {
            $this->authService->validarCoincidenciaCorreoRol($email, $datos['rol']);
        } catch (BusinessRuleException) {
            throw ValidationException::withMessages([
                'email' => 'Usa el formato nombre.apellido@'.$datos['rol'].'.mx para el perfil elegido.',
            ]);
        }

        DB::transaction(function () use ($datos, $email): void {
            $usuario = User::firstOrCreate(['email' => $email], [
                'name' => $datos['name'],
                'password' => $datos['password'],
                'email_tipo' => $datos['rol'],
            ]);
            if (! $usuario->wasRecentlyCreated) {
                throw ValidationException::withMessages(['email' => 'El correo ya está registrado.']);
            }

            $usuario->estado_registro = 'aprobado';
            $usuario->debe_cambiar_password = true;
            $usuario->save();
            $rol = Rol::firstOrCreate(['nombre_rol' => ucfirst($datos['rol'])]);
            $usuario->roles()->attach($rol->getKey());
        });

        return redirect()->route('coordinacion.registros')->with('status', 'Cuenta creada. Entrega la contraseña temporal a su titular.');
    }

    public function mostrarCambioInicial(Request $request): View|RedirectResponse
    {
        return $request->user()->debe_cambiar_password
            ? view('auth.initial-password')
            : redirect()->route('dashboard');
    }

    public function cambiarPasswordInicial(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        if (! $usuario->debe_cambiar_password) {
            return redirect()->route('dashboard');
        }

        $datos = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);
        if (Hash::check($datos['password'], $usuario->password)) {
            throw ValidationException::withMessages(['password' => 'Elige una contraseña distinta de la temporal.']);
        }

        $actualizada = User::whereKey($usuario->getKey())
            ->where('debe_cambiar_password', true)
            ->where('password', $usuario->password)
            ->update([
                'password' => Hash::make($datos['password']),
                'debe_cambiar_password' => false,
                'remember_token' => Str::random(60),
            ]);

        $this->logout($request);

        return redirect()->route('login')->with('status', $actualizada
            ? 'Contraseña guardada. Inicia sesión con tu nueva contraseña.'
            : 'Vuelve a iniciar sesión para continuar.');
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

        return redirect()->route('login')->with('status', 'Tu solicitud de registro está siendo procesada.');
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
            $usuario->debe_cambiar_password = false;
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
            $reglas['rol'] = ['required', 'string', 'in:alumno'];
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
