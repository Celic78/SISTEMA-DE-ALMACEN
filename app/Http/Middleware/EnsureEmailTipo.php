<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailTipo
{
    /**
     * Handle an incoming request.
     *
     * Validates that the user's institutional email account type matches the allowed types.
     * Valid account types: 'alumno', 'profesor', 'coordinador', 'almacen', 'tecnico'.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$tipos
     */
    public function handle(Request $request, Closure $next, string ...$tipos): Response
    {
        $user = $request->user();

        if ($user === null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No autenticado. Inicie sesión para continuar.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'));
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $allowedTipos = [];
        foreach ($tipos as $tipoGroup) {
            foreach (explode(',', $tipoGroup) as $tipo) {
                $trimmed = mb_strtolower(trim($tipo));
                if ($trimmed !== '') {
                    $allowedTipos[] = $trimmed;
                }
            }
        }

        if ($allowedTipos === []) {
            return $next($request);
        }

        $userTipo = mb_strtolower((string) $user->email_tipo);

        if (! in_array($userTipo, $allowedTipos, true)) {
            $tiposString = implode(', ', $allowedTipos);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Acceso restringido: Módulo exclusivo para cuentas de tipo: {$tiposString}.",
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, "Acceso restringido: Módulo exclusivo para cuentas de tipo: {$tiposString}.");
        }

        return $next($request);
    }
}
