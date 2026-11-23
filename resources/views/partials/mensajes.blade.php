@extends('layouts.private')

@section('title', 'Mensajes')

@section('content')
    @include('partials.alerts')

    <section class="messages-inbox" data-messages-inbox aria-labelledby="messages-title">
        <aside class="messages-contacts">
            <h1 id="messages-title">Mensajes</h1>
            <nav aria-label="Conversaciones" data-messages-refresh="contacts">
                @php($roles = ['coordinador' => 'Coordinación', 'tecnico' => 'Técnico', 'almacen' => 'Almacén'])
                @forelse ($contactos as $persona)
                    @php($sinLeer = (int) ($pendientes[$persona->id] ?? 0))
                    <a href="{{ route('mensajes.index', ['contacto' => $persona->id]) }}" @if ($contacto?->id === $persona->id) aria-current="page" @endif>
                        <span><strong>{{ $persona->name }}</strong><small>{{ $roles[$persona->email_tipo] }}</small></span>
                        @if ($sinLeer)
                            <span class="messages-count" aria-label="{{ $sinLeer }} sin leer" data-contact-unread="{{ $persona->id }}">{{ $sinLeer }}</span>
                        @endif
                    </a>
                @empty
                    <p>No hay contactos disponibles.</p>
                @endforelse
            </nav>
        </aside>

        @if ($contacto)
            <section class="messages-conversation" aria-labelledby="messages-contact-title">
                <header><h2 id="messages-contact-title">{{ $contacto->name }}</h2><small>{{ $roles[$contacto->email_tipo] }}</small></header>
                <div class="messages-history" data-messages-refresh="history" data-messages-scroll @if ($mensajes->onFirstPage()) data-messages-latest @endif>
                    @if ($mensajes->previousPageUrl() || $mensajes->nextPageUrl())
                        <nav class="messages-history-pages" aria-label="Historial">
                            @if ($mensajes->nextPageUrl())
                                <a href="{{ $mensajes->nextPageUrl() }}">Mensajes anteriores</a>
                            @endif
                            @if ($mensajes->previousPageUrl())
                                <a href="{{ $mensajes->previousPageUrl() }}">Mensajes más recientes</a>
                            @endif
                        </nav>
                    @endif
                    @forelse ($mensajes->getCollection()->reverse() as $mensaje)
                        @php($propio = (int) $mensaje->remitente === (int) auth()->id())
                        <article class="message-bubble {{ $propio ? 'message-outgoing' : '' }}">
                            <span class="sr-only">{{ $propio ? 'Tú' : $contacto->name }}</span>
                            <p>{{ $mensaje->mensaje }}</p>
                            <time datetime="{{ \Illuminate\Support\Carbon::parse($mensaje->enviado)->toIso8601String() }}">{{ \Illuminate\Support\Carbon::parse($mensaje->enviado)->format('d/m H:i') }}</time>
                        </article>
                    @empty
                        <p class="messages-empty">Esta conversación todavía no tiene mensajes.</p>
                    @endforelse
                </div>

                <form class="messages-compose" method="POST" action="{{ route('mensajes.store', $contacto) }}">
                    @csrf
                    <label class="sr-only" for="mensaje">Mensaje para {{ $contacto->name }}</label>
                    <textarea id="mensaje" name="mensaje" rows="2" maxlength="2000" placeholder="Escribe un mensaje" required>{{ old('mensaje') }}</textarea>
                    <button class="button button-primary" type="submit">Enviar</button>
                </form>
            </section>
        @else
            <p class="messages-empty messages-select">Selecciona un contacto para abrir la conversación.</p>
        @endif
    </section>
@endsection
