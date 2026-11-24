<?php

use App\Controllers\AlmacenController;
use App\Controllers\AuthController;
use App\Controllers\BienEspacioController;
use App\Controllers\ReporteController;
use App\Controllers\TecnicoController;
use App\Controllers\UbicacionController;
use App\Exceptions\BusinessRuleException;
use App\Http\Middleware\RequirePersonalPassword;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\AlmacenService;
use App\Services\AuthService;
use App\Services\CoordinacionService;
use App\Services\TecnicoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

Route::get('/', function (): View {
    $avisos = DB::table('avisos_mantenimiento')->where('fecha', '>=', now())
        ->orderBy('fecha')->limit(10)->get(['titulo', 'fecha']);

    return view('public.home', compact('avisos'));
})->name('home');

Route::view('/servicios', 'public.servicios')->name('servicios');
Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('guest')->group(function (): void {
    Route::post('/login', [AuthController::class, 'autenticar'])->middleware('throttle:10,1')->name('login.store');
    Route::view('/registro', 'auth.login')->name('register');
    Route::post('/registro', [AuthController::class, 'registrar'])->middleware('throttle:5,1')->name('register.store');
    Route::view('/recuperar', 'auth.login')->name('password.request');
    Route::post('/recuperar', [AuthController::class, 'enviarRecuperacion'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer/{token}', fn (): View => view('auth.login'))->name('password.reset');
    Route::post('/restablecer', [AuthController::class, 'restablecer'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware(['auth', RequirePersonalPassword::class])->group(function (): void {
    Route::view('/perfil', 'partials.perfil')->name('perfil.show');
    Route::get('/primer-acceso', [AuthController::class, 'mostrarCambioInicial'])->name('password.initial.edit');
    Route::post('/primer-acceso', [AuthController::class, 'cambiarPasswordInicial'])->middleware('throttle:10,1')->name('password.initial.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', function (Request $request, AuthService $authService): RedirectResponse {
        $usuario = $request->user();

        if (! $authService->puedeUsarCuenta($usuario)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return redirect()->to($authService->determinarRutaDestino($usuario));
    })->name('dashboard');

    Route::middleware('role:Alumno,Profesor,Coordinador')->group(function (): void {
        Route::get('/reportes/crear', [ReporteController::class, 'crear'])->name('reportes.create');
        Route::get('/reportes/espacios/{espacio}/bienes', [ReporteController::class, 'bienes'])->name('reportes.bienes');
        Route::get('/reportes/nuevo', fn (): RedirectResponse => redirect()->route('reportes.create'));
        Route::get('/reportes/mis-reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/estado', [ReporteController::class, 'index'])->name('reportes.estado');
        Route::post('/reportes', [ReporteController::class, 'guardar'])->name('reportes.store');
    });

    Route::middleware('role:Coordinador')->group(function (): void {
        Route::get('/coordinacion/ubicaciones', [UbicacionController::class, 'index'])->name('coordinacion.ubicaciones');
        Route::post('/coordinacion/ubicaciones/edificios', [UbicacionController::class, 'guardarEdificio'])->name('coordinacion.ubicaciones.edificios');
        Route::post('/coordinacion/ubicaciones/espacios', [UbicacionController::class, 'guardarEspacio'])->name('coordinacion.ubicaciones.espacios');
        Route::get('/coordinacion/espacios/{espacio}/bienes', [BienEspacioController::class, 'index'])->name('coordinacion.bienes.index');
        Route::post('/coordinacion/espacios/{espacio}/bienes', [BienEspacioController::class, 'store'])->name('coordinacion.bienes.store');
        Route::get('/coordinacion/espacios/{espacio}/bienes/{bien}/editar', [BienEspacioController::class, 'edit'])->name('coordinacion.bienes.edit');
        Route::patch('/coordinacion/espacios/{espacio}/bienes/{bien}', [BienEspacioController::class, 'update'])->name('coordinacion.bienes.update');
        Route::post('/coordinacion/personal', [AuthController::class, 'crearPersonal'])->name('coordinacion.personal.store');
        Route::get('/coordinacion', function (Request $request, CoordinacionService $servicio): View {
            $estados = match ($request->query('estado')) {
                'nuevas' => ['abierta', 'requiere_aprobacion'],
                'autorizaciones' => ['requiere_aprobacion'],
                'proceso' => ['asignada', 'aprobada', 'en_proceso', 'en_espera', 'en_pruebas'],
                'terminadas' => ['resuelta', 'cerrada', 'concluida', 'cancelada', 'cancelada_duplicada', 'rechazada'],
                default => null,
            };
            $ordenes = OrdenTrabajo::with([
                'solicitud.espacio', 'solicitud.prioridad', 'solicitud.solicitudesHijas.usuario:id,name', 'solicitud' => fn ($query) => $query->withCount('solicitudesHijas'),
                'tecnico', 'tecnicoApoyo', 'evidenciasCierre', 'reporteFinal', 'movimientosMaterial.material',
            ])
                ->when($estados, fn ($query) => $query->whereIn('estado', $estados))
                ->when($request->query('estado') === 'duplicados', fn ($query) => $query
                    ->where(fn ($grupo) => $grupo->whereHas('solicitud.solicitudesHijas')
                        ->orWhere(fn ($posibles) => $posibles->where('estado', 'abierta')
                            ->whereHas('solicitud', fn ($solicitud) => $solicitud
                                ->whereNull('id_solicitud_padre')
                                ->whereExists($servicio->candidatosParaVincular()->selectRaw('1')
                                    ->whereColumn('origen.id_solicitud', 'solicitudes.id_solicitud'))))))
                ->latest('id_orden')->paginate(15)->withQueryString();
            $tecnicos = User::with(['roles' => fn ($query) => $query->where('nombre_rol', 'Tecnico')])
                ->where('email_tipo', 'tecnico')->where('estado_registro', 'aprobado')->orderBy('name')->get();
            $prioridades = MatrizPrioridad::orderBy('tiempo_respuesta_hrs')->get();

            $metricas = [
                'reportes' => Solicitud::count(),
                'sin_asignar' => OrdenTrabajo::where('estado', 'abierta')->count(),
                'terminadas' => OrdenTrabajo::whereIn('estado', ['resuelta', 'cerrada', 'concluida'])->count(),
                'costo_estimado' => OrdenTrabajo::sum('monto_estimado'),
                'costo_real' => OrdenTrabajo::sum('costo_real'),
                'ordenes_con_costo' => OrdenTrabajo::whereNotNull('costo_real')->count(),
            ];
            $porPrioridad = Solicitud::select('id_prioridad')->selectRaw('COUNT(*) AS total')
                ->with('prioridad')->groupBy('id_prioridad')->orderByDesc('total')->get();
            $porUbicacion = Solicitud::select('ubicacion_libre')->selectRaw('COUNT(*) AS total')
                ->whereNotNull('ubicacion_libre')->groupBy('ubicacion_libre')->orderByDesc('total')->limit(10)->get();

            $atencion = DB::table('ordenes_trabajo')->join('solicitudes', 'ordenes_trabajo.id_solicitud', '=', 'solicitudes.id_solicitud')
                ->whereNotNull('ordenes_trabajo.fecha_inicio')
                ->select(['solicitudes.created_at as recibida', 'ordenes_trabajo.fecha_inicio as atendida'])->cursor()
                ->reduce(fn (array $total, object $orden): array => [
                    $total[0] + max(0, Carbon::parse($orden->recibida)->diffInMinutes(Carbon::parse($orden->atendida))),
                    $total[1] + 1,
                ], [0.0, 0]);
            $metricas['horas_atencion'] = $atencion[1] ? round($atencion[0] / ($atencion[1] * 60), 2) : null;
            $preventivas = DB::table('avisos_mantenimiento')->join('ordenes_trabajo', 'avisos_mantenimiento.id_orden_generada', '=', 'ordenes_trabajo.id_orden');
            $metricas['preventivas_generadas'] = (clone $preventivas)->count();
            $metricas['preventivas_completadas'] = $preventivas->whereIn('ordenes_trabajo.estado', ['resuelta', 'cerrada', 'concluida'])->count();
            $recurrentes = Solicitud::select(['ubicacion_libre', 'id_categoria'])->selectRaw('COUNT(*) AS total')
                ->whereNotNull('ubicacion_libre')->whereNull('id_solicitud_padre')->with('categoria')
                ->groupBy('ubicacion_libre', 'id_categoria')->havingRaw('COUNT(*) > 1')->orderByDesc('total')->limit(10)->get();

            $origenes = $ordenes->getCollection()->filter(fn (OrdenTrabajo $orden) => $orden->estado === 'abierta'
                && $orden->solicitud?->id_solicitud_padre === null && $orden->solicitud?->solicitudes_hijas_count === 0);
            $candidatosPorReporte = $servicio->candidatosParaVincular($origenes->pluck('id_solicitud')->all())
                ->orderBy('principal.id_solicitud')->get([
                    'origen.id_solicitud as origen_id', 'principal.id_solicitud', 'principal.descripcion_falla',
                ])->groupBy('origen_id');

            $duplicadosPorPrincipal = $servicio->candidatosParaVincular()
                ->whereIn('principal.id_solicitud', $ordenes->getCollection()->pluck('id_solicitud')->all())
                ->orderBy('origen.id_solicitud')->get([
                    'principal.id_solicitud as principal_id', 'origen.id_solicitud', 'origen.descripcion_falla', 'orden_origen.id_orden',
                ])->groupBy('principal_id');

            return view('coordinacion.dashboard', compact('ordenes', 'tecnicos', 'prioridades', 'metricas', 'porPrioridad', 'porUbicacion', 'recurrentes', 'candidatosPorReporte', 'duplicadosPorPrincipal'));
        })->name('coordinacion.index');

        Route::patch('/coordinacion/ordenes/{orden}/costo', function (Request $request, OrdenTrabajo $orden): RedirectResponse {
            $datos = $request->validate([
                'costo_real' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                'justificacion_costo_real' => ['required', 'string', 'max:500'],
            ]);

            DB::transaction(function () use ($datos, $orden, $request): void {
                $actual = OrdenTrabajo::whereKey($orden->getKey())->lockForUpdate()->firstOrFail();
                abort_unless(in_array($actual->estado, ['resuelta', 'cerrada', 'concluida'], true), 409);
                $anterior = $actual->costo_real;
                $actual->update($datos);
                BitacoraSistema::create([
                    'id_usuario' => $request->user()->getKey(),
                    'accion' => 'COSTO_REAL_ORDEN',
                    'descripcion' => json_encode([
                        'id_orden' => $actual->getKey(),
                        'costo_anterior' => $anterior,
                        'costo_real' => $actual->costo_real,
                        'justificacion' => $actual->justificacion_costo_real,
                    ], JSON_THROW_ON_ERROR),
                    'fecha' => now(),
                ]);
            });

            return back()->with('status', 'Costo real registrado.');
        })->name('coordinacion.ordenes.costo');

        Route::post('/coordinacion/ordenes/{orden}/asignar', function (Request $request, OrdenTrabajo $orden, CoordinacionService $servicio): RedirectResponse {
            $datos = $request->validate([
                'id_tecnico' => ['required', 'integer'],
                'id_prioridad' => ['required', 'integer', 'exists:matrices_prioridad,id_prioridad'],
                'alto_riesgo' => ['sometimes', 'boolean'],
                'costo_extra' => ['sometimes', 'boolean'],
                'monto_extra' => ['exclude_unless:costo_extra,1', 'required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999'],
                'tecnico_extra' => ['sometimes', 'boolean'],
                'nombre_tecnico_apoyo' => ['exclude_unless:tecnico_extra,1', 'required', 'string', 'max:150'],
            ], [
                'monto_extra.required' => 'Captura el costo adicional.',
                'nombre_tecnico_apoyo.required' => 'Escribe el nombre del técnico de apoyo.',
            ]);
            $tecnico = User::where('email_tipo', 'tecnico')->where('estado_registro', 'aprobado')->findOrFail($datos['id_tecnico']);
            abort_unless($tecnico->hasRol('Tecnico'), 422);
            $montoExtra = (float) ($datos['monto_extra'] ?? 0);

            DB::transaction(function () use ($orden, $datos, $tecnico, $montoExtra, $request, $servicio): void {
                $actual = OrdenTrabajo::whereKey($orden->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($actual->estado === 'abierta' && $actual->solicitud->id_solicitud_padre === null, 409);
                $actual->solicitud->update(['id_prioridad' => $datos['id_prioridad']]);
                $servicio->asignarTecnicoAOrden(
                    $actual,
                    $tecnico,
                    $request->user(),
                    esAltoRiesgo: $request->boolean('alto_riesgo'),
                    montoExtra: $montoExtra,
                    nombreTecnicoApoyo: $datos['nombre_tecnico_apoyo'] ?? null,
                );
            });

            return back()->with('status', 'Asignación guardada.');
        })->name('coordinacion.ordenes.asignar');

        Route::post('/coordinacion/ordenes/{orden}/preaviso', function (Request $request, OrdenTrabajo $orden, AlmacenService $almacen): RedirectResponse {
            $datos = $request->validate([
                'recoleccion' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
                'instrucciones' => ['required', 'string', 'max:500'],
            ]);
            abort_unless($orden->tecnico !== null && in_array($orden->estado, ['asignada', 'aprobada', 'en_proceso', 'en_espera'], true), 409);
            $almacen->registrarPreavisoCoordinacion($orden, $request->user(), $datos['recoleccion'], $datos['instrucciones']);

            return back()->with('status', 'Preaviso enviado a Almacén.');
        })->name('coordinacion.ordenes.preaviso');
        Route::post('/coordinacion/ordenes/{orden}/decidir', function (Request $request, OrdenTrabajo $orden, CoordinacionService $servicio): RedirectResponse {
            $datos = $request->validate([
                'decision' => ['required', 'in:aprobar,rechazar'],
                'motivo' => ['required', 'string', 'max:500'],
            ]);
            DB::transaction(function () use ($orden, $datos, $request, $servicio): void {
                $actual = OrdenTrabajo::whereKey($orden->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($actual->estado === 'requiere_aprobacion', 409);
                if ($datos['decision'] === 'aprobar') {
                    $servicio->aprobarOrden($actual, $request->user(), $datos['motivo']);
                } else {
                    $servicio->rechazarOrden($actual, $request->user(), $datos['motivo']);
                }
                $actual->solicitud->update(['estado' => $actual->estado]);
                $servicio->sincronizarEstadoSolicitudesHijas($actual->solicitud, $actual->estado, $request->user(), $datos['motivo']);
            });

            return back()->with('status', 'Decisión registrada.');
        })->name('coordinacion.ordenes.decidir');

        Route::post('/coordinacion/ordenes/{orden}/vincular', function (Request $request, OrdenTrabajo $orden, CoordinacionService $servicio): RedirectResponse {
            $datos = $request->validate(['principal' => ['required', 'integer', 'exists:solicitudes,id_solicitud']]);
            $servicio->vincularDuplicado($orden->getKey(), (int) $datos['principal'], $request->user(), 'principal');

            return back()->with('status', 'Reporte vinculado.');
        })->name('coordinacion.ordenes.vincular');
        Route::post('/coordinacion/ordenes/{orden}/duplicados', function (Request $request, OrdenTrabajo $orden, CoordinacionService $servicio): RedirectResponse {
            $datos = $request->validate(['duplicado' => ['required', 'integer', 'exists:ordenes_trabajo,id_orden']]);
            $servicio->vincularDuplicado((int) $datos['duplicado'], (int) $orden->id_solicitud, $request->user(), 'duplicado');

            return back()->with('status', 'Reporte agregado al grupo de duplicados.');
        })->name('coordinacion.ordenes.duplicados.agregar');
        Route::delete('/coordinacion/ordenes/{orden}/duplicados/{duplicado}', function (OrdenTrabajo $orden, Solicitud $duplicado, CoordinacionService $servicio, Request $request): RedirectResponse {
            $servicio->desvincularDuplicado($orden, $duplicado, $request->user());

            return back()->with('status', 'Reporte separado. Su orden vuelve a estar abierta.');
        })->name('coordinacion.ordenes.desvincular');
        Route::get('/coordinacion/avisos', function (): View {
            return view('coordinacion.avisos', [
                'categorias' => CategoriaSolicitud::where('nombre_categoria', CategoriaSolicitud::INMUEBLE)->orderBy('nombre_categoria')->get(),
            ]);
        })->name('coordinacion.avisos.index');
        Route::post('/coordinacion/avisos', function (Request $request): RedirectResponse {
            $datos = $request->validate([
                'titulo' => ['required', 'string', 'max:160'],
                'ubicacion' => ['required_with:id_categoria,intervalo_dias', 'nullable', 'string', 'max:255'],
                'id_categoria' => ['required_with:ubicacion,intervalo_dias', 'nullable', 'integer', Rule::exists('categorias_solicitud', 'id_categoria')->where('nombre_categoria', CategoriaSolicitud::INMUEBLE)],
                'intervalo_dias' => ['nullable', 'integer', 'min:1', 'max:365'],
                'fecha' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            ]);
            $datos['fecha'] = Carbon::parse($datos['fecha'])->format('Y-m-d H:i:s');
            $datos['id_coordinador'] = $request->user()->getKey();
            DB::table('avisos_mantenimiento')->insertOrIgnore($datos);

            return redirect()->route('coordinacion.avisos.index')->with('status', 'Aviso publicado.');
        })->name('coordinacion.avisos');
        Route::get('/coordinacion/registro', function (): View {
            return view('coordinacion.registros', [
                'usuarios' => User::where('estado_registro', 'pendiente')->orderBy('id')->paginate(25),
            ]);
        })->name('coordinacion.registros');
        Route::get('/coordinacion/cuadrillas', function (): View {
            $tecnicos = User::where('email_tipo', 'tecnico')->where('estado_registro', 'aprobado')
                ->whereHas('roles', fn ($roles) => $roles->where('nombre_rol', 'Tecnico'))
                ->with(['roles' => fn ($roles) => $roles->where('nombre_rol', 'Tecnico')])
                ->orderBy('name')->paginate(25);

            return view('coordinacion.cuadrillas', compact('tecnicos'));
        })->name('coordinacion.cuadrillas');
        Route::patch('/coordinacion/cuadrillas/{usuario}', function (Request $request, User $usuario): RedirectResponse {
            abort_unless($usuario->email_tipo === 'tecnico' && $usuario->estado_registro === 'aprobado', 404);
            $rol = $usuario->roles()->where('nombre_rol', 'Tecnico')->firstOrFail();
            $datos = $request->validate([
                'turno_tecnico' => ['required', 'in:matutino,vespertino'],
            ]);
            $usuario->roles()->updateExistingPivot($rol->getKey(), $datos);

            return back()->with('status', 'Perfil técnico actualizado.');
        })->name('coordinacion.cuadrillas.actualizar');
        Route::post('/coordinacion/registro/{usuario}', function (Request $request, User $usuario): RedirectResponse {
            $datos = $request->validate(['decision' => ['required', 'in:aprobado,rechazado']]);
            abort_unless(User::whereKey($usuario->getKey())->where('estado_registro', 'pendiente')->update(['estado_registro' => $datos['decision']]), 409);

            return back()->with('status', 'La decisión quedó registrada.');
        })->name('coordinacion.registros.decidir');
    });

    Route::middleware('role:Tecnico')->group(function (): void {
        Route::get('/cuadrillas', [TecnicoController::class, 'misOrdenes'])->name('cuadrillas.index');
        Route::post('/tecnico/ordenes/{orden}/material', function (Request $request, OrdenTrabajo $orden, TecnicoService $tecnicoService): RedirectResponse {
            $datos = $request->validate([
                'id_material' => ['required', 'integer', 'exists:materiales,id_material'],
                'cantidad' => ['required', 'numeric', 'min:0.01', 'max:999999'],
                'recoleccion' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
                'nota' => ['nullable', 'string', 'max:500'],
            ]);
            DB::transaction(function () use ($datos, $orden, $request, $tecnicoService): void {
                $orden = OrdenTrabajo::whereKey($orden->getKey())->lockForUpdate()->firstOrFail();
                abort_unless((int) $orden->id_tecnico === (int) $request->user()->getKey()
                    && in_array($orden->estado, ['asignada', 'aprobada', 'en_proceso', 'en_espera'], true), 403);

                $porAceptar = in_array($orden->estado, ['asignada', 'aprobada'], true);
                if ($porAceptar) {
                    abort_unless($orden->tieneAprobacion(), 409);
                    $estimacion = $request->validate(['horas_estimadas_sla' => ['required', 'integer', 'min:1', 'max:168']]);
                }

                $materialId = (int) $datos['id_material'];
                $pendientes = BitacoraSistema::where('accion', 'REQUISICION_MATERIALES_ALMACEN')
                    ->where('id_usuario', $request->user()->getKey())
                    ->cursor();

                foreach ($pendientes as $registro) {
                    $detalle = json_decode($registro->descripcion, true);
                    if ((int) ($detalle['id_orden'] ?? 0) !== (int) $orden->getKey()
                        || ($detalle['estado_requisicion'] ?? 'pendiente') !== 'pendiente') {
                        continue;
                    }

                    if (collect($detalle['insumos'] ?? [])->contains(fn (array $insumo): bool => (int) $insumo['id_material'] === $materialId)) {
                        throw ValidationException::withMessages(['id_material' => 'Ya existe una requisición pendiente de este material para la orden.']);
                    }
                }

                $cantidad = round((float) $datos['cantidad'], 2);
                $existencia = Existencia::where('id_material', $materialId)->lockForUpdate()->first();
                if ($existencia === null || round((float) $existencia->cantidad_disponible - (float) $existencia->cantidad_reservada, 2) < $cantidad) {
                    throw ValidationException::withMessages(['cantidad' => 'La cantidad solicitada supera el stock libre.']);
                }
                if ($porAceptar) {
                    $tecnicoService->iniciarOrdenConCompromiso($orden, $request->user(), (int) $estimacion['horas_estimadas_sla']);
                }

                $existencia->increment('cantidad_reservada', $cantidad);

                BitacoraSistema::create([
                    'id_usuario' => $request->user()->getKey(),
                    'accion' => 'REQUISICION_MATERIALES_ALMACEN',
                    'descripcion' => json_encode([
                        'id_orden' => $orden->getKey(),
                        'reservada' => true,
                        'fecha_estimada_recoleccion' => $datos['recoleccion'],
                        'instrucciones' => $datos['nota'] ?? null,
                        'insumos' => [[
                            'id_material' => $materialId,
                            'nombre' => Material::findOrFail($materialId)->nombre_material,
                            'cantidad' => $cantidad,
                        ]],
                    ], JSON_THROW_ON_ERROR),
                    'fecha' => now(),
                ]);
            });

            return redirect()->route('cuadrillas.index', ['estado' => 'proceso'])->with('status', 'Solicitud enviada a Almacén.');
        })->name('cuadrillas.material');
        Route::post('/tecnico/estimar', [TecnicoController::class, 'estimarTiempo'])->name('cuadrillas.estimar');
        Route::post('/tecnico/actualizar-orden', [TecnicoController::class, 'actualizarEstado'])->name('cuadrillas.actualizar');
    });

    Route::middleware('role:Almacen')->group(function (): void {
        Route::get('/almacen', [AlmacenController::class, 'inventario'])->name('almacen.index');
        Route::get('/almacen/movimientos', [AlmacenController::class, 'movimientos'])->name('almacen.movimientos');
        Route::get('/almacen/material/{material}/{tipo}', [AlmacenController::class, 'operacion'])
            ->whereIn('tipo', ['entrada', 'salida'])->name('almacen.material.operacion');
        Route::post('/almacen/material/{material}/salida', [AlmacenController::class, 'salida'])->name('almacen.material.salida');
        Route::post('/almacen/movimientos/{movimiento}/devolver', [AlmacenController::class, 'devolverPrestamo'])->name('almacen.movimientos.devolver');
        Route::get('/almacen/inventario', fn (): RedirectResponse => redirect()->route('almacen.index'));
        Route::post('/almacen/material', function (Request $request): RedirectResponse {
            $datos = $request->validate([
                'nombre_material' => ['required', 'string', 'max:255'],
                'id_seccion' => ['required', 'integer', 'exists:secciones_almacen,id_seccion'],
                'unidad_medida' => ['required', 'string', 'max:50'],
            ]);
            DB::transaction(function () use ($datos): void {
                Material::create($datos)->existencia()->create([
                    'cantidad_disponible' => 0,
                    'stock_minimo' => 0,
                ]);
            });

            return back()->with('status', 'Material agregado.');
        })->name('almacen.material.store');

        Route::delete('/almacen/material/{material}', function (Material $material): RedirectResponse {
            DB::transaction(function () use ($material): void {
                $actual = Material::whereKey($material->getKey())->lockForUpdate()->firstOrFail();
                $existencia = $actual->existencia()->lockForUpdate()->first();
                if ((float) ($existencia?->cantidad_disponible ?? 0) > 0
                    || (float) ($existencia?->cantidad_reservada ?? 0) > 0
                    || $actual->movimientos()->exists()) {
                    throw ValidationException::withMessages(['material' => 'El material conserva existencias o movimientos registrados.']);
                }
                $actual->delete();
            });

            return back()->with('status', 'Material eliminado.');
        })->name('almacen.material.destroy');
        Route::post('/almacen/material/{material}/entrada', function (Request $request, Material $material, AlmacenService $servicio): RedirectResponse {
            $datos = $request->validate([
                'cantidad' => ['required', 'numeric', 'min:0.01', 'max:999999'],
                'motivo' => ['required', 'string', 'max:250'],
            ]);
            $servicio->registrarEntradaStock($material->getKey(), (float) $datos['cantidad'], $request->user(), $datos['motivo']);

            return back()->with('status', 'Entrada registrada.');
        })->name('almacen.entrada');
        Route::post('/almacen/requisiciones/{registro}/despacho', function (Request $request, BitacoraSistema $registro, AlmacenService $servicio): RedirectResponse {
            $datos = $request->validate(['entrega' => ['required', 'string', 'max:500']]);
            try {
                DB::transaction(function () use ($registro, $datos, $request, $servicio): void {
                    $actual = BitacoraSistema::whereKey($registro->getKey())->lockForUpdate()->firstOrFail();
                    $detalle = json_decode($actual->descripcion, true, flags: JSON_THROW_ON_ERROR);
                    abort_unless($actual->accion === 'REQUISICION_MATERIALES_ALMACEN' && ($detalle['estado_requisicion'] ?? 'pendiente') === 'pendiente', 409);
                    $orden = OrdenTrabajo::whereKey($detalle['id_orden'])->lockForUpdate()->firstOrFail();
                    abort_unless((int) $orden->id_tecnico === (int) $actual->id_usuario && in_array($orden->estado, ['asignada', 'aprobada', 'en_proceso', 'en_espera'], true), 409);
                    if ($detalle['reservada'] ?? false) {
                        foreach ($detalle['insumos'] as $insumo) {
                            $existencia = Existencia::where('id_material', $insumo['id_material'])->lockForUpdate()->firstOrFail();
                            abort_unless((float) $existencia->cantidad_reservada >= (float) $insumo['cantidad'], 409);
                            $existencia->decrement('cantidad_reservada', $insumo['cantidad']);
                        }
                    }
                    $servicio->despacharMateriales($orden, $request->user(), $detalle['insumos']);
                    $detalle['estado_requisicion'] = 'entregada';
                    $detalle['entrega'] = $datos['entrega'];
                    $detalle['fecha_entrega'] = now()->toDateTimeString();
                    $detalle['id_almacenista'] = $request->user()->getKey();
                    $actual->update(['descripcion' => json_encode($detalle, JSON_THROW_ON_ERROR)]);
                });
            } catch (BusinessRuleException $exception) {
                throw ValidationException::withMessages(['entrega' => $exception->getMessage()]);
            }

            return back()->with('status', 'Entrega registrada.');
        })->name('almacen.requisiciones.despacho');
        Route::post('/almacen/requisiciones/{registro}/devolucion', function (Request $request, BitacoraSistema $registro, AlmacenService $servicio): RedirectResponse {
            $datos = $request->validate([
                'cantidad' => ['required', 'numeric', 'min:0', 'max:999999'],
                'motivo' => ['required', 'string', 'max:500'],
            ]);
            DB::transaction(function () use ($registro, $datos, $request, $servicio): void {
                $actual = BitacoraSistema::whereKey($registro->getKey())->lockForUpdate()->firstOrFail();
                $detalle = json_decode($actual->descripcion, true, flags: JSON_THROW_ON_ERROR);
                abort_unless($actual->accion === 'REQUISICION_MATERIALES_ALMACEN'
                    && ($detalle['estado_requisicion'] ?? null) === 'entregada'
                    && count($detalle['insumos'] ?? []) === 1, 409);
                $insumo = $detalle['insumos'][0];
                $devuelto = round((float) $datos['cantidad'], 2);

                if ($devuelto > (float) $insumo['cantidad']) {
                    throw ValidationException::withMessages(['cantidad' => 'La cantidad devuelta supera la entregada.']);
                }

                if ($devuelto > 0) {
                    $movimiento = $servicio->registrarEntradaStock((int) $insumo['id_material'], $devuelto, $request->user(), $datos['motivo']);
                    $movimiento->update(['id_orden' => $detalle['id_orden']]);
                }

                $detalle['estado_requisicion'] = 'cerrada';
                $detalle['cantidad_devuelta'] = $devuelto;
                $detalle['devolucion'] = $datos['motivo'];
                $detalle['fecha_devolucion'] = now()->toDateTimeString();
                $actual->update(['descripcion' => json_encode($detalle, JSON_THROW_ON_ERROR)]);
            });

            return back()->with('status', 'Devolución o consumo registrado.');
        })->name('almacen.requisiciones.devolucion');
        Route::post('/almacen/requisiciones/{registro}/cancelar', function (Request $request, BitacoraSistema $registro): RedirectResponse {
            $datos = $request->validate(['motivo' => ['required', 'string', 'max:500']]);
            DB::transaction(function () use ($registro, $datos): void {
                $actual = BitacoraSistema::whereKey($registro->getKey())->lockForUpdate()->firstOrFail();
                $detalle = json_decode($actual->descripcion, true, flags: JSON_THROW_ON_ERROR);
                abort_unless($actual->accion === 'REQUISICION_MATERIALES_ALMACEN' && ($detalle['estado_requisicion'] ?? 'pendiente') === 'pendiente', 409);
                if ($detalle['reservada'] ?? false) {
                    foreach ($detalle['insumos'] as $insumo) {
                        $existencia = Existencia::where('id_material', $insumo['id_material'])->lockForUpdate()->firstOrFail();
                        abort_unless((float) $existencia->cantidad_reservada >= (float) $insumo['cantidad'], 409);
                        $existencia->decrement('cantidad_reservada', $insumo['cantidad']);
                    }
                }
                $detalle['estado_requisicion'] = 'cancelada';
                $detalle['motivo_cancelacion'] = $datos['motivo'];
                $actual->update(['descripcion' => json_encode($detalle, JSON_THROW_ON_ERROR)]);
            });

            return back()->with('status', 'Requisición cancelada.');
        })->name('almacen.requisiciones.cancelar');
        Route::get('/almacen/requisiciones', [AlmacenController::class, 'requisicionesPendientes'])->name('almacen.requisiciones');
    });
});
