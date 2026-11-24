@extends('layouts.private')

@section('title', 'Editar ficha del bien')

@section('content')
    <a class="underline" href="{{ route('coordinacion.bienes.index', $espacio) }}">Volver al inventario del espacio</a>
    <h1 class="mt-2 text-2xl font-bold">Editar {{ $bien->nombre }}</h1>
    <p class="mb-4 text-slate-600">{{ $espacio->edificio->nombre }} · {{ $espacio->nombre_espacio }}</p>
    @include('partials.alerts')

    <form class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4" method="POST" action="{{ route('coordinacion.bienes.update', [$espacio, $bien]) }}">
        @csrf
        @method('PATCH')
        @include('coordinacion.bienes.campos')
        <div><button class="button button-primary">Guardar cambios</button></div>
    </form>
@endsection