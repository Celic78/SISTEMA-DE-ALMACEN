@extends('layouts.private')

@section('title', 'Inventario')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Inventario de materiales</h1>
    @include('partials.alerts')
    <details class="mb-4 rounded-xl border border-slate-200 bg-white p-4">
        <summary class="cursor-pointer font-bold">Agregar material</summary>
        <form method="POST" action="{{ route('almacen.material.store') }}" class="mt-3 flex flex-wrap items-end gap-2">
            @csrf
            <label>Nombre <input name="nombre_material" maxlength="255" class="block rounded border p-2" required></label>
            <label>Categoría
                <select name="id_seccion" class="block rounded border p-2" required>
                    @foreach ($secciones as $seccion)
                        <option value="{{ $seccion->getKey() }}">{{ $seccion->nombre_seccion }}</option>
                    @endforeach
                </select>
            </label>
            <label>Unidad de medida <input name="unidad_medida" maxlength="50" class="block rounded border p-2" required></label>
            <button class="button button-primary">Agregar</button>
        </form>
    </details>
    <form method="GET" action="{{ route('almacen.index') }}" class="mb-5 flex flex-wrap items-end gap-2">
        <label>Material <input name="buscar" value="{{ request('buscar') }}" class="block rounded border p-2"></label>
        <label>Categoría
            <select name="id_seccion" class="block rounded border p-2">
                <option value="">Todas</option>
                @foreach ($secciones as $seccion)
                    <option value="{{ $seccion->getKey() }}" @selected((string) request('id_seccion') === (string) $seccion->getKey())>{{ $seccion->nombre_seccion }}</option>
                @endforeach
            </select>
        </label>
        <button class="button button-secondary">Buscar</button>
    </form>
    <div class="grid gap-4">
        @forelse ($materiales as $material)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">{{ $material->nombre_material }}</h2>
                <p>{{ $material->seccion?->nombre_seccion }} · Existencias: {{ $material->existencia?->cantidad_disponible ?? 0 }}</p>
                <form method="POST" action="{{ route('almacen.entrada', $material) }}" class="mt-3 flex flex-wrap items-end gap-2">
                    @csrf
                    <label>Cantidad <input name="cantidad" type="number" min=".01" max="999999" step=".01" class="block rounded border p-2" required></label>
                    <label>Motivo <input name="motivo" maxlength="250" class="block rounded border p-2" required></label>
                    <button class="button button-primary">Registrar entrada</button>
                </form>
                @if ((float) ($material->existencia?->cantidad_disponible ?? 0) === 0.0)
                    <form method="POST" action="{{ route('almacen.material.destroy', $material) }}" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button class="button button-secondary">Eliminar material</button>
                    </form>
                @endif
            </article>
        @empty
            <p>No hay materiales para esta búsqueda.</p>
        @endforelse
    </div>
    {{ $materiales->links() }}
@endsection
