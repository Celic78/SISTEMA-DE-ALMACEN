<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Solicitud;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSolicitudOwnership
{
    /**
     * Handle an incoming request.
     *
     * Ensures that regular users can only access or modify their own Solicitud records.
     * Coordinators and Administrators are allowed to inspect and manage all requests.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $routeParamName  Route parameter key holding the Solicitud or its ID (default: 'solicitud')
     */
    public function handle(Request $request, Closure $next, string $routeParamName = 'solicitud'): Response
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

        // Coordinators and Administrators have universal review permissions
        if (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'isCoordinador') && $user->isCoordinador())
        ) {
            return $next($request);
        }

        $solicitud = $this->resolveSolicitud($request, $routeParamName);

        if ($solicitud === null) {
            return $next($request);
        }

        if ((int) $solicitud->id_usuario !== (int) $user->id) {
            $message = 'Acceso denegado: No cuenta con permisos para ver o modificar este reporte de falla.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, $message);
        }

        return $next($request);
    }

    /**
     * Resolve the Solicitud model from route parameters.
     */
    protected function resolveSolicitud(Request $request, string $paramName): ?Solicitud
    {
        $candidate = $request->route($paramName)
            ?? $request->route('solicitud')
            ?? $request->route('id_solicitud');

        if ($candidate instanceof Solicitud) {
            return $candidate;
        }

        if (is_numeric($candidate)) {
            return Solicitud::find((int) $candidate);
        }

        return null;
    }
}
