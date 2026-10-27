<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Verifies that the authenticated user possesses at least one of the specified roles.
     * Administrators are automatically granted access.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
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

        $expectedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $singleRole) {
                $trimmed = trim($singleRole);
                if ($trimmed !== '') {
                    $expectedRoles[] = $trimmed;
                }
            }
        }

        if ($expectedRoles === []) {
            return $next($request);
        }

        $hasRole = false;
        if (method_exists($user, 'hasAnyRol')) {
            $hasRole = $user->hasAnyRol($expectedRoles);
        } else {
            foreach ($expectedRoles as $role) {
                if ($user->hasRol($role)) {
                    $hasRole = true;
                    break;
                }
            }
        }

        if (! $hasRole && ! empty($user->email_tipo)) {
            $normalizedExpected = array_map(static fn (string $r): string => mb_strtolower($r), $expectedRoles);
            if (in_array(mb_strtolower((string) $user->email_tipo), $normalizedExpected, true)) {
                $hasRole = true;
            }
        }

        if (! $hasRole) {
            $rolesList = implode(', ', $expectedRoles);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Acceso denegado: Se requiere uno de los siguientes roles: {$rolesList}.",
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, "Acceso denegado: Se requiere uno de los siguientes roles: {$rolesList}.");
        }

        return $next($request);
    }
}
