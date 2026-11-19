<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\MovimientoMaterial;
use App\Models\OrdenTrabajo;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\AlmacenService;
use App\Services\AuthService;
use App\Services\CoordinacionService;
use App\Services\EvidenciaService;
use App\Services\SolicitudService;
use App\Services\TecnicoService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function help(): View
    {
        return view('public.home', ['help' => true]);
    }

    public function dashboard(Request $request, AuthService $auth): RedirectResponse
    {
        return redirect($auth->determinarRutaDestino($request->user()));
    }

    public function createReport(): View
    {
        return $this->page('Nuevo reporte', 'report-form', ['edificios' => Edificio::with('espacios')->get(), 'categorias' => CategoriaSolicitud::all(), 'prioridades' => MatrizPrioridad::all()]);
    }

    public function storeReport(Request $request, SolicitudService $service): RedirectResponse
    {
        $data = $request->validate([
            'id_edificio' => ['required', 'integer', 'exists:edificios,id_edificio'],
            'id_espacio' => ['required', 'integer', 'exists:espacios,id_espacio'],
            'id_categoria' => ['required', 'integer', 'exists:categorias_solicitud,id_categoria'],
            'id_prioridad' => ['nullable', 'integer', 'exists:matrices_prioridad,id_prioridad'],
            'descripcion_falla' => ['required', 'string', 'min:10', 'max:5000'],
            'fecha_hallazgo' => ['required', 'date', 'before_or_equal:now'],
            'archivo_evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        try {
            $report = $service->crearSolicitud($data, $request->user());
        } catch (BusinessRuleException $e) {
            throw ValidationException::withMessages(['descripcion_falla' => $e->getMessage()]);
        }

        return redirect()->route('reportes.show', $report)->with('status', 'Reporte registrado. Conserva el folio para dar seguimiento.');
    }

    public function reports(Request $request): View
    {
        $base = Solicitud::where('id_usuario', $request->user()->id);

        return $this->page('Mis reportes', 'reports', ['stats' => $this->reportStats(clone $base), 'solicitudes' => $this->filterReports($request, $base)->with(['espacio.edificio', 'categoria', 'prioridad'])->latest('id_solicitud')->paginate(10)->withQueryString()]);
    }

    public function report(Request $request, Solicitud $solicitud): View
    {
        abort_unless($request->user()->isCoordinador() || (int) $solicitud->id_usuario === $request->user()->id, 404);
        $solicitud->load(['espacio.edificio', 'categoria', 'prioridad', 'ordenTrabajo.tecnico', 'ordenTrabajo.historialEstados', 'ordenTrabajo.evidenciasCierre', 'solicitudPadre.ordenTrabajo.tecnico', 'solicitudPadre.ordenTrabajo.historialEstados', 'solicitudPadre.ordenTrabajo.evidenciasCierre']);

        return $this->page('Detalle del reporte', 'report-detail', ['solicitud' => $solicitud, 'orden' => $solicitud->solicitudPadre?->ordenTrabajo ?? $solicitud->ordenTrabajo]);
    }

    public function coordination(): View
    {
        return $this->page('Coordinación', 'summary', ['stats' => $this->reportStats(Solicitud::query()), 'solicitudes' => Solicitud::with(['espacio.edificio', 'prioridad'])->latest('id_solicitud')->limit(5)->get(), 'actividad' => BitacoraSistema::with('usuario')->latest('id_bitacora')->limit(6)->get(), 'pendientes' => OrdenTrabajo::where('estado', 'requiere_aprobacion')->count()]);
    }

    public function incidents(Request $request): View
    {
        return $this->page('Incidencias entrantes', 'incidents', ['solicitudes' => $this->filterReports($request, Solicitud::query())->with(['espacio.edificio', 'categoria', 'prioridad', 'ordenTrabajo'])->latest('id_solicitud')->paginate(10)->withQueryString(), 'prioridades' => MatrizPrioridad::all()]);
    }

    public function duplicates(): View
    {
        $groups = Solicitud::with(['espacio.edificio', 'usuario', 'categoria'])->whereNull('id_solicitud_padre')->whereIn('estado', ['pendiente', 'en_revision', 'abierta'])->get()->groupBy(fn (Solicitud $s): string => $s->id_espacio.'-'.$s->id_categoria)->filter(fn ($g): bool => $g->count() > 1);

        return $this->page('Posibles duplicados', 'duplicates', ['groups' => $groups]);
    }

    public function groupReports(Request $request, CoordinacionService $service): RedirectResponse
    {
        $data = $request->validate(['padre' => ['required', 'integer', 'exists:solicitudes,id_solicitud'], 'hijas' => ['required', 'array', 'min:1'], 'hijas.*' => ['required', 'integer', 'distinct', 'different:padre', 'exists:solicitudes,id_solicitud'], 'confirmacion' => ['accepted']]);
        DB::transaction(function () use ($data, $request, $service): void {
            $reports = Solicitud::whereIn('id_solicitud', array_merge([$data['padre']], $data['hijas']))->lockForUpdate()->get();
            $parent = $reports->firstWhere('id_solicitud', $data['padre']);
            foreach ($reports as $report) {
                if ($report->id_solicitud_padre || $report->id_espacio !== $parent->id_espacio || $report->id_categoria !== $parent->id_categoria || ! in_array($report->estado, ['pendiente', 'en_revision', 'abierta'], true)) {
                    throw ValidationException::withMessages(['hijas' => 'Solo se pueden agrupar reportes pendientes de la misma ubicación y categoría.']);
                }
            }
            $service->consolidarReportesDuplicados($parent, array_map('intval', $data['hijas']), $request->user());
        });

        return back()->with('status', 'Reportes agrupados. El seguimiento se vincula al reporte base.');
    }

    public function assignment(): View
    {
        $technicians = User::with('roles')->where(fn (Builder $q) => $q->where('email_tipo', 'tecnico')->orWhereHas('roles', fn (Builder $r) => $r->whereIn('nombre_rol', ['Tecnico', 'Técnico', 'Cuadrilla'])))->withCount(['ordenesTrabajo as activas' => fn (Builder $q) => $q->whereIn('estado', ['asignada', 'en_proceso', 'requiere_aprobacion'])])->get();

        return $this->page('Asignación de cuadrillas', 'assignment', ['ordenes' => OrdenTrabajo::with('solicitud.espacio.edificio')->whereIn('estado', ['abierta', 'asignada', 'aprobada'])->get(), 'tecnicos' => $technicians]);
    }

    public function assign(Request $request, CoordinacionService $service): RedirectResponse
    {
        $data = $request->validate(['id_orden' => ['required', 'integer', 'exists:ordenes_trabajo,id_orden'], 'id_tecnico' => ['required', 'integer', 'exists:users,id'], 'turno' => ['required', Rule::in(['Matutino', 'Vespertino'])], 'horas' => ['required', 'integer', 'min:1', 'max:720'], 'instrucciones' => ['required', 'string', 'max:3000'], 'monto' => ['required', 'numeric', 'min:0', 'max:99999999'], 'alto_riesgo' => ['nullable', 'boolean']]);
        $technician = User::with('roles')->findOrFail($data['id_tecnico']);
        abort_unless($technician->isTecnico(), 422, 'La persona seleccionada debe tener perfil técnico.');
        $shifts = $technician->roles->pluck('pivot.turno_tecnico')->filter();
        if ($shifts->isNotEmpty() && ! $shifts->contains($data['turno'])) {
            throw ValidationException::withMessages(['id_tecnico' => 'El técnico no está disponible en el turno seleccionado.']);
        }
        DB::transaction(function () use ($data, $service, $technician, $request): void {
            $order = OrdenTrabajo::lockForUpdate()->findOrFail($data['id_orden']);
            abort_unless(in_array($order->estado, ['abierta', 'asignada', 'aprobada'], true), 422, 'Esta orden ya no admite asignación.');
            $service->asignarTecnicoAOrden($order, $technician, $request->user(), (float) $data['monto'], $request->boolean('alto_riesgo'));
            $this->audit($request, 'INSTRUCCIONES_ASIGNACION', $data);
        });

        return redirect()->route('coordinacion.incidencias')->with('status', 'Cuadrilla asignada. Los trabajos de costo o riesgo elevado esperan autorización.');
    }

    public function approvals(Request $request): View
    {
        return $this->page('Autorizaciones especiales', 'approvals', ['ordenes' => OrdenTrabajo::with(['solicitud.espacio.edificio', 'aprobaciones.coordinador'])->where('requiere_aprobacion', true)->when($request->query('estado') === 'revisadas', fn (Builder $q) => $q->where('estado', '!=', 'requiere_aprobacion'), fn (Builder $q) => $q->where('estado', 'requiere_aprobacion'))->latest('id_orden')->paginate(10)->withQueryString()]);
    }

    public function decide(Request $request, OrdenTrabajo $orden, CoordinacionService $service): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['aprobar', 'rechazar', 'cotizacion', 'dictamen'])], 'motivo' => ['required', 'string', 'min:5', 'max:3000']]);
        DB::transaction(function () use ($orden, $data, $service, $request): void {
            $order = OrdenTrabajo::lockForUpdate()->findOrFail($orden->id_orden);
            abort_unless($order->estado === 'requiere_aprobacion', 422, 'Esta autorización ya fue atendida.');
            if ($data['decision'] === 'aprobar') {
                $service->aprobarOrden($order, $request->user(), $data['motivo']);
                $order->solicitud?->update(['estado' => $order->estado]);
            } elseif ($data['decision'] === 'rechazar') {
                $service->rechazarOrden($order, $request->user(), $data['motivo']);
                $order->solicitud?->update(['estado' => 'rechazada']);
            }
            $this->audit($request, 'REVISION_AUTORIZACION', ['id_orden' => $order->id_orden] + $data);
        });

        return back()->with('status', 'Decisión registrada con su motivo en la bitácora.');
    }

    public function orders(Request $request): View
    {
        $base = OrdenTrabajo::where('id_tecnico', $request->user()->id);
        $query = clone $base;
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->filled('q')) {
            $term = mb_substr((string) $request->query('q'), 0, 100);
            $id = preg_match('/OT-\d{4}-(\d+)/i', $term, $m) ? (int) $m[1] : (int) $term;
            $query->where(fn (Builder $q) => $q->where('id_orden', $id)->orWhereHas('solicitud', fn (Builder $s) => $s->where('descripcion_falla', 'like', '%'.$term.'%')->orWhereHas('espacio', fn (Builder $e) => $e->where('nombre_espacio', 'like', '%'.$term.'%'))));
        }

        return $this->page('Mis órdenes de trabajo', 'orders', ['stats' => ['Asignadas' => (clone $base)->where('estado', 'asignada')->count(), 'En atención' => (clone $base)->where('estado', 'en_proceso')->count(), 'Concluidas' => (clone $base)->where('estado', 'resuelta')->count()], 'ordenes' => $query->with(['solicitud.espacio.edificio', 'solicitud.prioridad'])->latest('id_orden')->paginate(10)->withQueryString()]);
    }

    public function order(Request $request, OrdenTrabajo $orden): View
    {
        $this->authorizeOrder($request, $orden);

        return $this->page('Atención de la orden', 'order', $this->orderData($orden));
    }

    public function progress(Request $request, OrdenTrabajo $orden, TecnicoService $service, EvidenciaService $evidence): RedirectResponse
    {
        $this->authorizeOrder($request, $orden);
        $data = $request->validate(['fase' => ['required', Rule::in(['diagnostico', 'espera_material', 'pruebas'])], 'horas' => ['required', 'integer', 'min:1', 'max:720'], 'notas' => ['required', 'string', 'min:5', 'max:5000'], 'evidencia' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240']]);
        try {
            DB::transaction(function () use ($request, $orden, $data, $service, $evidence): void {
                $locked = OrdenTrabajo::lockForUpdate()->findOrFail($orden->id_orden);
                $this->authorizeOrder($request, $locked);
                $this->assertActiveOrder($locked);
                if ($locked->fecha_inicio === null) {
                    $service->iniciarOrdenConCompromiso($locked, $request->user(), (int) $data['horas'], null, $data['notas']);
                }
                $details = ['id_orden' => $locked->id_orden] + $data;
                unset($details['evidencia']);
                if ($request->hasFile('evidencia')) {
                    $details['evidencia_url'] = $evidence->almacenarEvidenciaSolicitud($request->file('evidencia'));
                }
                $this->audit($request, 'AVANCE_ORDEN', $details);
            });
        } catch (BusinessRuleException $e) {
            throw ValidationException::withMessages(['fase' => $e->getMessage()]);
        }

        return back()->with('status', 'Avance guardado en la orden.');
    }

    public function requestMaterials(Request $request, OrdenTrabajo $orden): View
    {
        $this->authorizeOrder($request, $orden);

        return $this->page('Solicitud de materiales', 'material-request', $this->orderData($orden) + ['materiales' => Material::with('existencia')->get()]);
    }

    public function task(Request $request, OrdenTrabajo $orden, int $tarea, TecnicoService $service): RedirectResponse
    {
        $this->authorizeOrder($request, $orden);
        $this->assertActiveOrder($orden);
        $data = $request->validate(['completada' => ['required', 'boolean']]);
        $task = $orden->tareas()->findOrFail($tarea);
        $service->toggleTarea($task, $request->user(), (bool) $data['completada']);

        return back()->with('status', 'Lista de tareas actualizada.');
    }

    public function storeMaterials(Request $request, OrdenTrabajo $orden, TecnicoService $service): RedirectResponse
    {
        $this->authorizeOrder($request, $orden);
        $data = $request->validate(['id_material' => ['required', 'integer', 'exists:materiales,id_material'], 'cantidad' => ['required', 'numeric', 'gt:0', 'max:999999'], 'urgencia' => ['required', Rule::in(['normal', 'urgente'])], 'justificacion' => ['required', 'string', 'min:5', 'max:3000']]);
        $this->assertActiveOrder($orden);
        $service->solicitarInsumos($orden, $request->user(), [['id_material' => (int) $data['id_material'], 'cantidad' => (float) $data['cantidad']]], $data['urgencia'].': '.$data['justificacion']);

        return redirect()->route('cuadrillas.show', $orden)->with('status', 'Solicitud enviada a la bandeja de almacén.');
    }

    public function closure(Request $request, OrdenTrabajo $orden): View
    {
        $this->authorizeOrder($request, $orden);

        return $this->page('Evidencia y cierre', 'closure', $this->orderData($orden));
    }

    public function close(Request $request, OrdenTrabajo $orden, TecnicoService $service): RedirectResponse
    {
        $this->authorizeOrder($request, $orden);
        $data = $request->validate(['resumen' => ['required', 'string', 'min:10', 'max:5000'], 'evidencia' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'], 'confirmacion' => ['accepted']]);
        try {
            DB::transaction(function () use ($orden, $service, $request, $data): void {
                $locked = OrdenTrabajo::lockForUpdate()->findOrFail($orden->id_orden);
                $this->authorizeOrder($request, $locked);
                abort_unless($locked->estado === 'en_proceso', 422, 'Inicia la atención antes de cerrar la orden.');
                $service->cerrarOrdenFormal($locked, $request->user(), now(), [$request->file('evidencia')], $data['resumen']);
            });
        } catch (BusinessRuleException $e) {
            throw ValidationException::withMessages(['evidencia' => $e->getMessage()]);
        }

        return redirect()->route('cuadrillas.show', $orden)->with('status', 'Orden concluida con evidencia y constancia de cierre.');
    }

    public function inventory(Request $request): View
    {
        $query = Material::with(['existencia', 'seccion']);
        if ($request->filled('q')) {
            $term = mb_substr((string) $request->query('q'), 0, 100);
            $query->where(fn (Builder $q) => $q->where('nombre_material', 'like', '%'.$term.'%')->orWhere('codigo_qr_barras', 'like', '%'.$term.'%'));
        }
        if ($request->filled('seccion')) {
            $query->where('id_seccion', $request->query('seccion'));
        }
        if (in_array($request->query('stock'), ['bajo', 'disponible'], true)) {
            $operator = $request->query('stock') === 'bajo' ? '<=' : '>';
            $query->whereHas('existencia', fn (Builder $q) => $q->whereColumn('cantidad_disponible', $operator, 'stock_minimo'));
        }

        return $this->page('Inventario', 'inventory', ['stats' => ['Insumos catalogados' => Material::count(), 'Bajo el mínimo' => Material::whereHas('existencia', fn (Builder $q) => $q->whereColumn('cantidad_disponible', '<=', 'stock_minimo'))->count(), 'Salidas de hoy' => MovimientoMaterial::where('tipo_movimiento', 'salida')->whereDate('fecha_movimiento', today())->count()], 'materiales' => $query->orderBy('nombre_material')->paginate(10)->withQueryString(), 'secciones' => SeccionAlmacen::all()]);
    }

    public function entry(): View
    {
        return $this->page('Entrada de inventario', 'entry', ['materiales' => Material::with(['existencia', 'seccion'])->orderBy('nombre_material')->get()]);
    }

    public function storeEntry(Request $request, AlmacenService $service): RedirectResponse
    {
        $data = $request->validate(['id_material' => ['required', 'integer', 'exists:materiales,id_material'], 'cantidad' => ['required', 'numeric', 'gt:0', 'max:999999'], 'concepto' => ['required', Rule::in(['Compra institucional', 'Donación', 'Transferencia', 'Ajuste físico'])], 'referencia' => ['required', 'string', 'max:255']]);
        $service->registrarEntradaStock((int) $data['id_material'], (float) $data['cantidad'], $request->user(), $data['concepto'].' | '.$data['referencia']);

        return redirect()->route('almacen.index')->with('status', 'Entrada registrada. La existencia y la bitácora fueron actualizadas.');
    }

    public function requisitions(Request $request): View
    {
        $query = BitacoraSistema::with('usuario')->where('accion', 'SOLICITUD_INSUMOS_TECNICO');
        if ($request->filled('q')) {
            $query->where('id_bitacora', $request->integer('q'));
        }
        $delivered = $this->deliveredIds();
        $all = $query->latest('id_bitacora')->get()->map(fn ($b): array => ['registro' => $b, 'datos' => json_decode($b->descripcion, true), 'entregada' => in_array($b->id_bitacora, $delivered, true)]);
        $rows = $all->filter(fn (array $r): bool => $request->query('estado') === 'entregadas' ? $r['entregada'] : ! $r['entregada']);

        return $this->page('Solicitudes de materiales', 'requisitions', ['requisiciones' => $rows, 'materiales' => Material::with(['existencia', 'seccion'])->get()->keyBy('id_material'), 'stats' => ['Pendientes de entrega' => $all->where('entregada', false)->count(), 'Solicitudes entregadas' => count($delivered), 'Insumos solicitados' => $rows->sum(fn (array $r): int => count($r['datos']['items'] ?? []))]]);
    }

    public function delivery(BitacoraSistema $requisicion): View
    {
        abort_unless($requisicion->accion === 'SOLICITUD_INSUMOS_TECNICO', 404);
        $data = json_decode($requisicion->descripcion, true);
        $delivery = BitacoraSistema::with('usuario')->where('accion', 'ENTREGA_REQUISICION')->get()->first(fn ($b): bool => (int) (json_decode($b->descripcion, true)['id_requisicion'] ?? 0) === $requisicion->id_bitacora);

        return $this->page('Registrar entrega', 'delivery', ['requisicion' => $requisicion->load('usuario'), 'datos' => $data, 'materiales' => Material::with(['existencia', 'seccion'])->whereIn('id_material', array_column($data['items'], 'id_material'))->get()->keyBy('id_material'), 'entregada' => $delivery !== null, 'entrega' => $delivery, 'vale' => $delivery ? json_decode($delivery->descripcion, true) : []]);
    }

    public function dispatch(Request $request, BitacoraSistema $requisicion, AlmacenService $service): RedirectResponse
    {
        abort_unless($requisicion->accion === 'SOLICITUD_INSUMOS_TECNICO', 404);
        $data = $request->validate(['receptor' => ['required', 'string', 'max:255'], 'observaciones' => ['nullable', 'string', 'max:3000'], 'confirmacion' => ['accepted']]);
        try {
            DB::transaction(function () use ($request, $requisicion, $service, $data): void {
                $locked = BitacoraSistema::lockForUpdate()->findOrFail($requisicion->id_bitacora);
                if (in_array($locked->id_bitacora, $this->deliveredIds(), true)) {
                    throw ValidationException::withMessages(['confirmacion' => 'Esta solicitud ya fue entregada. No se descontó inventario nuevamente.']);
                }
                $details = json_decode($locked->descripcion, true);
                $order = OrdenTrabajo::lockForUpdate()->findOrFail($details['id_orden']);
                $this->assertActiveOrder($order);
                $service->despacharMateriales($order, $request->user(), $details['items']);
                $this->audit($request, 'ENTREGA_REQUISICION', ['id_requisicion' => $locked->id_bitacora, 'id_orden' => $order->id_orden, 'metodo' => 'Cotejo manual', 'folio' => 'VAL-'.now()->year.'-'.$locked->id_bitacora] + $data);
            });
        } catch (BusinessRuleException $e) {
            throw ValidationException::withMessages(['confirmacion' => $e->getMessage()]);
        }

        return back()->with('status', 'Entrega registrada. Vale VAL-'.now()->year.'-'.$requisicion->id_bitacora.' emitido con cotejo manual.');
    }

    public function ledger(Request $request): View
    {
        return $this->page('Bitácora de almacén', 'ledger', ['movimientos' => $this->movementQuery($request)->latest('id_movimiento')->paginate(10)->withQueryString()]);
    }

    public function exportLedger(Request $request): View|StreamedResponse
    {
        $query = $this->movementQuery($request)->latest('id_movimiento');
        if ($request->query('formato') === 'pdf') {
            return view('partials.simic-print', ['movimientos' => $query->get()]);
        }

        return response()->streamDownload(function () use ($query): void {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Folio', 'Fecha', 'Material', 'Movimiento', 'Cantidad', 'Responsable'], ',', '"', '');
            foreach ($query->lazy(200) as $m) {
                $cells = [$m->id_movimiento, $m->fecha_movimiento->format('Y-m-d H:i'), $m->material->nombre_material, $m->tipo_movimiento, $m->cantidad, $m->usuarioAlmacen?->name];
                $cells = array_map(static fn ($s): string => preg_match('/^[=+@\-\t\r]/', (string) $s) ? "'".$s : (string) $s, $cells);
                fputcsv($file, $cells, ',', '"', '');
            }
            fclose($file);
        }, 'bitacora-almacen.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function page(string $title, string $screen, array $data = []): View
    {
        return view('partials.simic-page', ['title' => $title, 'screen' => $screen] + $data);
    }

    private function reportStats(Builder $query): array
    {
        return ['En atención' => (clone $query)->whereIn('estado', ['asignada', 'en_proceso'])->count(), 'En espera' => (clone $query)->whereIn('estado', ['pendiente', 'en_revision', 'requiere_aprobacion', 'abierta'])->count(), 'Concluidos' => (clone $query)->whereIn('estado', ['resuelta', 'cerrada'])->count()];
    }

    private function filterReports(Request $request, Builder $query): Builder
    {
        if ($request->filled('q')) {
            $term = mb_substr((string) $request->query('q'), 0, 100);
            $id = preg_match('/(?:FOL|INC)-\d{4}-(\d+)/i', $term, $m) ? (int) $m[1] : (int) $term;
            $query->where(fn (Builder $q) => $q->where('id_solicitud', $id)->orWhere('descripcion_falla', 'like', '%'.$term.'%')->orWhereHas('espacio', fn (Builder $e) => $e->where('nombre_espacio', 'like', '%'.$term.'%')->orWhereHas('edificio', fn (Builder $b) => $b->where('nombre', 'like', '%'.$term.'%'))));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->filled('prioridad')) {
            $query->where('id_prioridad', $request->query('prioridad'));
        }

        return $query;
    }

    private function movementQuery(Request $request): Builder
    {
        $data = $request->validate(['desde' => ['nullable', 'date'], 'hasta' => ['nullable', 'date', 'after_or_equal:desde'], 'tipo' => ['nullable', Rule::in(['entrada', 'salida'])], 'material' => ['nullable', 'integer'], 'q' => ['nullable', 'string', 'max:100']]);
        $query = MovimientoMaterial::with(['material', 'usuarioAlmacen']);
        if (! empty($data['desde'])) {
            $query->whereDate('fecha_movimiento', '>=', $data['desde']);
        }
        if (! empty($data['hasta'])) {
            $query->whereDate('fecha_movimiento', '<=', $data['hasta']);
        }
        if (! empty($data['tipo'])) {
            $query->where('tipo_movimiento', $data['tipo']);
        }
        if (! empty($data['material'])) {
            $query->where('id_material', $data['material']);
        }
        if (! empty($data['q'])) {
            $query->whereHas('material', fn (Builder $q) => $q->where('nombre_material', 'like', '%'.$data['q'].'%'));
        }

        return $query;
    }

    private function authorizeOrder(Request $request, OrdenTrabajo $orden): void
    {
        abort_unless($request->user()->isAdmin() || (int) $orden->id_tecnico === $request->user()->id, 404);
    }

    private function assertActiveOrder(OrdenTrabajo $orden): void
    {
        abort_unless(in_array($orden->estado, ['abierta', 'asignada', 'aprobada', 'en_proceso'], true), 422, 'La orden no admite esta operación en su estado actual.');
    }

    private function orderData(OrdenTrabajo $orden): array
    {
        $orden->load(['solicitud.espacio.edificio', 'solicitud.categoria', 'tecnico', 'historialEstados', 'evidenciasCierre', 'reporteFinal', 'tareas']);
        $logs = BitacoraSistema::whereIn('accion', ['AVANCE_ORDEN', 'INSTRUCCIONES_ASIGNACION'])->latest('id_bitacora')->get()->map(fn ($b) => json_decode($b->descripcion, true))->filter(fn ($d): bool => (int) ($d['id_orden'] ?? 0) === $orden->id_orden);

        return ['orden' => $orden, 'solicitud' => $orden->solicitud, 'avance' => $logs->first() ?? [], 'asignacion' => $logs->first(fn ($d): bool => isset($d['instrucciones'])) ?? []];
    }

    private function deliveredIds(): array
    {
        return BitacoraSistema::where('accion', 'ENTREGA_REQUISICION')->get()->map(fn ($b): int => (int) (json_decode($b->descripcion, true)['id_requisicion'] ?? 0))->all();
    }

    private function audit(Request $request, string $action, array $details): void
    {
        BitacoraSistema::create(['id_usuario' => $request->user()->id, 'accion' => $action, 'descripcion' => json_encode($details, JSON_UNESCAPED_UNICODE), 'fecha' => now()]);
    }
}
