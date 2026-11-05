<?php

namespace App\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\OrdenTrabajo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\CoordinacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoordinacionController extends Controller
{
    public function __construct(
        protected ?CoordinacionService $coordinacionService = null
    ) {
        $this->coordinacionService ??= app(CoordinacionService::class);
    }

    /**
     * Muestra el Centro de Mando y Coordinación Operativa con indicadores clave y órdenes activas.
     */
    public function dashboard(Request $request): View|JsonResponse
    {
        $ordenesAbiertas = OrdenTrabajo::where('estado', 'abierta')->count();
        $ordenesEnProceso = OrdenTrabajo::where('estado', 'en_proceso')->count();
        $ordenesPorAprobar = OrdenTrabajo::where('requiere_aprobacion', true)
            ->where('estado', 'requiere_aprobacion')
            ->count();
        $ordenesConcluidas = OrdenTrabajo::whereIn('estado', ['resuelta', 'concluida', 'cerrada'])->count();

        $ordenesRecientes = OrdenTrabajo::with(['solicitud.espacio.edificio', 'solicitud.categoria', 'tecnico', 'aprobaciones'])
            ->latest('id_orden')
            ->paginate(15);

        $turnoActual = $this->coordinacionService->determinarTurnoActual();

        $metricas = [
            'ordenes_abiertas' => $ordenesAbiertas,
            'ordenes_en_proceso' => $ordenesEnProceso,
            'ordenes_por_aprobar' => $ordenesPorAprobar,
            'ordenes_concluidas' => $ordenesConcluidas,
            'turno_actual' => $turnoActual ?? 'Matutino',
            'monto_total_comprometido' => (float) OrdenTrabajo::whereIn('estado', ['abierta', 'en_proceso', 'asignada'])->sum('monto_estimado'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'metricas' => $metricas,
                'ordenes' => $ordenesRecientes,
            ]);
        }

        return view('dashboard', compact('metricas', 'ordenesRecientes'));
    }

    /**
     * Matriz de detección anti-duplicados por algoritmo LBS v2.4 (Espacio + Categoría).
     */
    public function matrizDuplicados(Request $request): View|JsonResponse
    {
        // Agrupación de solicitudes activas que comparten la misma ubicación física y categoría
        $duplicadosGrupos = Solicitud::select('id_espacio', 'id_categoria', DB::raw('COUNT(*) as total'))
            ->whereIn('estado', ['pendiente', 'en_revision', 'abierta', 'en_proceso'])
            ->whereNull('id_solicitud_padre')
            ->groupBy('id_espacio', 'id_categoria')
            ->having('total', '>', 1)
            ->with(['espacio.edificio', 'categoria'])
            ->get();

        $matriz = [];
        foreach ($duplicadosGrupos as $grupo) {
            $solicitudes = Solicitud::with(['usuario', 'espacio.edificio', 'categoria', 'ordenTrabajo'])
                ->where('id_espacio', $grupo->id_espacio)
                ->where('id_categoria', $grupo->id_categoria)
                ->whereNull('id_solicitud_padre')
                ->whereIn('estado', ['pendiente', 'en_revision', 'abierta', 'en_proceso'])
                ->orderBy('id_solicitud')
                ->get();

            $maestroSugerido = $solicitudes->first();
            $secundarias = $solicitudes->slice(1)->values();

            $matriz[] = [
                'id_espacio' => $grupo->id_espacio,
                'espacio_nombre' => $grupo->espacio?->nombre_espacio,
                'edificio_nombre' => $grupo->espacio?->edificio?->nombre,
                'id_categoria' => $grupo->id_categoria,
                'categoria_nombre' => $grupo->categoria?->nombre_categoria,
                'total_reportes' => $grupo->total,
                'folio_maestro_sugerido' => 'FOL-'.date('Y').'-'.str_pad((string) $maestroSugerido?->id_solicitud, 4, '0', STR_PAD_LEFT),
                'solicitud_maestra' => $maestroSugerido,
                'solicitudes_secundarias' => $secundarias,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'algoritmo' => 'Anti-Duplicados LBS v2.4',
                'grupos_detectados' => count($matriz),
                'matriz' => $matriz,
            ]);
        }

        return view('dashboard', [
            'seccion' => 'duplicados',
            'matrizDuplicados' => $matriz,
        ]);
    }

    /**
     * Consolida múltiples reportes concurrentes bajo un Folio Maestro único.
     */
    public function consolidarFolioMaestro(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_solicitud_padre' => ['nullable', 'integer', 'exists:solicitudes,id_solicitud'],
            'folio_maestro' => ['nullable', 'string'],
            'solicitudes_duplicadas' => ['required', 'array', 'min:1'],
            'solicitudes_duplicadas.*' => ['integer', 'exists:solicitudes,id_solicitud'],
        ], [
            'solicitudes_duplicadas.required' => 'Debe seleccionar al menos un reporte secundario para consolidar.',
        ]);

        // Resolución del folio maestro / solicitud padre
        $idPadre = $validated['id_solicitud_padre'] ?? null;
        if ($idPadre === null && ! empty($validated['folio_maestro'])) {
            $limpio = preg_replace('/[^0-9]/', '', (string) $validated['folio_maestro']);
            $idPadre = (int) $limpio;
        }

        /** @var Solicitud|null $solicitudPadre */
        $solicitudPadre = Solicitud::find($idPadre);
        if ($solicitudPadre === null) {
            $mensajeError = 'No se encontró la solicitud principal o Folio Maestro especificado.';

            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $mensajeError], 404)
                : back()->with('error', $mensajeError);
        }

        /** @var User $coordinador */
        $coordinador = Auth::user() ?? User::whereHas('roles', fn ($q) => $q->where('nombre_rol', 'like', '%coordinador%'))->first() ?? User::first();

        try {
            $totalConsolidadas = $this->coordinacionService->consolidarReportesDuplicados(
                $solicitudPadre,
                $validated['solicitudes_duplicadas'],
                $coordinador
            );

            $folioMaestroStr = 'FOL-'.date('Y').'-'.str_pad((string) $solicitudPadre->id_solicitud, 4, '0', STR_PAD_LEFT);
            $mensajeExito = "Consolidación exitosa: Se unificaron {$totalConsolidadas} reportes bajo el Folio Maestro {$folioMaestroStr}.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensajeExito,
                    'folio_maestro' => $folioMaestroStr,
                    'total_consolidadas' => $totalConsolidadas,
                ]);
            }

            return back()->with('status', $mensajeExito);

        } catch (BusinessRuleException $e) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        }
    }
}
