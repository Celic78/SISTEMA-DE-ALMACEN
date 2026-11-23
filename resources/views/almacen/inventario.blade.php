@extends('layouts.private')

@section('title', $categoria['etiqueta'] ?? 'Inventario')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">{{ $categoria['etiqueta'] ?? 'Inventario de materiales' }}</h1>
    @include('partials.alerts')
    <details class="mb-4 rounded-xl border border-slate-200 bg-white p-4" @if ($errors->hasAny(['nombre_material', 'id_seccion', 'unidad_medida'])) open @endif>
        <summary class="cursor-pointer font-bold">Agregar material</summary>
        <form method="POST" action="{{ route('almacen.material.store') }}" class="mt-3 flex flex-wrap items-end gap-2">
            @csrf
            <label>Nombre <input name="nombre_material" value="{{ old('nombre_material') }}" maxlength="255" class="block rounded border p-2" required></label>
            @if ($seccionActual)
                <input type="hidden" name="id_seccion" value="{{ $seccionActual->getKey() }}">
            @else
                <label>Categoría
                    <select name="id_seccion" class="block rounded border p-2" required>
                        @foreach ($secciones as $seccion)
                            <option value="{{ $seccion->getKey() }}" @selected(old('id_seccion') == $seccion->getKey())>{{ $seccion->nombre_seccion }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label>Unidad de medida <input name="unidad_medida" value="{{ old('unidad_medida') }}" maxlength="50" class="block rounded border p-2" required></label>
            <button class="button button-primary">Agregar</button>
        </form>
    </details>
    <form method="GET" action="{{ route('almacen.index') }}" class="mb-5 flex flex-wrap items-end gap-2">
        <label>Material <input name="buscar" type="search" value="{{ request('buscar') }}" placeholder="Ejemplo: silla" class="block rounded border p-2"></label>
        @if ($categoria)
            <input type="hidden" name="categoria" value="{{ request('categoria') }}">
        @else
        <label>Categoría
            <select name="id_seccion" class="block rounded border p-2">
                <option value="">Todas</option>
                @foreach ($secciones as $seccion)
                    <option value="{{ $seccion->getKey() }}" @selected((string) request('id_seccion') === (string) $seccion->getKey())>{{ $seccion->nombre_seccion }}</option>
                @endforeach
            </select>
        </label>
        @endif
        <button class="button button-secondary">Buscar</button>
        <a class="button button-secondary" href="{{ route('almacen.index', request()->only('categoria')) }}">Todos</a>
    </form>
    <div class="warehouse-items">
        @forelse ($materiales as $material)
            @php($disponible = (float) ($material->existencia?->cantidad_disponible ?? 0))
            @php($reservado = (float) ($material->existencia?->cantidad_reservada ?? 0))
            @php($sePuedeEliminar = $disponible === 0.0 && $reservado === 0.0 && ! $material->movimientos_exists)
            <article class="warehouse-item rounded-xl border border-slate-200 bg-white">
                <div>
                    <h2 class="font-bold">{{ $material->nombre_material }}</h2>
                    <p>{{ $disponible }} {{ $material->unidad_medida }} @if ($reservado > 0) · {{ $reservado }} reservadas @endif</p>
                </div>
                <div class="warehouse-item-actions">
                    @php($parametros = ['material' => $material] + request()->only('categoria'))
                    <a class="button button-secondary" href="{{ route('almacen.material.operacion', $parametros + ['tipo' => 'entrada']) }}">Agregar</a>
                    <a class="button button-secondary" href="{{ route('almacen.material.operacion', $parametros + ['tipo' => 'salida']) }}">{{ $material->esHerramienta() ? 'Préstamos' : 'Reposición' }}</a>
                </div>
                <details class="warehouse-item-options">
                    <summary>Opciones</summary>
                    <form method="POST" action="{{ route('almacen.material.destroy', $material) }}">
                        @csrf
                        @method('DELETE')
                        <button class="button button-secondary" @disabled(! $sePuedeEliminar)>Eliminar material</button>
                        @unless ($sePuedeEliminar)
                            <small>Conserva existencias, reservas o movimientos registrados.</small>
                        @endunless
                    </form>
                </details>
            </article>
        @empty
            <p>No hay materiales para esta búsqueda.</p>
        @endforelse
    </div>
    {{ $materiales->links() }}
@endsection
