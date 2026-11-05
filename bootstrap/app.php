<?php

use App\Http\Middleware\EnsureEmailTipo;
use App\Http\Middleware\EnsureOrdenAprobadaParaMaterial;
use App\Http\Middleware\EnsureSolicitudOwnership;
use App\Http\Middleware\EnsureTecnicoAsignado;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RegistrarBitacoraSistema;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Headers de seguridad institucionales aplicados globalmente a solicitudes web
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Alias de middlewares para asignación en rutas y controladores
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'email.tipo' => EnsureEmailTipo::class,
            'tecnico.asignado' => EnsureTecnicoAsignado::class,
            'solicitud.propietario' => EnsureSolicitudOwnership::class,
            'orden.aprobada.material' => EnsureOrdenAprobadaParaMaterial::class,
            'bitacora' => RegistrarBitacoraSistema::class,
            'force.json' => ForceJsonResponse::class,
            'security.headers' => SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
