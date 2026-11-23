@extends('layouts.private')

@section('title', $titulo)

@section('content')
    <h1 class="mb-4 text-2xl font-bold">{{ $material->seccion?->nombre_seccion ?? 'Inventario' }}</h1>
    <section class="warehouse-operation rounded-xl border border-slate-200 bg-white" aria-labelledby="warehouse-operation-title">
        <a class="button button-secondary" href="{{ route('almacen.index', $volver) }}">← Volver</a>
        <h2 id="warehouse-operation-title" class="text-xl font-bold">{{ $titulo }}</h2>
        <p>{{ $material->nombre_material }} · {{ $material->unidad_medida }}</p>
        @include('partials.alerts')
        <form method="POST" action="{{ route($tipo === 'entrada' ? 'almacen.entrada' : 'almacen.material.salida', $material) }}">
            @csrf
            <label>Motivo
                <input name="motivo" value="{{ old('motivo') }}" maxlength="250" required>
            </label>
            @if ($prestamo)
                <label>¿A quién se prestó?
                    <input name="prestado_a" value="{{ old('prestado_a') }}" maxlength="150" required>
                </label>
            @endif
            <label>Cantidad
                <input name="cantidad" value="{{ old('cantidad') }}" type="number" min=".01" max="999999" step=".01" required>
            </label>
            <button class="button button-primary" type="submit">Aceptar</button>
        </form>
    </section>
@endsection
