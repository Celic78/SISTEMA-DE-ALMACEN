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
                <label>¿Dónde ocurrió la falla?
                    <input name="ubicacion_libre" value="{{ old('ubicacion_libre') }}" maxlength="255" placeholder="Ej. Edificio A, aula 204" required>
                </label>
            </div>
            <div data-step>
                <h2>2. Descripción</h2>
                <label>Fecha y hora del hallazgo
                    @php($hoy = $momentoDelReporte->toDateString())
                    @php($fechaAnterior = old('fecha_hallazgo'))
                    <input type="datetime-local" name="fecha_hallazgo"
                        min="{{ $hoy }}T00:00" max="{{ $hoy }}T23:59"
                        value="{{ is_string($fechaAnterior) && str_starts_with($fechaAnterior, $hoy.'T') ? $fechaAnterior : $momentoDelReporte->format('Y-m-d\TH:i') }}" required>
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
    <script src="{{ asset('mantresc-reporte.js') }}"></script>
@endpush
