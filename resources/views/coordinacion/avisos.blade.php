@extends('layouts.private')

@section('title', 'Avisos de mantenimiento')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Avisos de mantenimiento</h1>
    @include('partials.alerts')
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="font-bold">Publicar aviso de mantenimiento</h2>
        <p class="mt-1 text-sm text-slate-600">Publica avisos sobre trabajos en los inmuebles del plantel.</p>
        <form method="POST" action="{{ route('coordinacion.avisos') }}" class="mt-3 flex flex-wrap items-end gap-3">
            @csrf
            <label class="grid gap-1">Título <input class="rounded border p-2" name="titulo" maxlength="160" value="{{ old('titulo') }}" required></label>
            <label class="grid gap-1">Fecha y hora <input class="rounded border p-2" name="fecha" type="datetime-local" value="{{ old('fecha') }}" required></label>
            <label class="grid gap-1">Ubicación del trabajo <input class="rounded border p-2" name="ubicacion" maxlength="255"></label>
            <label class="grid gap-1">Categoría del trabajo
                <select class="rounded border p-2" name="id_categoria">
                    <option value="">Solo publicar aviso</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->getKey() }}" @selected((string) old('id_categoria') === (string) $categoria->getKey())>{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">Repetir cada (días) <input class="rounded border p-2" name="intervalo_dias" type="number" min="1" max="365"></label>
            <button class="button button-primary">Guardar programación</button>
        </form>
    </section>
@endsection
