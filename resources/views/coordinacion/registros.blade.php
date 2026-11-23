@extends('layouts.private')

@section('title', 'Perfiles')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Perfiles</h1>
    @include('partials.alerts')
    <section class="mb-6" aria-labelledby="solicitudes-acceso">
        <h2 id="solicitudes-acceso" class="mb-3 text-lg font-bold">Solicitudes de acceso</h2>
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
    </section>
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4" aria-labelledby="alta-personal">
        <h2 id="alta-personal" class="font-bold">Crear cuenta de personal</h2>
        <p class="mt-1 text-sm text-slate-600">Entrega la contraseña temporal a su titular. El sistema le pedirá cambiarla al entrar por primera vez.</p>
        <form method="POST" action="{{ route('coordinacion.personal.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2">
            @csrf
            <label class="grid gap-1">Nombre completo <input class="rounded border p-2" name="name" maxlength="255" value="{{ old('name') }}" required></label>
            <label class="grid gap-1">Rol institucional
                <select class="rounded border p-2" name="rol" required>
                    <option value="tecnico" @selected(old('rol') === 'tecnico')>Técnico</option>
                    <option value="profesor" @selected(old('rol') === 'profesor')>Profesor</option>
                    <option value="almacen" @selected(old('rol') === 'almacen')>Almacén</option>
                </select>
            </label>
            <label class="grid gap-1">Correo institucional <input class="rounded border p-2" name="email" type="email" maxlength="254" value="{{ old('email') }}" required></label>
            <label class="grid gap-1">Contraseña temporal <input class="rounded border p-2" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required></label>
            <div><button class="button button-primary">Crear cuenta</button></div>
        </form>
    </section>
@endsection
