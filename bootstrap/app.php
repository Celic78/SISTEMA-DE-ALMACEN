<?php

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
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Alias de middlewares para asignación en rutas y controladores
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'email.tipo' => \App\Http\Middleware\EnsureEmailTipo::class,
            'tecnico.asignado' => \App\Http\Middleware\EnsureTecnicoAsignado::class,
            'solicitud.propietario' => \App\Http\Middleware\EnsureSolicitudOwnership::class,
            'orden.aprobada.material' => \App\Http\Middleware\EnsureOrdenAprobadaParaMaterial::class,
            'bitacora' => \App\Http\Middleware\RegistrarBitacoraSistema::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
