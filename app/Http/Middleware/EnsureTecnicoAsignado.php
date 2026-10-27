<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\OrdenTrabajo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTecnicoAsignado
{
    /**
     * Handle an incoming request.
     *
     * Ensures that the work order being managed is assigned to the authenticated technician,
     * unless the user is a Coordinator or Administrator.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $routeParamName  Route parameter key holding the OrdenTrabajo or its ID (default: 'orden')
     */
    public function handle(Request $request, Closure $next, string $routeParamName = 'orden'): Response
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

        // Coordinators and Administrators have supervisory access to all work orders
        if (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'isCoordinador') && $user->isCoordinador())
        ) {
            return $next($request);
        }

        $orden = $this->resolveOrdenTrabajo($request, $routeParamName);

        if ($orden === null) {
            // If the route did not carry a work order identifier, allow request to continue or let controller handle
            return $next($request);
        }

        if ((int) $orden->id_tecnico !== (int) $user->id) {
            $message = 'Acceso denegado: No está asignado como técnico responsable de esta orden de trabajo.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, $message);
        }

        return $next($request);
    }

    /**
     * Resolve the OrdenTrabajo model from the request route parameters.
     */
    protected function resolveOrdenTrabajo(Request $request, string $paramName): ?OrdenTrabajo
    {
        $candidate = $request->route($paramName)
            ?? $request->route('orden')
            ?? $request->route('id_orden')
            ?? $request->route('ordenTrabajo')
            ?? $request->route('orden_trabajo');

        if ($candidate instanceof OrdenTrabajo) {
            return $candidate;
        }

        if (is_numeric($candidate)) {
            return OrdenTrabajo::find((int) $candidate);
        }

        return null;
    }
}
