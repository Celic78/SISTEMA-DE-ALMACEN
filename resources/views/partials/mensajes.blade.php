@extends('layouts.private')

@section('title', 'Mensajes')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Mensajes</h1>
    @include('partials.alerts')
    <form method="GET" action="{{ route('mensajes.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <label>Contacto
            <select name="contacto" class="block rounded border p-2" required>
                <option value="">Elige a quién escribir</option>
                @foreach ($contactos as $persona)
                    <option value="{{ $persona->id }}" @selected($contacto?->id === $persona->id)>{{ $persona->name }} · {{ ucfirst($persona->email_tipo) }}</option>
                @endforeach
            </select>
        </label>
        <button class="button button-secondary">Abrir conversación</button>
    </form>
    @if ($contacto)
        <form method="POST" action="{{ route('mensajes.store', $contacto) }}" class="mb-4 grid max-w-xl gap-2">
            @csrf
            <label>Mensaje para {{ $contacto->name }} <textarea class="block w-full rounded border p-2" name="mensaje" rows="3" maxlength="2000" required>{{ old('mensaje') }}</textarea></label>
            <button class="button button-primary justify-self-start">Enviar mensaje</button>
        </form>
        <div class="grid gap-2" data-live-panel>
            @forelse ($mensajes as $mensaje)
                <article class="rounded-xl border border-slate-200 bg-white p-4">
                    <h2 class="font-bold">{{ (int) $mensaje->remitente === (int) auth()->id() ? 'Tú' : $contacto->name }}</h2>
                    <p class="whitespace-pre-wrap">{{ $mensaje->mensaje }}</p>
                    <time>{{ $mensaje->enviado }}</time>
                </article>
            @empty
                <p>Esta conversación todavía no tiene mensajes.</p>
            @endforelse
        </div>
        {{ $mensajes->links() }}
    @endif
@endsection
@push('scripts')
    <script src="{{ asset('mantresc-live.js') }}" defer></script>
@endpush