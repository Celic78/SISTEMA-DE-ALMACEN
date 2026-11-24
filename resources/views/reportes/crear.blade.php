@extends('layouts.private')

@section('title', 'Nuevo reporte')

@section('content')
    <section class="access-card">
        <h1>Nuevo reporte</h1>
        @include('partials.alerts')
        <form method="POST" action="{{ route('reportes.store') }}" enctype="multipart/form-data" data-report-wizard>
            @csrf
            <div data-step>
                <h2>1. Ubicación</h2>
                <label>Edificio
                    <select name="id_edificio" data-report-building required>
                        <option value="">Selecciona un edificio</option>
                        @foreach ($edificios as $edificio)
                            <option value="{{ $edificio->getKey() }}" @selected((string) old('id_edificio') === (string) $edificio->getKey())>{{ $edificio->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label data-report-space-kind-wrapper hidden>Tipo de espacio
                    <select data-report-space-kind></select>
                </label>
                <label><span data-report-space-label>Espacio</span>
                    <select name="id_espacio" data-report-space data-selected="{{ is_scalar(old('id_espacio')) ? old('id_espacio') : '' }}" required>
                        <option value="">Primero selecciona un edificio</option>
                    </select>
                </label>
                <template data-report-space-catalog>
                    <select>
                        @foreach ($edificios as $edificio)
                            @foreach ($edificio->espacios->sortBy('nombre_espacio', SORT_NATURAL | SORT_FLAG_CASE) as $espacio)
                                <option value="{{ $espacio->getKey() }}" data-building="{{ $edificio->getKey() }}" data-kind="{{ $espacio->tipo_espacio }}" data-space-name="{{ $espacio->nombre_espacio }}">{{ $edificio->nombre }} · {{ $espacio->nombre_espacio }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </template>
                <div data-report-assets data-url="{{ route('reportes.bienes', ['espacio' => '__espacio__']) }}">
                    <label>Bien afectado
                        <select name="bien" data-report-asset-name data-selected="{{ is_string(old('bien')) ? old('bien') : '' }}" required>
                            <option value="">Primero selecciona un espacio</option>
                        </select>
                    </label>
                    <label data-report-asset-unit-wrapper hidden>Número o código del bien
                        <select name="id_bien_espacio" data-report-asset-unit data-selected="{{ is_scalar(old('id_bien_espacio')) ? old('id_bien_espacio') : '' }}" disabled></select>
                    </label>
                    <div data-report-asset-other hidden>
                        <label>Nombre del bien o elemento
                            <input name="nombre_bien" value="{{ old('nombre_bien') }}" maxlength="120" disabled>
                        </label>
                        <label>Número, si está marcado (opcional)
                            <input name="numero_bien" value="{{ old('numero_bien') }}" maxlength="60" disabled>
                        </label>
                    </div>
                    <p class="registration-notice" data-report-asset-status role="status" hidden></p>
                </div>
                <noscript>Activa JavaScript para seleccionar el espacio del reporte.</noscript>
            </div>
            <div data-step>
                <h2>2. Descripción</h2>
                @php
                    $minima = $fechaMinima->toDateString();
                    $hoy = $momentoDelReporte->toDateString();
                    $seleccionada = old('fecha_hallazgo', $hoy);
                    if (! is_string($seleccionada) || $seleccionada < $minima || $seleccionada > $hoy) {
                        $seleccionada = $hoy;
                    }
                    $meses = collect([$fechaMinima, $momentoDelReporte])->unique(fn ($fecha) => $fecha->format('Y-m'));
                @endphp
                <details class="report-calendar" data-report-calendar>
                    <summary>Fecha del hallazgo · <span data-report-calendar-date>{{ \Illuminate\Support\Carbon::parse($seleccionada)->format('d/m/Y') }}</span></summary>
                    @foreach ($meses as $mes)
                        @php($primerDia = $mes->copy()->startOfMonth()->dayOfWeekIso)
                        <table>
                            <caption>{{ $mes->copy()->locale('es')->translatedFormat('F Y') }}</caption>
                            <thead>
                                <tr>
                                    @foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $diaSemana)
                                        <th scope="col">{{ $diaSemana }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_chunk(range(2 - $primerDia, 43 - $primerDia), 7) as $semana)
                                    @if ($semana[0] <= $mes->daysInMonth)
                                        <tr>
                                            @foreach ($semana as $dia)
                                                <td>
                                                    @if ($dia > 0 && $dia <= $mes->daysInMonth)
                                                        @php($fecha = $mes->copy()->day($dia))
                                                        @php($valor = $fecha->toDateString())
                                                        @if ($valor >= $minima && $valor <= $hoy)
                                                            <label class="report-calendar-option">
                                                                <input type="radio" name="fecha_hallazgo" value="{{ $valor }}" data-date-label="{{ $fecha->format('d/m/Y') }}" aria-label="{{ $fecha->format('d/m/Y') }}" @checked($seleccionada === $valor) required>
                                                                <span @if ($valor === $hoy) aria-current="date" @endif>{{ $dia }}</span>
                                                            </label>
                                                        @else
                                                            <span class="report-calendar-disabled" aria-disabled="true">{{ $dia }}</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                    <p>Puedes elegir hoy o cualquiera de los siete días anteriores.</p>
                </details>
                <label>Hora del hallazgo (07:00 a 19:00, centro de México)
                    <input type="time" name="hora_hallazgo" min="{{ $horaMinima }}" max="{{ $horaMaxima }}" value="{{ old('hora_hallazgo', $horaPredeterminada) }}" required>
                </label>
                <label>Describe la falla del inmueble
                    <textarea name="descripcion_falla" rows="4" placeholder="Ej. La puerta del aula no cierra correctamente." minlength="10" maxlength="2000" class="rounded-lg border border-slate-300 p-2" required>{{ old('descripcion_falla') }}</textarea>
                </label>
            </div>
            <div data-step>
                <h2>3. Evidencia</h2>
                @include('partials.adjuntar-evidencia', [
                    'etiqueta' => 'Fotografía del fallo',
                    'campo' => 'evidencia',
                    'formatos' => 'image/jpeg,image/png,image/webp',
                ])
            </div>
            <div class="actions">
                <button type="button" class="button button-secondary" data-previous hidden>Atrás</button>
                <button type="button" class="button button-primary" data-next hidden>Siguiente</button>
                <button type="submit" class="button button-primary">Enviar reporte</button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('mantresc-reporte.js').'?v=4' }}"></script>
@endpush
