<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Models\Aprobacion;
use App\Models\BitacoraSistema;
use App\Models\HistorialEstado;
use App\Models\OrdenTrabajo;
use App\Models\Rol;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CoordinacionService
{
    /**
     * Threshold amount in MXN triggering mandatory coordinator approval.
     */
    public const LIMITE_PRESUPUESTO_APROBACION = 15000.00;

    /**
     * High-risk operational keywords requiring special sign-off.
     *
     * @var list<string>
     */
    protected array $palabrasClaveAltoRiesgo = [
        'altura',
        'alturas',
        'andamio',
        'transformador',
        'subestacion',
        'subestación',
        'alta tension',
        'alta tensión',
        'quimico',
        'químico',
        'gas',
        'caldera',
    ];

    /**
     * Consolidate duplicate service requests under a main parent request.
     *
     * Rule: Institutional anti-duplicates allows technicians to work on one order
     * while keeping all linked requesters synchronized in chain.
     *
     * @param  list<int>  $idsSolicitudesDuplicadas
     *
     * @throws BusinessRuleException
     */
    public function consolidarReportesDuplicados(
        Solicitud $solicitudPadre,
        array $idsSolicitudesDuplicadas,
        User $coordinador
    ): int {
        if ($idsSolicitudesDuplicadas === []) {
            return 0;
        }

        return DB::transaction(function () use ($solicitudPadre, $idsSolicitudesDuplicadas, $coordinador): int {
            $hijas = Solicitud::whereIn('id_solicitud', $idsSolicitudesDuplicadas)
                ->where('id_solicitud', '!=', $solicitudPadre->id_solicitud)
                ->get();

            $afectadas = 0;
            foreach ($hijas as $hija) {
                $estadoPrevio = $hija->estado;

                $hija->update([
                    'id_solicitud_padre' => $solicitudPadre->id_solicitud,
                    'estado' => 'agrupada',
                ]);

                // Cancel initial duplicate work orders if any was opened
                if ($hija->ordenTrabajo !== null) {
                    $hija->ordenTrabajo->update(['estado' => 'cancelada_duplicada']);
                }

                $afectadas++;
            }

            BitacoraSistema::create([
                'id_usuario' => $coordinador->id,
                'accion' => 'CONSOLIDACION_DUPLICADOS',
                'descripcion' => json_encode([
                    'id_solicitud_padre' => $solicitudPadre->id_solicitud,
                    'cantidad_unificadas' => $afectadas,
                    'ids_duplicadas' => $idsSolicitudesDuplicadas,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $afectadas;
        });
    }

    /**
     * Cascade status changes from a parent ticket to all associated duplicate requests.
     */
    public function sincronizarEstadoSolicitudesHijas(
        Solicitud $solicitudPadre,
        string $nuevoEstado,
        User $usuarioCambio,
        ?string $motivo = null
    ): int {
        $hijas = $solicitudPadre->solicitudesHijas()->get();

        foreach ($hijas as $hija) {
            $hija->update(['estado' => $nuevoEstado]);
        }

        return $hijas->count();
    }

    /**
     * Evaluate if a work order triggers high risk or budget approval thresholds.
     */
    public function evaluarRequiereAprobacion(
        float $montoEstimado,
        bool $esAltoRiesgo = false,
        ?string $descripcion = null
    ): bool {
        if ($esAltoRiesgo || $montoEstimado >= self::LIMITE_PRESUPUESTO_APROBACION) {
            return true;
        }

        if ($descripcion !== null && $descripcion !== '') {
            $normalized = mb_strtolower($descripcion);
            foreach ($this->palabrasClaveAltoRiesgo as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Formally grant coordinator authorization for a high-cost or high-risk order.
     */
    public function aprobarOrden(OrdenTrabajo $orden, User $coordinador, string $motivo): Aprobacion
    {
        return DB::transaction(function () use ($orden, $coordinador, $motivo): Aprobacion {
            $estadoAnterior = $orden->estado;

            $aprobacion = Aprobacion::create([
                'id_orden' => $orden->id_orden,
                'id_coordinador' => $coordinador->id,
                'estado' => 'aprobada',
                'motivo' => trim($motivo),
            ]);

            $orden->update([
                'estado' => $orden->id_tecnico ? 'asignada' : 'aprobada',
            ]);

            HistorialEstado::create([
                'id_orden' => $orden->id_orden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $orden->estado,
                'id_usuario_cambio' => $coordinador->id,
                'fecha_cambio' => now(),
            ]);

            BitacoraSistema::create([
                'id_usuario' => $coordinador->id,
                'accion' => 'ORDEN_APROBADA',
                'descripcion' => json_encode([
                    'id_orden' => $orden->id_orden,
                    'motivo' => $motivo,
                    'monto_estimado' => $orden->monto_estimado,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $aprobacion;
        });
    }

    /**
     * Formally reject an approval request for a work order.
     */
    public function rechazarOrden(OrdenTrabajo $orden, User $coordinador, string $motivo): Aprobacion
    {
        return DB::transaction(function () use ($orden, $coordinador, $motivo): Aprobacion {
            $estadoAnterior = $orden->estado;

            $aprobacion = Aprobacion::create([
                'id_orden' => $orden->id_orden,
                'id_coordinador' => $coordinador->id,
                'estado' => 'rechazada',
                'motivo' => trim($motivo),
            ]);

            $orden->update(['estado' => 'rechazada']);

            HistorialEstado::create([
                'id_orden' => $orden->id_orden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'rechazada',
                'id_usuario_cambio' => $coordinador->id,
                'fecha_cambio' => now(),
            ]);

            return $aprobacion;
        });
    }

    /**
     * Determine current active operational shift based on time of day.
     *
     * Matutino:   07:00 - 13:00 (7:00 AM - 1:00 PM)
     * Vespertino: 13:00 - 19:00 (1:00 PM - 7:00 PM)
     */
    public function determinarTurnoActual(?Carbon $momento = null): ?string
    {
        $hora = ($momento ?? now())->hour;

        if ($hora >= 7 && $hora < 13) {
            return 'Matutino';
        }

        if ($hora >= 13 && $hora < 19) {
            return 'Vespertino';
        }

        return null;
    }

    /**
     * Retrieve available technicians crossing specialty and operational shift.
     *
     * @return Collection<int, User>
     */
    public function obtenerTecnicosDisponibles(string $especialidad, ?string $turno = null): Collection
    {
        $turnoFiltro = $turno ?? $this->determinarTurnoActual();

        return User::whereHas('roles', function ($q) use ($especialidad, $turnoFiltro) {
            $q->where(function ($sub) {
                $sub->where('nombre_rol', 'Tecnico')
                    ->orWhere('nombre_rol', 'Técnico');
            });

            if ($especialidad !== '') {
                $q->where(function ($sub) use ($especialidad) {
                    $sub->where('usuario_rol.especialidad_tecnico', 'like', "%{$especialidad}%");
                });
            }

            if ($turnoFiltro !== null) {
                $q->where(function ($sub) use ($turnoFiltro) {
                    $sub->where('usuario_rol.turno_tecnico', 'like', "%{$turnoFiltro}%");
                });
            }
        })->get();
    }

    /**
     * Assign a technician to a work order enforcing cost and risk validation.
     *
     * @throws ApprovalRequiredException
     * @throws BusinessRuleException
     */
    public function asignarTecnicoAOrden(
        OrdenTrabajo $orden,
        User $tecnico,
        User $coordinador,
        float $montoEstimado = 0.00,
        bool $esAltoRiesgo = false
    ): OrdenTrabajo {
        return DB::transaction(function () use ($orden, $tecnico, $coordinador, $montoEstimado, $esAltoRiesgo): OrdenTrabajo {
            $descripcion = $orden->solicitud?->descripcion_falla;
            $requiereAprobacion = $this->evaluarRequiereAprobacion($montoEstimado, $esAltoRiesgo, $descripcion);

            $estadoAnterior = $orden->estado;
            $nuevoEstado = $requiereAprobacion ? 'requiere_aprobacion' : 'asignada';

            $orden->update([
                'id_tecnico' => $tecnico->id,
                'monto_estimado' => $montoEstimado,
                'requiere_aprobacion' => $requiereAprobacion,
                'estado' => $nuevoEstado,
            ]);

            // Sincronizar estado en la solicitud padre
            $orden->solicitud?->update([
                'estado' => $nuevoEstado,
            ]);

            HistorialEstado::create([
                'id_orden' => $orden->id_orden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'id_usuario_cambio' => $coordinador->id,
                'fecha_cambio' => now(),
            ]);

            BitacoraSistema::create([
                'id_usuario' => $coordinador->id,
                'accion' => 'ASIGNACION_TECNICO',
                'descripcion' => json_encode([
                    'id_orden' => $orden->id_orden,
                    'id_tecnico' => $tecnico->id,
                    'requiere_aprobacion' => $requiereAprobacion,
                    'monto_estimado' => $montoEstimado,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $orden;
        });
    }
}
