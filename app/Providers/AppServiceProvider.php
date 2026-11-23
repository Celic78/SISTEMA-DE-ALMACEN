<?php

namespace App\Providers;

use App\Models\OrdenTrabajo;
use App\Models\User;
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

            if ($usuario?->email_tipo === 'coordinador') {
                $view->with([
                    'solicitudesDeAccesoPendientes' => User::where('estado_registro', 'pendiente')->count(),
                    'reportesPendientesDeRevision' => OrdenTrabajo::whereIn('estado', ['abierta', 'requiere_aprobacion'])
                        ->whereHas('solicitud')->count(),
                ]);
            }
        });
    }
}
