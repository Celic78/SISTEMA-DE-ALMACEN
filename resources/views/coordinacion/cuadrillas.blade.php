@extends('layouts.private')

@section('title', 'Cuadrillas')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Cuadrillas</h1>
    @include('partials.alerts')
    <div class="grid gap-4">
        @forelse ($tecnicos as $tecnico)
            @php($perfil = $tecnico->roles->first()->pivot)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">{{ $tecnico->name }}</h2>
                <p>{{ $tecnico->email }}</p>
                <form method="POST" action="{{ route('coordinacion.cuadrillas.actualizar', $tecnico) }}" class="mt-3 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <label>Turno
                        <select class="block rounded border p-2" name="turno_tecnico" required>
                            <option value="">Selecciona un turno</option>
                            <option value="matutino" @selected($perfil->turno_tecnico === 'matutino')>Matutino · 07:00–13:00</option>
                            <option value="vespertino" @selected($perfil->turno_tecnico === 'vespertino')>Vespertino · 13:00–19:00</option>
                        </select>
                    </label>
                    <button class="button button-primary" type="submit">Guardar turno</button>
                </form>
            </article>
        @empty
            <p>No hay técnicos aprobados.</p>
        @endforelse
    </div>
    {{ $tecnicos->links() }}
@endsection
