<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePersonalPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (Auth::viaRemember() && ! app(AuthService::class)->puedeUsarCuenta($usuario)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $pending = $usuario->debe_cambiar_password;

        if (! $pending && $request->session()->get('acceso_temporal')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        if ($pending && ! $request->routeIs('password.initial.*', 'logout')) {
            return redirect()->route('password.initial.edit');
        }

        return $next($request);
    }
}
