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
        'alumno' => '/^[a-zA-Z0-9._%+-]+@alumnos(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'profesor' => '/^[a-zA-Z0-9._%+-]+@profesores(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'coordinador' => '/^(coordinacion@univ\.edu\.mx|[a-zA-Z0-9._%+-]+@admin(\.[a-zA-Z0-9.-]+)?\.edu\.mx)$/i',
        'tecnico' => '/^[a-zA-Z0-9._%+-]+@tecnicos(\.[a-zA-Z0-9.-]+)?\.edu\.mx$/i',
        'almacen' => '/^(almacen@univ\.edu\.mx|[a-zA-Z0-9._%+-]+@almacen(\.[a-zA-Z0-9.-]+)?\.edu\.mx)$/i',
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

        if ($pattern === null || preg_match($pattern, trim($email)) !== 1) {
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
    public function autenticar(string $email, string $password, string $rolSeleccionado): array
    {
        $email = trim($email);

        // PASO 1: Validación estricta Correo vs Rol antes de tocar la contraseña
        $this->validarCoincidenciaCorreoRol($email, $rolSeleccionado);

        // PASO 2: Búsqueda del usuario en base de datos
        $user = User::where('email', $email)->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            throw new BusinessRuleException('Credenciales inválidas. Compruebe su contraseña.', 401);
        }

        // PASO 3: Validación del perfil del usuario (roles y/o email_tipo)
        $tipoEsperado = $this->normalizarRolATipo($rolSeleccionado);
        $tieneRol = $user->hasRol($rolSeleccionado)
            || ($user->email_tipo !== null && mb_strtolower($user->email_tipo) === $tipoEsperado)
            || $user->isAdmin();

        if (! $tieneRol) {
            throw new InvalidInstitutionalEmailException('Error, los datos ingresados no coinciden');
        }

        // PASO 4: Determinación de la ruta RBAC correspondiente
        $rutaDestino = $this->determinarRutaDestino($user, $rolSeleccionado);

        return [
            'user' => $user,
            'redirect_url' => $rutaDestino,
            'rol_activo' => $rolSeleccionado,
        ];
    }

    /**
     * Determine the redirect route or UI view for Role-Based Access Control (RBAC).
     */
    public function determinarRutaDestino(User $user, ?string $rolActivo = null): string
    {
        $rolNormalized = $rolActivo ? $this->normalizarRolATipo($rolActivo) : null;

        if ($rolNormalized === 'coordinador' || $user->isCoordinador() || $user->isAdmin()) {
            return route('coordinacion.index', [], false);
        }

        if ($rolNormalized === 'tecnico' || $user->isTecnico()) {
            return route('cuadrillas.index', [], false);
        }

        if ($rolNormalized === 'almacen' || $user->isAlmacen()) {
            return route('almacen.index', [], false);
        }

        // Alumnos y Profesores (Solicitantes)
        return route('reportes.create', [], false);
    }

    /**
     * Extract the institutional email category from an email address using regex.
     */
    public function obtenerTipoCuentaDesdeEmail(string $email): ?string
    {
        $email = trim($email);

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
