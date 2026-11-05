<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Mapeo de patrones de expresiones regulares para dominios institucionales obligatorios.
     *
     * @var array<string, string>
     */
    protected array $patronesDominios = [
        'alumno' => '/^[a-zA-Z0-9._%+-]+@alumnos(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'profesor' => '/^[a-zA-Z0-9._%+-]+@profesores(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'coordinador' => '/^(coordinacion@univ\.edu\.mx|[a-zA-Z0-9._%+-]+@admin(\.[a-zA-Z0-9.-]+)?\.edu\.mx)$/i',
        'tecnico' => '/^[a-zA-Z0-9._%+-]+@tecnicos(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'almacen' => '/^(almacen@univ\.edu\.mx|[a-zA-Z0-9._%+-]+@almacen(\.[a-zA-Z0-9.-]+)?\.edu\.mx)$/i',
    ];

    public function __construct(
        protected ?AuthService $authService = null
    ) {
        $this->authService ??= app(AuthService::class);
    }

    /**
     * Renderiza la vista de inicio de sesión institucional con selector de rol.
     */
    public function login(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $ruta = $this->authService->determinarRutaDestino($user, session('rol_activo'));

            return redirect()->intended($ruta);
        }

        return view('auth.login');
    }

    /**
     * Autentica al usuario institucional validando dominio por Regex y contraseña por BCrypt.
     */
    public function autenticar(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'rol' => ['required', 'string'],
        ], [
            'email.required' => 'El correo institucional es obligatorio.',
            'password.required' => 'La contraseña institucional es obligatoria.',
            'rol.required' => 'Debe seleccionar su rol institucional.',
        ]);

        $email = trim($validated['email']);
        $password = (string) $validated['password'];
        $rol = trim($validated['rol']);
        $rolNormalizado = mb_strtolower($rol);

        // PASO 1: Validación estricta con Regex según el rol institucional seleccionado
        $patronRegex = $this->patronesDominios[$rolNormalizado] ?? null;

        if ($patronRegex === null || preg_match($patronRegex, $email) !== 1) {
            $mensajeError = 'Conflicto de Credenciales Institucionales: El correo ingresado no coincide con el dominio autorizado para el rol '.$rol.'.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $mensajeError,
                ], 422);
            }

            return back()
                ->withInput($request->only('email', 'rol'))
                ->with('error', $mensajeError);
        }

        // PASO 2: Búsqueda del usuario en base de datos
        $usuario = User::where('email', $email)->first();

        // PASO 3: Verificación del Hash BCrypt de la contraseña
        if ($usuario === null || ! Hash::check($password, (string) $usuario->password)) {
            $mensajeError = 'Credenciales inválidas: Verifique su contraseña o consulte a la Mesa de Ayuda.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $mensajeError,
                ], 401);
            }

            return back()
                ->withInput($request->only('email', 'rol'))
                ->with('error', $mensajeError);
        }

        // PASO 4: Iniciar sesión y fijar parámetros en la sesión de Laravel
        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();
        session([
            'rol_activo' => $rol,
            'email_tipo' => $usuario->email_tipo ?? $rolNormalizado,
        ]);

        $rutaDestino = $this->authService->determinarRutaDestino($usuario, $rol);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Acceso autorizado al portal SIMIC.',
                'user' => $usuario,
                'redirect_url' => $rutaDestino,
            ]);
        }

        return redirect()->intended($rutaDestino)->with('status', 'Bienvenido al sistema SIMIC, '.$usuario->name.'.');
    }

    /**
     * Cierra la sesión activa del usuario y limpia tokens de seguridad.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sesión institucional cerrada correctamente.');
    }
}
