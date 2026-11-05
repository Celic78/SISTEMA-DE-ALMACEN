<?php

namespace App\Controllers;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\BitacoraSistema;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\OrdenTrabajo;
use App\Models\SeccionAlmacen;
use App\Models\User;
use App\Services\AlmacenService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Despacha materiales en ventanilla, descuenta stock de existencias y genera el vale digital con auditoría.
     *
     * @param  Request|int|string|null  $ordenId
     * @param  array<int, array{id_material: int, cantidad: float}>  $itemsDespacho
     */
    public function despacharMaterial($ordenId = null, array $itemsDespacho = []): RedirectResponse|JsonResponse
    {
        $request = null;
        if ($ordenId instanceof Request) {
            $request = $ordenId;
            $ordenId = (int) ($request->input('id_orden') ?? $request->input('orden_id'));
            $itemsDespacho = $request->input('items') ?? $request->input('materiales') ?? [];
        } else {
            $ordenId = (int) $ordenId;
        }

        /** @var OrdenTrabajo|null $orden */
        $orden = OrdenTrabajo::with(['solicitud.espacio', 'tecnico'])->find($ordenId);
        if ($orden === null) {
            $msg = 'La orden de trabajo indicada para el despacho no fue localizada.';

            return ($request && $request->wantsJson())
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : back()->with('error', $msg);
        }

        if (empty($itemsDespacho) || ! is_array($itemsDespacho)) {
            $msg = 'Debe indicar al menos un material e indicar la cantidad a entregar.';

            return ($request && $request->wantsJson())
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        /** @var User $usuarioAlmacen */
        $usuarioAlmacen = Auth::user()
            ?? User::whereHas('roles', fn ($q) => $q->where('nombre_rol', 'like', '%almacen%'))->first()
            ?? User::first();

        try {
            // 1. Ejecución del despacho mediante el servicio especializado (validaciones, descuento e inserción en Kardex)
            $movimientos = $this->almacenService->despacharMateriales(
                $orden,
                $usuarioAlmacen,
                $itemsDespacho
            );

            // 2. Generación formal del Vale Digital Foliado (VAL-2026-XXXX)
            $metodo = $request ? ($request->input('metodo_validacion') ?? 'QR_DIGITAL') : 'QR_DIGITAL';
            $valeDigital = $this->generarValeDigital($orden, $movimientos, $metodo);

            // 3. INSERCIÓN OBLIGATORIA EN BITÁCORA DE AUDITORÍA
            BitacoraSistema::create([
                'id_usuario' => $usuarioAlmacen->id,
                'accion' => 'DESPACHO_VALE_DIGITAL_AUDITORIA',
                'descripcion' => json_encode([
                    'modulo' => 'ALMACEN_SUMINISTROS',
                    'evento' => 'DESPACHO_OFICIAL_VENTANILLA',
                    'folio_vale' => $valeDigital['folio_vale'],
                    'id_orden' => $orden->id_orden,
                    'tecnico_receptor_id' => $orden->id_tecnico,
                    'tecnico_receptor_nombre' => $orden->tecnico?->name ?? 'Técnico Cuadrilla',
                    'almacenista_despachador_id' => $usuarioAlmacen->id,
                    'almacenista_nombre' => $usuarioAlmacen->name,
                    'metodo_autenticacion' => $metodo,
                    'total_materiales' => count($itemsDespacho),
                    'items' => $itemsDespacho,
                    'hash_auditoria' => $valeDigital['sello_digital_seguridad'],
                    'timestamp_oficial' => now()->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            $mensajeExito = "Despacho autorizado exitosamente. Se emitió el Vale Digital Oficial {$valeDigital['folio_vale']}.";

            if ($request && $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensajeExito,
                    'vale_digital' => $valeDigital,
                    'movimientos' => $movimientos,
                ], 200);
            }

            return back()->with('status', $mensajeExito)->with('vale_generado', $valeDigital);

        } catch (ApprovalRequiredException|\App\Exceptions\InsufficientStockException|BusinessRuleException $e) {
            return ($request && $request->wantsJson())
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return ($request && $request->wantsJson())
                ? response()->json(['success' => false, 'message' => 'Error en despacho: '.$e->getMessage()], 500)
                : back()->with('error', 'Error en despacho: '.$e->getMessage());
        }
    }

    /**
     * Emite la constancia oficial y estructura del Vale Digital foliado con sello de integridad.
     *
     * @param  OrdenTrabajo|int|string  $orden
     * @param  array<int, mixed>  $movimientos
     * @return array<string, mixed>
     */
    public function generarValeDigital($orden, array $movimientos = [], ?string $metodoValidacion = 'QR_DIGITAL'): array
    {
        $idOrden = $orden instanceof OrdenTrabajo ? $orden->id_orden : (int) $orden;
        $ordenModel = $orden instanceof OrdenTrabajo ? $orden : OrdenTrabajo::with(['solicitud', 'tecnico'])->find($idOrden);

        $folioVale = 'VAL-'.date('Y').'-'.str_pad((string) $idOrden, 4, '0', STR_PAD_LEFT);
        $selloDigital = hash('sha256', $folioVale.'-'.$idOrden.'-'.now()->timestamp.'-'.config('app.key'));

        $detallesInsumos = [];
        foreach ($movimientos as $mov) {
            if ($mov instanceof MovimientoMaterial) {
                $detallesInsumos[] = [
                    'id_material' => $mov->id_material,
                    'material' => $mov->material?->nombre_material ?? 'Material #'.$mov->id_material,
                    'unidad_medida' => $mov->material?->unidad_medida ?? 'Pza',
                    'codigo_barras' => $mov->material?->codigo_qr_barras ?? 'N/A',
                    'cantidad_entregada' => (float) $mov->cantidad,
                    'fecha_movimiento' => $mov->fecha_movimiento?->toDateTimeString() ?? now()->toDateTimeString(),
                ];
            } elseif (is_array($mov)) {
                $detallesInsumos[] = $mov;
            }
        }

        $vale = [
            'folio_vale' => $folioVale,
            'id_orden' => $idOrden,
            'folio_solicitud' => 'FOL-'.date('Y').'-'.str_pad((string) ($ordenModel?->solicitud?->id_solicitud ?? $idOrden), 4, '0', STR_PAD_LEFT),
            'tecnico_responsable' => $ordenModel?->tecnico?->name ?? 'Técnico de Cuadrilla',
            'email_tecnico' => $ordenModel?->tecnico?->email ?? 'tecnico@tecnicos.edu.mx',
            'metodo_validacion' => $metodoValidacion ?? 'QR_DIGITAL',
            'sello_digital_seguridad' => $selloDigital,
            'fecha_emision' => now()->toDateTimeString(),
            'insumos_entregados' => $detallesInsumos,
            'estado_vale' => 'firmado_y_entregado',
        ];

        // Registro de respaldo y auditoría en bitacora_auditoria (bitacora_sistema)
        BitacoraSistema::create([
            'id_usuario' => Auth::id() ?? $ordenModel?->id_tecnico,
            'accion' => 'EMISION_VALE_DIGITAL_AUDITORIA',
            'descripcion' => json_encode([
                'folio_vale' => $folioVale,
                'id_orden' => $idOrden,
                'sello_seguridad' => $selloDigital,
                'total_insumos' => count($detallesInsumos),
                'metodo' => $metodoValidacion,
            ], JSON_UNESCAPED_UNICODE),
            'fecha' => now(),
        ]);

        return $vale;
    }
}
