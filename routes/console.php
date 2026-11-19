<?php

use App\Models\User;
use App\Services\SolicitudService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mantenimiento:generar-preventivas', function (): void {
    $servicio = app(SolicitudService::class);
    DB::table('avisos_mantenimiento')
        ->whereNull('ultima_generacion')->where('fecha', '<=', now())
        ->whereNotNull('id_coordinador')->whereNotNull('id_categoria')
        ->orderBy('id')->chunkById(50, function ($avisos) use ($servicio): void {
            foreach ($avisos as $aviso) {
                DB::transaction(function () use ($aviso, $servicio): void {
                    $tabla = DB::table('avisos_mantenimiento');
                    $actual = (clone $tabla)->where('id', $aviso->id)->lockForUpdate()->first();
                    if ($actual === null || $actual->ultima_generacion !== null || $actual->id_coordinador === null || $actual->id_categoria === null) {
                        return;
                    }
                    $coordinador = User::findOrFail($actual->id_coordinador);
                    $reporte = $servicio->crearSolicitud([
                        'ubicacion_libre' => $actual->ubicacion,
                        'id_categoria' => $actual->id_categoria,
                        'descripcion_falla' => $actual->titulo,
                        'fecha_hallazgo' => $actual->fecha,
                        'huella_reporte' => hash('sha256', 'preventivo:'.$actual->id),
                    ], $coordinador);
                    (clone $tabla)->where('id', $actual->id)->update([
                        'ultima_generacion' => now(),
                        'id_orden_generada' => $reporte->ordenTrabajo()->firstOrFail()->getKey(),
                    ]);

                    if ($actual->intervalo_dias !== null) {
                        $fecha = Carbon::parse($actual->fecha);
                        do {
                            $fecha->addDays($actual->intervalo_dias);
                        } while ($fecha->isPast());
                        $tabla->insertOrIgnore([
                            'titulo' => $actual->titulo,
                            'fecha' => $fecha->toDateTimeString(),
                            'ubicacion' => $actual->ubicacion,
                            'id_categoria' => $actual->id_categoria,
                            'id_coordinador' => $actual->id_coordinador,
                            'intervalo_dias' => $actual->intervalo_dias,
                        ]);
                    }
                });
            }
        });
})->purpose('Generar órdenes de mantenimiento preventivo programadas');

Schedule::command('mantenimiento:generar-preventivas')->everyFifteenMinutes()->withoutOverlapping();
