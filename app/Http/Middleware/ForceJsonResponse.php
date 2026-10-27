<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * Ensures that incoming requests are treated as expecting JSON responses,
     * guaranteeing consistent JSON format for validation errors and exceptions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if (! $response->headers->has('Content-Type') && $request->isJson()) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
