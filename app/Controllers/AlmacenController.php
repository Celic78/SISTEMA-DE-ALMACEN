<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BitacoraSistema;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\OrdenTrabajo;
use App\Models\SeccionAlmacen;
use App\Services\AlmacenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function __construct(
        protected ?AlmacenService $almacenService = null
    ) {
        $this->almacenService ??= app(AlmacenService::class);
    }

    /**
     * Muestra el catálogo de inventario, control de existencias y alertas de stock mínimo.
     */
    public function inventario(Request $request): View|JsonResponse
    {
        $busqueda = $request->input('buscar');
        $idSeccion = $request->input('id_seccion');
        $soloBajoStock = $request->boolean('bajo_stock');

        $query = Material::with(['seccion', 'existencia'])
            ->when($busqueda, function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('nombre_material', 'like', "%{$term}%")
                        ->orWhere('codigo_qr_barras', 'like', "%{$term}%")
                        ->orWhere('especificacion', 'like', "%{$term}%");
                });
            })
            ->when($idSeccion, fn ($q, $sec) => $q->where('id_seccion', $sec))
            ->when($soloBajoStock, function ($q) {
                $q->whereHas('existencia', function ($sub) {
                    $sub->whereColumn('cantidad_disponible', '<=', 'stock_minimo');
                });
            })
            ->orderBy('id_seccion')
            ->orderBy('nombre_material');

        $materiales = $query->paginate(20)->withQueryString();

        // Cálculo de alertas de inventario y stock crítico
        $totalMateriales = Material::count();
        $totalSecciones = SeccionAlmacen::count();
        $itemsBajoStock = Existencia::whereColumn('cantidad_disponible', '<=', 'stock_minimo')->count();
        $itemsAgotados = Existencia::where('cantidad_disponible', '<=', 0)->count();

        $secciones = SeccionAlmacen::withCount('materiales')->get();

        $metricas = [
            'total_materiales' => $totalMateriales,
            'total_secciones' => $totalSecciones,
            'alertas_bajo_stock' => $itemsBajoStock,
            'items_agotados' => $itemsAgotados,
            'nivel_abastecimiento' => $totalMateriales > 0 ? round((($totalMateriales - $itemsBajoStock) / $totalMateriales) * 100, 1) : 100,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'metricas' => $metricas,
                'secciones' => $secciones,
                'materiales' => $materiales,
            ]);
        }

        return view('dashboard', [
            'seccion' => 'almacen',
            'materiales' => $materiales,
            'secciones' => $secciones,
            'metricasAlmacen' => $metricas,
        ]);
    }

    /**
     * Muestra las requisiciones de material pendientes de ventanilla y preavisos de cuadrillas.
     */
    public function requisicionesPendientes(Request $request): View|JsonResponse
    {
        // Requisiciones registradas desde la consola técnica en bitácora
        $requisiciones = BitacoraSistema::with('usuario')
            ->whereIn('accion', ['REQUISICION_MATERIALES_ALMACEN', 'PRENOTIFICACION_DESPACHO_ALMACEN'])
            ->latest('id_bitacora')
            ->limit(30)
            ->get()
            ->map(function ($registro) {
                $detalles = json_decode((string) $registro->descripcion, true) ?? [];

                return [
                    'id_requisicion' => $registro->id_bitacora,
                    'fecha_solicitud' => $registro->fecha,
                    'usuario_solicitante' => $registro->usuario?->name ?? 'Técnico Especialista',
                    'id_orden' => $detalles['id_orden'] ?? null,
                    'folio_orden' => $detalles['folio_orden'] ?? ('OT-2026-'.($detalles['id_orden'] ?? '0000')),
                    'total_items' => $detalles['total_items'] ?? count($detalles['insumos'] ?? []),
                    'insumos' => $detalles['insumos'] ?? [],
                    'estado' => $detalles['estado_requisicion'] ?? 'pendiente',
                ];
            });

        // Órdenes de trabajo activas listas para despacho
        $ordenesListasDespacho = OrdenTrabajo::with(['solicitud.espacio.edificio', 'tecnico'])
            ->whereIn('estado', ['abierta', 'asignada', 'en_proceso', 'aprobada'])
            ->latest('id_orden')
            ->limit(20)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'total_pendientes' => count($requisiciones),
                'requisiciones' => $requisiciones,
                'ordenes_activas' => $ordenesListasDespacho,
            ]);
        }

        return view('dashboard', [
            'seccion' => 'requisiciones',
            'requisiciones' => $requisiciones,
            'ordenesDespacho' => $ordenesListasDespacho,
        ]);
    }
}
