<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as BladeView;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.private', function (BladeView $view): void {
            $usuario = auth()->user();

            if ($usuario && in_array($usuario->email_tipo, ['coordinador', 'tecnico', 'almacen'], true)) {
                $view->with('totalMensajesSinLeer', $usuario->mensajesOperativosSinLeer()->count());
            }
        });
    }
}
