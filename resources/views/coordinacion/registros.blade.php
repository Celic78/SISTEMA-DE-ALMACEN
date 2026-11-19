@extends('layouts.private')

@section('title', 'Solicitudes de acceso')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Solicitudes de acceso</h1>
    @include('partials.alerts')
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
        <h2 class="font-bold">Publicar aviso de mantenimiento</h2>
        <form method="POST" action="{{ route('coordinacion.avisos') }}" class="mt-3 flex flex-wrap items-end gap-3">
            @csrf
            <label class="grid gap-1">Título <input class="rounded border p-2" name="titulo" maxlength="160" value="{{ old('titulo') }}" required></label>
            <label class="grid gap-1">Fecha y hora <input class="rounded border p-2" name="fecha" type="datetime-local" value="{{ old('fecha') }}" required></label>
            <label class="grid gap-1">Ubicación del trabajo <input class="rounded border p-2" name="ubicacion" maxlength="255"></label>
            <label class="grid gap-1">Categoría del trabajo
                <select class="rounded border p-2" name="id_categoria">
                    <option value="">Solo publicar aviso</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->getKey() }}">{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1">Repetir cada (días) <input class="rounded border p-2" name="intervalo_dias" type="number" min="1" max="365"></label>
            <button class="button button-primary">Guardar programación</button>
        </form>
    </section>
    <div class="grid gap-4">
        @forelse ($usuarios as $usuario)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">{{ $usuario->name }}</h2>
                <p>{{ $usuario->email }} · {{ $usuario->email_tipo }}</p>
                <form method="POST" action="{{ route('coordinacion.registros.decidir', $usuario) }}" class="mt-3 flex gap-3">
                    @csrf
                    <button class="rounded-lg bg-emerald-800 px-4 py-2 text-white" name="decision" value="aprobado">Aceptar</button>
                    <button class="rounded-lg border border-slate-400 px-4 py-2" name="decision" value="rechazado">Rechazar</button>
                </form>
            </article>
        @empty
            <p>No hay solicitudes pendientes.</p>
        @endforelse
    </div>
    {{ $usuarios->links() }}
@endsection
