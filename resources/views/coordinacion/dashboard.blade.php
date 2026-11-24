@extends('layouts.private')

@section('title', 'Coordinación')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Coordinación de mantenimiento</h1>
    @include('partials.alerts')
    <details class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
        <summary class="cursor-pointer font-bold">Métricas de mantenimiento</summary>
        <dl class="mt-3 grid gap-2 sm:grid-cols-4">
            <div><dt>Reportes recibidos</dt><dd>{{ $metricas['reportes'] }}</dd></div>
            <div><dt>Órdenes sin asignar</dt><dd>{{ $metricas['sin_asignar'] }}</dd></div>
            <div><dt>Órdenes terminadas</dt><dd>{{ $metricas['terminadas'] }}</dd></div>
            <div><dt>Costo estimado total</dt><dd>${{ number_format((float) $metricas['costo_estimado'], 2) }}</dd></div>
            <div><dt>Costo real registrado</dt><dd>${{ number_format((float) $metricas['costo_real'], 2) }} · {{ $metricas['ordenes_con_costo'] }} órdenes</dd></div>
            <div><dt>Tiempo promedio de atención</dt><dd>{{ $metricas['horas_atencion'] === null ? 'Sin datos' : $metricas['horas_atencion'].' h' }}</dd></div>
            <div><dt>Preventivos completados</dt><dd>{{ $metricas['preventivas_completadas'] }} de {{ $metricas['preventivas_generadas'] }}</dd></div>
        </dl>
        <div class="mt-3 grid gap-2 sm:grid-cols-2">
            <div><h2 class="font-bold">Reportes por prioridad</h2>
                @foreach ($porPrioridad as $grupo)<p>{{ $grupo->prioridad?->nivel ?? 'Por evaluar' }} · {{ $grupo->total }}</p>@endforeach
            </div>
            <div><h2 class="font-bold">Ubicaciones con más reportes</h2>
                @foreach ($porUbicacion as $grupo)<p>{{ $grupo->ubicacion_libre }} · {{ $grupo->total }}</p>@endforeach
            </div>
        </div>
                    <div><h2 class="font-bold">Fallas recurrentes</h2>
                @forelse ($recurrentes as $grupo)
                    <p>{{ $grupo->ubicacion_libre }} · {{ $grupo->categoria?->nombre_categoria }} · {{ $grupo->total }}</p>
                @empty
                    <p>Sin recurrencias registradas.</p>
                @endforelse
            </div>
    </details>
    <nav class="mb-5 flex flex-wrap gap-2" aria-label="Estado de las órdenes">
        @foreach (['todas' => 'Todas', 'nuevas' => 'Nuevas', 'proceso' => 'En proceso', 'terminadas' => 'Terminadas', 'duplicados' => 'Posibles duplicados', 'autorizaciones' => 'Autorizaciones'] as $filtro => $etiqueta)
            <a class="button button-secondary" href="{{ route('coordinacion.index', ['estado' => $filtro]) }}" @if (request('estado', 'todas') === $filtro) aria-current="page" @endif>{{ $etiqueta }}</a>
        @endforeach
    </nav>
    <div class="grid gap-4" data-live-panel>
        @forelse ($ordenes as $orden)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                @php
                    $solicitud = $orden->solicitud;
                @endphp
                <h2 class="font-bold">Orden #{{ $orden->getKey() }} · {{ $solicitud->ubicacion_libre ?? $solicitud->espacio?->nombre_espacio }}</h2>
                <p>Folio del reporte: #{{ $solicitud->getKey() }}</p>
                @if ($solicitud->bien_reportado)<p>Bien afectado: {{ $solicitud->bien_reportado }}</p>@endif
                <p>{{ $solicitud->descripcion_falla }}</p>
                @if ($solicitud->evidencia_url)
                    @include('partials.fotografia-evidencia', ['url' => $solicitud->evidencia_url, 'etiqueta' => 'Ver fotografía del reporte'])
                @endif
                <p>Estado: {{ str_replace('_', ' ', $orden->estado) }} · Prioridad: {{ $solicitud->prioridad?->nivel ?? 'Por evaluar' }}</p>
                <p>Técnico: {{ $orden->tecnico?->name ?? 'Sin asignar' }}</p>
                @if ($solicitud->solicitudes_hijas_count > 0)
                    <details class="my-3 rounded-lg border border-slate-200 p-3">
                        <summary class="cursor-pointer font-bold">{{ 1 + $solicitud->solicitudes_hijas_count }} reportes de esta incidencia</summary>
                        <p class="mt-2">Reporte principal #{{ $solicitud->getKey() }}. Los demás folios se muestran abajo.</p>
                        <ul class="mt-2 grid gap-3">
                            @foreach ($solicitud->solicitudesHijas as $duplicado)
                                <li class="rounded-lg border border-slate-200 p-3">
                                    <h3 class="font-bold">Reporte #{{ $duplicado->getKey() }} · {{ $duplicado->usuario?->name ?? 'Solicitante' }}</h3>
                                    <p>Hallazgo: {{ $duplicado->fecha_hallazgo?->format('d/m/Y H:i') }}</p>
                                    @if ($duplicado->bien_reportado)<p>Bien afectado: {{ $duplicado->bien_reportado }}</p>@endif
                                    <p>{{ $duplicado->descripcion_falla }}</p>
                                    @if ($duplicado->evidencia_url)
                                        @include('partials.fotografia-evidencia', ['url' => $duplicado->evidencia_url, 'etiqueta' => 'Ver evidencia del reporte #'.$duplicado->getKey()])
                                    @endif
                                    <form method="POST" action="{{ route('coordinacion.ordenes.desvincular', [$orden, $duplicado]) }}" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button-secondary">Separar reporte #{{ $duplicado->getKey() }}</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif
                @php
                    $agregables = $duplicadosPorPrincipal->get($solicitud->getKey(), collect());
                @endphp
                @if ($agregables->isNotEmpty())
                    <details class="my-3 rounded-lg border border-slate-200 p-3">
                        <summary class="cursor-pointer font-bold">Agregar duplicado a este reporte</summary>
                        <form action="{{ route('coordinacion.ordenes.duplicados.agregar', $orden) }}" method="POST" class="mt-3 flex flex-wrap items-end gap-2">
                            @csrf
                            <label>Reporte que deseas agregar
                                <select class="block w-full max-w-full rounded border p-2" name="duplicado" required>
                                    <option value="">Selecciona un folio</option>
                                    @foreach ($agregables as $candidato)
                                        <option value="{{ $candidato->id_orden }}">Reporte #{{ $candidato->id_solicitud }} · {{ \Illuminate\Support\Str::limit($candidato->descripcion_falla, 75) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="button button-secondary">Agregar al reporte #{{ $solicitud->getKey() }}</button>
                        </form>
                    </details>
                @endif
                @if ($nombreApoyo = $orden->nombre_tecnico_apoyo ?? $orden->tecnicoApoyo?->name)<p>Técnico de apoyo: {{ $nombreApoyo }}</p>@endif
                @if ($orden->monto_extra > 0)<p>Costo adicional: ${{ number_format((float) $orden->monto_extra, 2) }}</p>@endif
                <p>Costo estimado total: ${{ number_format((float) $orden->monto_estimado, 2) }}</p>
                @if (in_array($orden->estado, ['resuelta', 'cerrada', 'concluida'], true))
                    <p>Costo real: {{ $orden->costo_real === null ? 'Pendiente de captura' : '$'.number_format((float) $orden->costo_real, 2) }}</p>
                    <form method="POST" action="{{ route('coordinacion.ordenes.costo', $orden) }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        @method('PATCH')
                        <label>Costo real <input class="block rounded border p-2" type="number" name="costo_real" min="0" max="99999999.99" step="0.01" value="{{ $orden->costo_real }}" required></label>
                        <label>Justificación <input class="block rounded border p-2" name="justificacion_costo_real" value="{{ $orden->justificacion_costo_real }}" maxlength="500" required></label>
                        <button class="button button-secondary" type="submit">Guardar costo</button>
                    </form>
                @endif
                @if ($orden->reporteFinal)
                    <p>Resumen de cierre: {{ $orden->reporteFinal->resumen_ejecutivo }}</p>
                @endif
                @foreach ($orden->evidenciasCierre as $evidencia)
                    @if ($evidencia->tipo === 'fotografia')
                        @include('partials.fotografia-evidencia', ['url' => $evidencia->url_archivo, 'etiqueta' => 'Evidencia de cierre '.$loop->iteration])
                    @else
                        <a class="underline" href="{{ $evidencia->url_archivo }}">Evidencia de cierre {{ $loop->iteration }}</a>
                    @endif
                @endforeach
                @if ($orden->movimientosMaterial->isNotEmpty())
                    <h3 class="mt-2 font-semibold">Consumo por material</h3>
                    <ul class="list-disc pl-5">
                        @foreach ($orden->movimientosMaterial->groupBy('id_material') as $movimientos)
                            @php
                                $material = $movimientos->first()->material;
                                $entregado = $movimientos->where('tipo_movimiento', 'salida')->sum('cantidad');
                                $devuelto = $movimientos->where('tipo_movimiento', 'entrada')->sum('cantidad');
                            @endphp
                            <li>{{ $material->nombre_material }} · {{ number_format($entregado - $devuelto, 2) }} {{ $material->unidad_medida }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($orden->estado === 'abierta' && $solicitud->id_solicitud_padre === null)
                    <form action="{{ route('coordinacion.ordenes.asignar', $orden) }}" method="POST" class="mt-3 grid gap-2 sm:grid-cols-2">
                        @csrf
                        <label>Prioridad
                            <select name="id_prioridad" class="block w-full rounded border p-2" required>
                                @foreach ($prioridades as $prioridad)
                                    <option value="{{ $prioridad->getKey() }}" @selected($solicitud->id_prioridad === $prioridad->getKey())>{{ $prioridad->nivel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Técnico
                            <select class="block w-full rounded border p-2" name="id_tecnico" required>
                                <option value="">Selecciona un técnico</option>
                                @foreach ($tecnicos as $tecnico)
                                    @php
                                        $perfil = $tecnico->roles->first()?->pivot;
                                    @endphp
                                    <option value="{{ $tecnico->getKey() }}">{{ $tecnico->name }} · {{ $perfil?->turno_tecnico ?? 'Sin turno' }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="self-end"><input type="checkbox" name="alto_riesgo" value="1"> Trabajo de alto riesgo</label>
                        <fieldset class="assignment-option">
                            <legend class="sr-only">Costo adicional</legend>
                            <label><input type="checkbox" name="costo_extra" value="1"> Requiere costo adicional</label>
                            <label class="assignment-option-field">Monto adicional (MXN)
                                <input class="block w-full rounded border p-2" name="monto_extra" type="number" min=".01" max="99999999" step=".01">
                            </label>
                        </fieldset>
                        <fieldset class="assignment-option">
                            <legend class="sr-only">Apoyo técnico</legend>
                            <label><input type="checkbox" name="tecnico_extra" value="1"> Requiere otro técnico</label>
                            <label class="assignment-option-field">Técnico de apoyo
                                <input class="block w-full rounded border p-2" name="nombre_tecnico_apoyo" type="text" maxlength="150" placeholder="Escribe el nombre del técnico de apoyo">
                            </label>
                        </fieldset>
                        <button class="button button-primary justify-self-start">Asignar trabajo</button>
                    </form>
                    @php($candidatos = $candidatosPorReporte->get($solicitud->getKey(), collect()))
                    @if ($candidatos->isNotEmpty())
                        <form action="{{ route('coordinacion.ordenes.vincular', $orden) }}" method="POST" class="mt-4 flex flex-wrap items-end gap-2">
                            @csrf
                            <label>Reporte principal
                                <select class="block w-full max-w-full rounded border p-2" name="principal" required>
                                    <option value="">Selecciona un reporte</option>
                                    @foreach ($candidatos as $candidato)
                                        <option value="{{ $candidato->id_solicitud }}">Reporte #{{ $candidato->id_solicitud }} · {{ \Illuminate\Support\Str::limit($candidato->descripcion_falla, 75) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="button button-secondary">Vincular duplicado</button>
                        </form>
                    @endif
                @elseif ($orden->estado === 'requiere_aprobacion')
                    <form action="{{ route('coordinacion.ordenes.decidir', $orden) }}" method="POST" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <label>Justificación <input class="block rounded border p-2" name="motivo" maxlength="500" required></label>
                        <button class="button button-primary" name="decision" value="aprobar">Aprobar</button>
                        <button class="button button-secondary" name="decision" value="rechazar">Rechazar</button>
                    </form>
                @endif
                @if ($orden->tecnico && in_array($orden->estado, ['asignada', 'aprobada', 'en_proceso', 'en_espera'], true))
                    <form action="{{ route('coordinacion.ordenes.preaviso', $orden) }}" method="POST" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <label>Recolección <input type="datetime-local" class="block rounded border p-2" name="recoleccion" required></label>
                        <label>Material e instrucciones <input class="block rounded border p-2" name="instrucciones" maxlength="500" required></label>
                        <button class="button button-secondary">Avisar a Almacén</button>
                    </form>
                @endif
            </article>
        @empty
            <p>No hay órdenes para este estado.</p>
        @endforelse
    </div>
    {{ $ordenes->links() }}
@endsection

@push('scripts')
    <script src="{{ asset('mantresc-live.js').'?v=2' }}" defer></script>
@endpush
