<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Models\BitacoraSistema;
use App\Models\EvidenciaCierre;
use App\Models\HistorialEstado;
use App\Models\OrdenTrabajo;
use App\Models\ReporteFinal;
use App\Models\TareaOrden;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TecnicoService
{
    public function __construct(
        protected CoordinacionService $coordinacionService,
        protected EvidenciaService $evidenciaService
    ) {}

    /**
     * Start work order execution and register estimated resolution SLA.
     *
     * @throws ApprovalRequiredException
     * @throws BusinessRuleException
     */
    public function iniciarOrdenConCompromiso(
        OrdenTrabajo $orden,
        User $tecnico,
        int $horasEstimadasSla,
        ?Carbon $fechaInicio = null,
        ?string $comentarios = null
    ): OrdenTrabajo {
        $this->validarTecnicoAsignado($orden, $tecnico);

        if ($orden->requiere_aprobacion && mb_strtolower((string) $orden->estado) !== 'aprobada') {
            throw new ApprovalRequiredException(
                'No se puede iniciar la orden: requiere aprobación previa de Coordinación.'
            );
        }

        return DB::transaction(function () use ($orden, $tecnico, $horasEstimadasSla, $fechaInicio, $comentarios): OrdenTrabajo {
            $estadoAnterior = $orden->estado;
            $inicio = $fechaInicio ?? now();

            $orden->update([
                'fecha_inicio' => $inicio,
                'estado' => 'en_proceso',
            ]);

            $orden->solicitud?->update([
                'estado' => 'en_proceso',
            ]);

            // Cascada de sincronización con reportes unificados
            if ($orden->solicitud !== null) {
                $this->coordinacionService->sincronizarEstadoSolicitudesHijas(
                    $orden->solicitud,
                    'en_proceso',
                    $tecnico,
                    'Técnico inició labores en sitio'
                );
            }

            HistorialEstado::create([
                'id_orden' => $orden->id_orden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'en_proceso',
                'id_usuario_cambio' => $tecnico->id,
                'fecha_cambio' => now(),
            ]);

            BitacoraSistema::create([
                'id_usuario' => $tecnico->id,
                'accion' => 'ORDEN_INICIADA_SLA',
                'descripcion' => json_encode([
                    'id_orden' => $orden->id_orden,
                    'horas_estimadas_sla' => $horasEstimadasSla,
                    'fecha_inicio' => $inicio->toDateTimeString(),
                    'comentarios' => $comentarios,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $orden;
        });
    }

    /**
     * Complete and formally close a work order enforcing evidence and completion rules.
     *
     * Business Rules:
     * 1. Requires exact termination timestamp.
     * 2. Requires at least one closing evidence (photo, act, or file).
     * 3. Requires all subtasks to be completed if tasks exist.
     * 4. Cascades 'resuelta' status to parent and all grouped duplicate requests.
     *
     * @param  list<array{tipo: string, url_archivo: string, descripcion?: string|null}>  $evidencias
     *
     * @throws BusinessRuleException
     */
    public function cerrarOrdenFormal(
        OrdenTrabajo $orden,
        User $tecnico,
        Carbon|string $fechaFin,
        array $evidencias,
        ?string $resumenEjecutivo = null
    ): OrdenTrabajo {
        $this->validarTecnicoAsignado($orden, $tecnico);

        // REGLA 1: Evidencias obligatorias
        if ($evidencias === []) {
            throw new BusinessRuleException(
                'Cierre rechazado: Es obligatorio adjuntar al menos una evidencia fotográfica o documental del trabajo terminado.'
            );
        }

        // REGLA 2: Verificación de checklist de tareas completadas
        $tareasIncompletas = $orden->tareas()->where('completada', false)->count();
        if ($tareasIncompletas > 0) {
            throw new BusinessRuleException(
                "Cierre rechazado: Hay {$tareasIncompletas} tarea(s) pendientes de marcar como completadas en la orden."
            );
        }

        $momentoFin = $fechaFin instanceof Carbon ? $fechaFin : Carbon::parse($fechaFin);

        return DB::transaction(function () use ($orden, $tecnico, $momentoFin, $evidencias, $resumenEjecutivo): OrdenTrabajo {
            $estadoAnterior = $orden->estado;

            $orden->update([
                'fecha_fin' => $momentoFin,
                'estado' => 'resuelta',
            ]);

            // Guardar evidencias físicas/digitales mediante el servicio especializado
            $this->evidenciaService->registrarEvidenciasCierre($orden, $evidencias);

            // Reporte ejecutivo final si fue emitido
            if ($resumenEjecutivo !== null && trim($resumenEjecutivo) !== '') {
                ReporteFinal::create([
                    'id_orden' => $orden->id_orden,
                    'resumen_ejecutivo' => trim($resumenEjecutivo),
                    'fecha_generacion' => now(),
                ]);
            }

            // Cascada de resolución: Notificación y actualización de la solicitud padre
            $orden->solicitud?->update([
                'estado' => 'resuelta',
            ]);

            // Cascada de resolución: Sincronización automática de todas las solicitudes hijas duplicadas
            if ($orden->solicitud !== null) {
                $this->coordinacionService->sincronizarEstadoSolicitudesHijas(
                    $orden->solicitud,
                    'resuelta',
                    $tecnico,
                    'Incidencia resuelta por la cuadrilla técnica'
                );
            }

            HistorialEstado::create([
                'id_orden' => $orden->id_orden,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'resuelta',
                'id_usuario_cambio' => $tecnico->id,
                'fecha_cambio' => now(),
            ]);

            BitacoraSistema::create([
                'id_usuario' => $tecnico->id,
                'accion' => 'ORDEN_CERRADA_CON_EVIDENCIA',
                'descripcion' => json_encode([
                    'id_orden' => $orden->id_orden,
                    'fecha_fin' => $momentoFin->toDateTimeString(),
                    'total_evidencias' => count($evidencias),
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $orden;
        });
    }

    /**
     * Mark an internal task in the work order as completed or pending.
     */
    public function toggleTarea(TareaOrden $tarea, User $tecnico, bool $completada = true): TareaOrden
    {
        $orden = $tarea->ordenTrabajo;
        if ($orden !== null) {
            $this->validarTecnicoAsignado($orden, $tecnico);
        }

        $tarea->update(['completada' => $completada]);

        return $tarea;
    }

    /**
     * Add a new subtask to the work order checklist.
     */
    public function agregarTarea(OrdenTrabajo $orden, string $descripcionTarea): TareaOrden
    {
        return $orden->tareas()->create([
            'descripcion_tarea' => trim($descripcionTarea),
            'completada' => false,
        ]);
    }

    /**
     * Ensure the authenticated user is the assigned technician or has supervisor authority.
     *
     * @throws BusinessRuleException
     */
    protected function validarTecnicoAsignado(OrdenTrabajo $orden, User $user): void
    {
        if ($user->isAdmin() || $user->isCoordinador()) {
            return;
        }

        if ((int) $orden->id_tecnico !== (int) $user->id) {
            throw new BusinessRuleException(
                'Acceso denegado: No está asignado como técnico responsable de esta orden.',
                403
            );
        }
    }
}
