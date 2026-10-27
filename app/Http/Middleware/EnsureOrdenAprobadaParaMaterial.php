<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\OrdenTrabajo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrdenAprobadaParaMaterial
{
    /**
     * Handle an incoming request.
     *
     * Validates that material requests or dispatches for a work order comply with approval rules.
     * If the work order flags `requiere_aprobacion`, materials cannot be dispatched until
     * the order or its approval status has been formally authorized.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $routeParamName  Route parameter key holding the OrdenTrabajo (default: 'orden')
     */
    public function handle(Request $request, Closure $next, string $routeParamName = 'orden'): Response
    {
        $orden = $this->resolveOrdenTrabajo($request, $routeParamName);

        if ($orden === null) {
            return $next($request);
        }

        // If the order does not require special approval, dispatch is immediately allowed
        if (! $orden->requiere_aprobacion) {
            return $next($request);
        }

        $estaAprobada = $this->isOrdenAprobada($orden);

        if (! $estaAprobada) {
            $message = "Operación de almacén bloqueada: La orden de trabajo #{$orden->id_orden} requiere visto bueno formal de Coordinación antes de despachar o solicitar materiales.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'requiere_aprobacion' => true,
                    'id_orden' => $orden->id_orden,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }

    /**
     * Determine if the work order has been approved.
     */
    protected function isOrdenAprobada(OrdenTrabajo $orden): bool
    {
        if (mb_strtolower((string) $orden->estado) === 'aprobada') {
            return true;
        }

        // Check if there is an approval record marked as 'aprobada'
        return $orden->aprobaciones()
            ->where('estado', 'aprobada')
            ->exists();
    }

    /**
     * Resolve the work order instance from route or request inputs.
     */
    protected function resolveOrdenTrabajo(Request $request, string $paramName): ?OrdenTrabajo
    {
        $candidate = $request->route($paramName)
            ?? $request->route('orden')
            ?? $request->route('id_orden')
            ?? $request->input('id_orden')
            ?? $request->route('ordenTrabajo');

        if ($candidate instanceof OrdenTrabajo) {
            return $candidate;
        }

        if (is_numeric($candidate)) {
            return OrdenTrabajo::find((int) $candidate);
        }

        return null;
    }
}
