@extends('layouts.private')

@section('title', 'Edificios y espacios')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Edificios y espacios</h1>
    @include('partials.alerts')
    <div class="grid gap-4">
        <section class="rounded-xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 font-bold">Agregar edificio</h2>
            <form class="flex flex-wrap items-end gap-3" method="POST" action="{{ route('coordinacion.ubicaciones.edificios') }}">
                @csrf
                <label class="grid gap-1">Nombre del edificio
                    <input class="rounded border p-2" name="nombre" value="{{ old('nombre') }}" maxlength="100" placeholder="Ej. Edificio 6" required>
                </label>
                <button class="button button-primary">Agregar edificio</button>
            </form>
        </section>
        @if ($edificios->isNotEmpty())
            <section class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 font-bold">Agregar aula, laboratorio u otro espacio</h2>
                <form class="flex flex-wrap items-end gap-3" method="POST" action="{{ route('coordinacion.ubicaciones.espacios') }}">
                    @csrf
                    <label class="grid gap-1">Edificio
                        <select class="rounded border p-2" name="id_edificio" required>
                            <option value="">Selecciona un edificio</option>
                            @foreach ($edificios as $edificio)
                                <option value="{{ $edificio->getKey() }}" @selected((string) old('id_edificio') === (string) $edificio->getKey())>{{ $edificio->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1">Tipo de espacio
                        <select class="rounded border p-2" name="tipo_espacio" required>
                            <option value="">Selecciona un tipo</option>
                            @foreach (['Aula', 'Laboratorio', 'Baño', 'Oficina', 'Otro'] as $tipo)
                                <option @selected(old('tipo_espacio') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1">Nombre o número
                        <input class="rounded border p-2" name="nombre_espacio" value="{{ old('nombre_espacio') }}" maxlength="100" placeholder="Ej. Aula 21" required>
                    </label>
                    <label class="grid gap-1">Piso (opcional)
                        <input class="rounded border p-2" name="piso" value="{{ old('piso') }}" maxlength="100">
                    </label>
                    <button class="button button-primary">Agregar espacio</button>
                </form>
            </section>
        @endif
        @foreach ($edificios as $edificio)
            <details class="rounded-xl border border-slate-200 bg-white p-4">
                <summary class="cursor-pointer font-bold">{{ $edificio->nombre }} · {{ $edificio->espacios->count() }} espacios</summary>
                <ul class="mt-3 list-disc pl-5">
                    @forelse ($edificio->espacios->sortBy('nombre_espacio', SORT_NATURAL | SORT_FLAG_CASE) as $espacio)
                        <li class="mb-2">
                            {{ $espacio->nombre_espacio }} · {{ $espacio->tipo_espacio }}
                            <a class="underline" href="{{ route('coordinacion.bienes.index', $espacio) }}">Ver inventario ({{ $espacio->bienes_count }})</a>
                        </li>
                    @empty
                        <li>Agrega un espacio para habilitar reportes en este edificio.</li>
                    @endforelse
                </ul>
            </details>
        @endforeach
    </div>
@endsection