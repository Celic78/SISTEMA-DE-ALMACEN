<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class TestViewsCommand extends Command
{
    protected $signature = 'views:test';

    protected $description = 'Prueba el renderizado de todas las vistas Blade del sistema';

    public function handle(): int
    {
        View::share('errors', new \Illuminate\Support\ViewErrorBag());

        $user = User::first();
        if ($user) {
            Auth::login($user);
        }

        $views = [
            'public.home' => [],
            'auth.login' => [],
            'dashboard' => [],
            'coordinacion.dashboard' => [],
            'almacen.inventario' => [],
            'almacen.requisiciones' => [],
            'tecnico.mis-ordenes' => [],
            'reportes.crear' => [
                'edificios' => [],
                'categorias' => [],
                'prioridades' => [],
                'solicitudes' => [],
            ],
            'reportes.mis-reportes' => [],
        ];

        foreach ($views as $viewName => $data) {
            try {
                if (! View::exists($viewName)) {
                    $this->error("Vista no existe: {$viewName}");

                    return 1;
                }
                $rendered = View::make($viewName, $data)->render();
                $this->line("<info>[OK]</info> Vista renderizada correctamente: {$viewName} (" . strlen($rendered) . ' bytes)');
            } catch (\Throwable $e) {
                $this->error("[ERROR] Falló vista {$viewName}: " . $e->getMessage());

                return 1;
            }
        }

        $this->info('Todas las vistas Blade compilaron y renderizaron sin errores.');

        return 0;
    }
}
