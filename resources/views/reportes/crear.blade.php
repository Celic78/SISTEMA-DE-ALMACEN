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
                <label>Categoría
                    <select name="id_categoria" required>
                        <option value="">Selecciona una categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->getKey() }}" @selected((string) old('id_categoria') === (string) $categoria->getKey())>{{ $categoria->nombre_categoria }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Fecha y hora del hallazgo
                    <input type="datetime-local" name="fecha_hallazgo" value="{{ old('fecha_hallazgo') }}" required>
                </label>
                <label>Describe la falla
                    <textarea name="descripcion_falla" rows="4" minlength="10" maxlength="2000" class="rounded-lg border border-slate-300 p-2" required>{{ old('descripcion_falla') }}</textarea>
                </label>
            </div>
            <div data-step>
                <h2>3. Evidencia</h2>
                <label>Fotografía del fallo
                    <input type="file" name="evidencia" accept="image/jpeg,image/png,image/webp" required>
                </label>
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
