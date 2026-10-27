<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BitacoraSistema;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RegistrarBitacoraSistema
{
    /**
     * Handle an incoming request.
     *
     * Automatically registers audit log entries in `bitacora_sistema` for successful
     * mutating requests (POST, PUT, PATCH, DELETE) or specifically monitored endpoints.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $accionPersonalizada  Optional override for the action name
     */
    public function handle(Request $request, Closure $next, ?string $accionPersonalizada = null): Response
    {
        $response = $next($request);

        // Only log successful responses for mutating actions, unless explicitly overridden
        if ($this->shouldLog($request, $response, $accionPersonalizada)) {
            $this->logActivity($request, $response, $accionPersonalizada);
        }

        return $response;
    }

    /**
     * Determine whether the current request and response should be audited.
     */
    protected function shouldLog(Request $request, Response $response, ?string $accionPersonalizada): bool
    {
        if ($accionPersonalizada !== null) {
            return true;
        }

        $method = strtoupper($request->method());
        $isMutating = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        return $isMutating && $response->isSuccessful();
    }

    /**
     * Persist the audit log record in bitacora_sistema.
     */
    protected function logActivity(Request $request, Response $response, ?string $accionPersonalizada): void
    {
        try {
            $user = $request->user();
            $routeName = $request->route()?->getName();
            $method = strtoupper($request->method());
            $path = $request->path();

            $accion = $accionPersonalizada ?? ($routeName ?: "{$method} /{$path}");

            // Filter out sensitive parameters such as passwords and tokens
            $sanitizedInputs = $request->except([
                'password',
                'password_confirmation',
                '_token',
                'current_password',
            ]);

            $descripcionData = [
                'metodo' => $method,
                'ruta' => $path,
                'ip' => $request->ip(),
                'status_code' => $response->getStatusCode(),
                'parametros' => $sanitizedInputs,
            ];

            BitacoraSistema::create([
                'id_usuario' => $user?->id,
                'accion' => mb_substr($accion, 0, 255),
                'descripcion' => json_encode($descripcionData, JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);
        } catch (Throwable $e) {
            // Fault tolerance: Never let an audit logging issue interrupt user flow
            Log::warning('Error registrando auditoría en bitacora_sistema: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
