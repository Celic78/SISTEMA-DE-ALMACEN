@extends('layouts.private')

@section('title', 'Trabajo asignado')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">{{ match (request('estado')) {
        'terminadas' => 'Trabajos terminados',
        'proceso' => 'Proceso de trabajo',
        default => 'Órdenes asignadas',
    } }}</h1>
    @include('partials.alerts')
    <div class="grid gap-4">
        @forelse ($ordenes as $orden)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">Orden #{{ $orden->getKey() }} · {{ $orden->solicitud?->ubicacion_libre ?? $orden->solicitud?->espacio?->nombre_espacio }}</h2>
                <p>{{ $orden->solicitud?->descripcion_falla }}</p>
                @if ($orden->solicitud?->evidencia_url)
                    <a class="underline" href="{{ $orden->solicitud->evidencia_url }}">Ver fotografía del reporte</a>
                @endif
                <p>Estado: {{ str_replace('_', ' ', $orden->estado) }}</p>
                @if ($orden->fecha_asignacion)<p>Asignación: {{ $orden->fecha_asignacion->format('d/m/Y H:i') }}</p>@endif
                @if ($orden->fecha_inicio)<p>Inicio: {{ $orden->fecha_inicio->format('d/m/Y H:i') }}</p>@endif
                @if ($orden->fecha_fin)<p>Terminado el <time datetime="{{ $orden->fecha_fin->toIso8601String() }}">{{ $orden->fecha_fin->format('d/m/Y H:i') }}</time></p>@endif
                @foreach ($orden->evidenciasCierre as $evidencia)
                    <a class="underline" href="{{ $evidencia->url_archivo }}">Evidencia de cierre {{ $loop->iteration }}</a>
                @endforeach
                @if (in_array($orden->estado, ['asignada', 'aprobada'], true))
                    <form method="POST" action="{{ route('cuadrillas.estimar') }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <input type="hidden" name="id_orden" value="{{ $orden->getKey() }}">
                        <label>Tiempo estimado en horas <input class="block rounded border p-2" name="horas_estimadas_sla" type="number" min="1" max="168" required></label>
                        <button class="button button-primary">Aceptar sin solicitar material</button>
                    </form>
                @elseif (in_array($orden->estado, ['en_proceso', 'en_espera', 'en_pruebas'], true))
                    <form method="POST" action="{{ route('cuadrillas.actualizar') }}" enctype="multipart/form-data" class="mt-3 grid gap-2">
                        @csrf
                        <input type="hidden" name="id_orden" value="{{ $orden->getKey() }}">
                        <label>Avance
                            <select class="block rounded border p-2" name="estado" required>
                                <option value="en_proceso">En proceso</option>
                                <option value="en_espera">En espera</option>
                                <option value="en_pruebas">En pruebas</option>
                                <option value="resuelta">Solucionado</option>
                            </select>
                        </label>
                        <label>Comentarios <input class="block w-full rounded border p-2" name="comentarios" maxlength="1000"></label>
                        <label>Archivos de cierre <input class="block" type="file" name="evidencias[]" accept="image/*,video/mp4,video/webm" multiple></label>
                        <button class="button button-primary justify-self-start">Guardar avance</button>
                    </form>
                @endif
                @if (in_array($orden->estado, ['asignada', 'aprobada', 'en_proceso', 'en_espera'], true) && $materiales->isNotEmpty())
                    <details class="mt-3">
                        @php($porAceptar = in_array($orden->estado, ['asignada', 'aprobada'], true))
                        <summary class="cursor-pointer font-semibold">{{ $porAceptar ? 'Aceptar y solicitar material' : 'Solicitar material' }}</summary>
                        <form method="POST" action="{{ route('cuadrillas.material', $orden) }}" class="mt-2 flex flex-wrap items-end gap-2">
                            @csrf
                            @if ($porAceptar)
                                <label>Tiempo estimado en horas <input class="block rounded border p-2" name="horas_estimadas_sla" type="number" min="1" max="168" required></label>
                            @endif
                            <label>Material <select class="block rounded border p-2" name="id_material" required>
                                @foreach ($materiales as $material)<option value="{{ $material->getKey() }}">{{ $material->nombre_material }}</option>@endforeach
                            </select></label>
                            <label>Cantidad <input class="block rounded border p-2" name="cantidad" type="number" min=".01" step=".01" required></label>
                            <label>Fecha y hora de recolección <input class="block rounded border p-2" name="recoleccion" type="datetime-local" required></label>
                            <label>Nota <input class="block rounded border p-2" name="nota" maxlength="500"></label>
                            <button class="button button-secondary">{{ $porAceptar ? 'Aceptar y enviar solicitud' : 'Enviar solicitud' }}</button>
                        </form>
                    </details>
                @endif
            </article>
        @empty
            <p>{{ match (request('estado')) {
                'terminadas' => 'Todavía no tienes trabajos terminados.',
                'proceso' => 'Todavía no tienes trabajos en proceso.',
                default => 'Todavía no tienes órdenes asignadas.',
            } }}</p>
        @endforelse
    </div>
    {{ $ordenes->links() }}
@endsection
