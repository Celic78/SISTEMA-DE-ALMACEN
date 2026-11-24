@extends('layouts.private')

@section('title', 'Inventario del espacio')

@section('content')
    <a class="underline" href="{{ route('coordinacion.ubicaciones') }}">Volver a Edificios y espacios</a>
    <h1 class="mt-2 text-2xl font-bold">{{ $espacio->nombre_espacio }}</h1>
    <p class="mb-4 text-slate-600">{{ $espacio->edificio->nombre }} · Inventario del espacio</p>
    @include('partials.alerts')

    <details class="my-4 rounded-xl border border-slate-200 bg-white p-4" @if ($errors->any()) open @endif>
        <summary class="cursor-pointer font-bold">Registrar un bien</summary>
        <form class="mt-4 grid gap-3" method="POST" action="{{ route('coordinacion.bienes.store', $espacio) }}">
            @csrf
            @include('coordinacion.bienes.campos')
            <div><button class="button button-primary">Guardar bien</button></div>
        </form>
    </details>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($bienes as $registro)
            <article class="flex min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">{{ $registro->nombre }}</h2>
                <p class="mb-2 text-sm text-slate-600">{{ $registro->codigo_inventario }} · {{ \App\Models\BienEspacio::ESTADOS[$registro->estado] }}</p>
                <dl class="mb-4 grid gap-2">
                    <div><dt class="font-semibold">Tipo</dt><dd>{{ $registro->tipo }}</dd></div>
                    <div><dt class="font-semibold">Ubicación exacta</dt><dd>{{ $registro->ubicacion_interna }}</dd></div>
                    @if ($registro->marca)<div><dt class="font-semibold">Marca</dt><dd>{{ $registro->marca }}</dd></div>@endif
                    @if ($registro->modelo)<div><dt class="font-semibold">Modelo</dt><dd>{{ $registro->modelo }}</dd></div>@endif
                    @if ($registro->numero_serie)<div><dt class="font-semibold">Número de serie</dt><dd>{{ $registro->numero_serie }}</dd></div>@endif
                    @if ($registro->observaciones)<div><dt class="font-semibold">Observaciones</dt><dd class="whitespace-pre-line">{{ $registro->observaciones }}</dd></div>@endif
                </dl>
                <a class="button button-secondary mt-auto self-start" href="{{ route('coordinacion.bienes.edit', [$espacio, $registro]) }}">Editar ficha</a>
            </article>
        @empty
            <p>Aún no hay bienes registrados en este espacio.</p>
        @endforelse
    </div>
    {{ $bienes->links() }}
@endsection