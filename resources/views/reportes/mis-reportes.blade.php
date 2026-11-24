@extends('layouts.private')

@section('title', request()->routeIs('reportes.estado') ? 'Estado de reporte' : 'Mis reportes')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">@yield('title')</h1>
        <a class="button button-primary" href="{{ route('reportes.create') }}">Nuevo reporte</a>
    </div>
    @include('partials.alerts')
    <div class="grid gap-4" data-live-panel>
        @forelse ($solicitudes as $solicitud)
            @php
                $ordenSeguimiento = $solicitud->solicitudPadre?->ordenTrabajo ?? $solicitud->ordenTrabajo;
                $estadoSeguimiento = $ordenSeguimiento?->estado ?? $solicitud->estado;
                $interrumpido = in_array($estadoSeguimiento, ['cancelada', 'cancelada_duplicada', 'rechazada'], true);
                $etapaActual = match ($estadoSeguimiento) {
                    'resuelta', 'cerrada', 'concluida' => 4,
                    'en_proceso', 'en_espera', 'en_pruebas' => 3,
                    default => $ordenSeguimiento?->fecha_inicio ? 3 : ($ordenSeguimiento?->id_tecnico ? 2 : 1),
                };
            @endphp
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">Reporte #{{ $solicitud->getKey() }} · {{ $solicitud->categoria?->nombre_categoria }}</h2>
                <p>{{ $solicitud->ubicacion_libre ?? $solicitud->espacio?->nombre_espacio }}</p>
                @if ($solicitud->bien_reportado)<p>Bien afectado: {{ $solicitud->bien_reportado }}</p>@endif
                <p>{{ $solicitud->descripcion_falla }}</p>
                @if ($solicitud->evidencia_url)
                    @include('partials.fotografia-evidencia', ['url' => $solicitud->evidencia_url, 'etiqueta' => 'Ver fotografía del reporte'])
                @endif
                @foreach ($ordenSeguimiento?->evidenciasCierre ?? [] as $evidencia)
                    @if ($evidencia->tipo === 'fotografia')
                        @include('partials.fotografia-evidencia', ['url' => $evidencia->url_archivo, 'etiqueta' => 'Fotografía de cierre '.$loop->iteration])
                    @endif
                @endforeach
                <p>Estado: {{ match ($estadoSeguimiento) {
                    'pendiente', 'abierta' => 'Reporte recibido',
                    'asignada' => 'Técnico asignado',
                    'requiere_aprobacion' => 'Pendiente de autorización',
                    'en_proceso', 'en_pruebas' => 'En mantenimiento',
                    'en_espera' => 'En espera',
                    'resuelta', 'cerrada', 'concluida' => 'Solucionado',
                    default => ucfirst(str_replace('_', ' ', $solicitud->estado)),
                } }}</p>
                @if (request()->routeIs('reportes.estado'))
                    <ol class="report-progress" aria-label="Seguimiento del reporte">
                        @foreach (['Reporte enviado' => '✉', 'Técnico asignado' => '⛑', 'En mantenimiento' => '⚙', 'Solucionado y terminado' => '✓'] as $etapa => $icono)
                            <li data-reached="{{ (int) ($loop->iteration <= $etapaActual) }}" @if (! $interrumpido && $loop->iteration === $etapaActual) aria-current="step" @endif>
                                <span aria-hidden="true">{{ $icono }}</span>
                                {{ $etapa }}
                                @if ($loop->iteration === 3 && $ordenSeguimiento?->horas_estimadas_sla)
                                    <small>Tiempo estimado: {{ $ordenSeguimiento->horas_estimadas_sla }} h</small>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @elseif ($ordenSeguimiento?->horas_estimadas_sla)
                    <p>Tiempo estimado: {{ $ordenSeguimiento->horas_estimadas_sla }} h</p>
                @endif
            </article>
        @empty
            <p>Todavía no has registrado reportes.</p>
        @endforelse
    </div>
    {{ $solicitudes->links() }}
@endsection

@push('scripts')
    <script src="{{ asset('mantresc-live.js') }}" defer></script>
@endpush
