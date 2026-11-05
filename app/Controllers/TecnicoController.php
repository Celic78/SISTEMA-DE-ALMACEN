<?php

namespace App\Controllers;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\BitacoraSistema;
use App\Models\HistorialEstado;
use App\Models\OrdenTrabajo;
use App\Models\User;
use App\Services\CoordinacionService;
use App\Services\TecnicoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TecnicoController extends Controller
{
    public function __construct(
        protected ?TecnicoService $tecnicoService = null,
        protected ?CoordinacionService $coordinacionService = null
    ) {
        $this->tecnicoService ??= app(TecnicoService::class);
        $this->coordinacionService ??= app(CoordinacionService::class);
    }

    /**
     * Lista las órdenes de trabajo asignadas a la cuadrilla técnica activa.
     */
    public function misOrdenes(Request $request): View|JsonResponse
    {
        /** @var User $tecnico */
        $tecnico = Auth::user() ?? User::whereHas('roles', fn ($q) => $q->where('nombre_rol', 'like', '%tecnico%'))->first() ?? User::first();

        $ordenes = OrdenTrabajo::with([
            'solicitud.espacio.edificio',
            'solicitud.categoria',
            'solicitud.prioridad',
            'tareas',
            'aprobaciones',
            'movimientosMaterial.material',
            'evidenciasCierre',
        ])
            ->where('id_tecnico', $tecnico->id)
            ->latest('id_orden')
            ->paginate(15);

        $conteoEstados = [
            'total' => $ordenes->total(),
            'en_proceso' => OrdenTrabajo::where('id_tecnico', $tecnico->id)->where('estado', 'en_proceso')->count(),
            'asignadas' => OrdenTrabajo::where('id_tecnico', $tecnico->id)->where('estado', 'asignada')->count(),
            'resueltas' => OrdenTrabajo::where('id_tecnico', $tecnico->id)->whereIn('estado', ['resuelta', 'concluida'])->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tecnico' => $tecnico->name,
                'metricas' => $conteoEstados,
                'ordenes' => $ordenes,
            ]);
        }

        return view('dashboard', [
            'seccion' => 'cuadrillas',
            'ordenes' => $ordenes,
            'metricasTecnico' => $conteoEstados,
        ]);
    }

    /**
     * Actualiza el estado dentro del flujo de atención de la orden de trabajo.
     */
    public function actualizarEstado(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_orden' => ['required', 'integer', 'exists:ordenes_trabajo,id_orden'],
            'estado' => ['required', 'string', 'in:asignada,en_proceso,en_pruebas,resuelta,cerrada'],
            'comentarios' => ['nullable', 'string', 'max:1000'],
            'resumen_ejecutivo' => ['nullable', 'string', 'max:2000'],
            'evidencias' => ['nullable', 'array'],
        ]);

        /** @var OrdenTrabajo $orden */
        $orden = OrdenTrabajo::with('solicitud')->findOrFail($validated['id_orden']);
        /** @var User $tecnico */
        $tecnico = Auth::user() ?? $orden->tecnico ?? User::first();

        $estadoAnterior = $orden->estado;
        $nuevoEstado = $validated['estado'];

        try {
            if ($nuevoEstado === 'resuelta' || $nuevoEstado === 'cerrada') {
                $evidencias = $validated['evidencias'] ?? [
                    [
                        'tipo' => 'foto_despues',
                        'url_archivo' => 'evidencias/cierre_folio_'.$orden->id_orden.'.jpg',
                        'descripcion' => $validated['comentarios'] ?? 'Cierre técnico satisfactorio',
                    ],
                ];

                $this->tecnicoService->cerrarOrdenFormal(
                    $orden,
                    $tecnico,
                    now(),
                    $evidencias,
                    $validated['resumen_ejecutivo'] ?? $validated['comentarios']
                );
            } else {
                $orden->update([
                    'estado' => $nuevoEstado,
                ]);

                $orden->solicitud?->update([
                    'estado' => $nuevoEstado,
                ]);

                if ($orden->solicitud !== null) {
                    $this->coordinacionService->sincronizarEstadoSolicitudesHijas(
                        $orden->solicitud,
                        $nuevoEstado,
                        $tecnico,
                        $validated['comentarios'] ?? "Transición técnica a {$nuevoEstado}"
                    );
                }

                HistorialEstado::create([
                    'id_orden' => $orden->id_orden,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'id_usuario_cambio' => $tecnico->id,
                    'fecha_cambio' => now(),
                ]);

                BitacoraSistema::create([
                    'id_usuario' => $tecnico->id,
                    'accion' => 'CAMBIO_ESTADO_ORDEN',
                    'descripcion' => json_encode([
                        'id_orden' => $orden->id_orden,
                        'estado_anterior' => $estadoAnterior,
                        'estado_nuevo' => $nuevoEstado,
                        'comentarios' => $validated['comentarios'] ?? null,
                    ], JSON_UNESCAPED_UNICODE),
                    'fecha' => now(),
                ]);
            }

            $mensaje = "Estado de la orden #{$orden->id_orden} actualizado con éxito a '{$nuevoEstado}'.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'orden' => $orden->fresh(['solicitud', 'historialEstados']),
                ]);
            }

            return back()->with('status', $mensaje);

        } catch (BusinessRuleException|ApprovalRequiredException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }

    /**
     * Registra la estimación y compromiso de tiempo SLA para la resolución de la orden.
     */
    public function estimarTiempo(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_orden' => ['required', 'integer', 'exists:ordenes_trabajo,id_orden'],
            'horas_estimadas_sla' => ['required', 'integer', 'min:1', 'max:168'],
            'fecha_inicio' => ['nullable', 'date'],
            'comentarios' => ['nullable', 'string', 'max:1000'],
        ], [
            'horas_estimadas_sla.required' => 'Debe ingresar el compromiso de horas de atención técnica SLA.',
            'horas_estimadas_sla.min' => 'El tiempo SLA mínimo es de 1 hora.',
        ]);

        /** @var OrdenTrabajo $orden */
        $orden = OrdenTrabajo::with('solicitud')->findOrFail($validated['id_orden']);
        /** @var User $tecnico */
        $tecnico = Auth::user() ?? $orden->tecnico ?? User::first();

        $inicio = ! empty($validated['fecha_inicio']) ? Carbon::parse($validated['fecha_inicio']) : now();

        try {
            $ordenActualizada = $this->tecnicoService->iniciarOrdenConCompromiso(
                $orden,
                $tecnico,
                (int) $validated['horas_estimadas_sla'],
                $inicio,
                $validated['comentarios'] ?? null
            );

            $mensaje = "Compromiso SLA registrado: {$validated['horas_estimadas_sla']} hrs estimadas para la orden #{$orden->id_orden}. Labores iniciadas.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'orden' => $ordenActualizada,
                    'horas_estimadas_sla' => $validated['horas_estimadas_sla'],
                    'fecha_compromiso_resolucion' => $inicio->copy()->addHours((int) $validated['horas_estimadas_sla'])->toDateTimeString(),
                ]);
            }

            return back()->with('status', $mensaje);

        } catch (ApprovalRequiredException|BusinessRuleException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }
}
