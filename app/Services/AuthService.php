<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Exceptions\InvalidInstitutionalEmailException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Institutional domain regex patterns mapped to internal email account types.
     *
     * @var array<string, string>
     */
    protected array $patronesRegex = [
        'alumno' => '/\A[a-z]+\.[a-z]+@alumno\.mx\z/i',
        'profesor' => '/\A[a-z]+\.[a-z]+@profesor\.mx\z/i',
        'coordinador' => '/\A[a-z]+\.[a-z]+@coordinador\.mx\z/i',
        'tecnico' => '/\A[a-z]+\.[a-z]+@tecnico\.mx\z/i',
        'almacen' => '/\A[a-z]+\.[a-z]+@almacen\.mx\z/i',
    ];

    /**
     * Map human-readable role names or form values to the internal account type.
     *
     * @var array<string, string>
     */
    protected array $mapeoRolATipo = [
        'alumno' => 'alumno',
        'estudiante' => 'alumno',
        'profesor' => 'profesor',
        'docente' => 'profesor',
        'coordinador' => 'coordinador',
        'coordinación' => 'coordinador',
        'coordinacion' => 'coordinador',
        'administrador' => 'coordinador',
        'admin' => 'coordinador',
        'tecnico' => 'tecnico',
        'técnico' => 'tecnico',
        'cuadrilla' => 'tecnico',
        'almacen' => 'almacen',
        'almacén' => 'almacen',
        'almacenista' => 'almacen',
    ];

    /**
     * Validate email pattern compatibility against the requested institutional role.
     *
     * Business Rule: Intercepts and blocks execution before password evaluation
     * if the email pattern does not belong to the selected role category.
     *
     * @throws InvalidInstitutionalEmailException
     */
    public function validarCoincidenciaCorreoRol(string $email, string $rolSeleccionado): bool
    {
        $tipoEsperado = $this->normalizarRolATipo($rolSeleccionado);

        if ($tipoEsperado === null) {
            throw new InvalidInstitutionalEmailException('Error, los datos ingresados no coinciden');
        }

        $pattern = $this->patronesRegex[$tipoEsperado] ?? null;

        if ($pattern === null || preg_match($pattern, $email) !== 1) {
            throw new InvalidInstitutionalEmailException('Error, los datos ingresados no coinciden');
        }

        return true;
    }

    /**
     * Authenticate an institutional user by applying cross-validation rules.
     *
     * @return array{user: User, redirect_url: string, rol_activo: string}
     *
     * @throws InvalidInstitutionalEmailException
     * @throws BusinessRuleException
     */
    public function autenticar(string $email, string $password, ?string $rolSeleccionado = null): array
    {
        $rolSeleccionado ??= $this->obtenerTipoCuentaDesdeEmail($email);
        if ($rolSeleccionado === null) {
            throw new InvalidInstitutionalEmailException('Error, los datos ingresados no coinciden');
        }

        // PASO 1: Validación estricta Correo vs Rol antes de tocar la contraseña
        $this->validarCoincidenciaCorreoRol($email, $rolSeleccionado);

        // PASO 2: Búsqueda del usuario en base de datos
        $user = User::where('email', mb_strtolower($email))->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            throw new BusinessRuleException('Credenciales inválidas. Compruebe su contraseña.', 401);
        }

        // PASO 3: Validación del perfil del usuario (roles y/o email_tipo)
        if (($user->estado_registro ?? 'aprobado') !== 'aprobado') {
            throw new BusinessRuleException('La cuenta todavía no tiene acceso autorizado.', 403);
        }

        $tipoEsperado = $this->normalizarRolATipo($rolSeleccionado);
        $tieneRol = $user->hasRol($tipoEsperado) && $user->email_tipo === $tipoEsperado;

        if (! $tieneRol) {
            throw new InvalidInstitutionalEmailException('Error, los datos ingresados no coinciden');
        }

        // PASO 4: Determinación de la ruta RBAC correspondiente
        $rutaDestino = $this->determinarRutaDestino($user, $rolSeleccionado);

        return [
            'user' => $user,
            'redirect_url' => $rutaDestino,
            'rol_activo' => $tipoEsperado,
        ];
    }

    /**
     * Determine the redirect route or UI view for Role-Based Access Control (RBAC).
     */
    public function determinarRutaDestino(User $user, ?string $rolActivo = null): string
    {
        // El destino se obtiene del usuario autenticado; nunca de un valor de sesión
        // que pudiera pertenecer a una cuenta anterior en el mismo navegador.
        $rolNormalized = $this->normalizarRolATipo((string) $user->email_tipo);

        if ($rolNormalized === 'coordinador') {
            return route('coordinacion.index', [], false);
        }

        if ($rolNormalized === 'tecnico') {
            return route('cuadrillas.index', [], false);
        }

        if ($rolNormalized === 'almacen') {
            return route('almacen.index', [], false);
        }

        // Alumnos y Profesores (Solicitantes)
        return route('reportes.index', [], false);
    }

    /**
     * Verify that a remembered account still has the institutional identity
     * and role that were validated when it was allowed to sign in.
     */
    public function puedeUsarCuenta(User $user): bool
    {
        if (($user->estado_registro ?? 'aprobado') !== 'aprobado') {
            return false;
        }

        $tipoDesdeCorreo = $this->obtenerTipoCuentaDesdeEmail((string) $user->email);

        if ($tipoDesdeCorreo === null || $user->email_tipo !== $tipoDesdeCorreo) {
            return false;
        }

        return $user->hasRol(ucfirst($tipoDesdeCorreo));
    }

    /**
     * Extract the institutional email category from an email address using regex.
     */
    public function obtenerTipoCuentaDesdeEmail(string $email): ?string
    {

        foreach ($this->patronesRegex as $tipo => $pattern) {
            if (preg_match($pattern, $email) === 1) {
                return $tipo;
            }
        }

        return null;
    }

    /**
     * Normalize human role string to internal account key.
     */
    protected function normalizarRolATipo(string $rol): ?string
    {
        $key = mb_strtolower(trim($rol));

        return $this->mapeoRolATipo[$key] ?? null;
    }
}
