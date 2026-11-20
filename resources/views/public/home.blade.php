@extends('layouts.public')

@section('body-class', 'site-home')

@section('content')
    <section class="hero" aria-labelledby="inicio-titulo">
        <div class="hero-copy">
            <h1 id="inicio-titulo">Mantenimiento escolar con seguimiento</h1>
            <p>Plataforma institucional para el reporte de fallas, mantenimiento preventivo y control de materiales en aulas, mobiliario e instalaciones.</p>
            @auth
                <a class="button button-primary" href="{{ route('dashboard') }}">Ir a mi panel</a>
            @else
                <a class="button button-primary" href="{{ route('login') }}">Iniciar sesión</a>
            @endauth
        </div>
        <aside class="hero-guide" aria-labelledby="hero-guide-title">
            <h2 id="hero-guide-title">Del reporte a la solución</h2>
            <ol>
                <li>Registra la ubicación y la falla.</li>
                <li>Adjunta evidencia fotográfica.</li>
                <li>Consulta el estado de tu reporte.</li>
            </ol>
        </aside>
    </section>

    <section class="section notices" id="avisos" aria-labelledby="avisos-titulo">
        <h2 id="avisos-titulo">Avisos de mantenimiento</h2>
        @forelse ($avisos ?? [] as $aviso)
            <article><h3>{{ $aviso->titulo }}</h3><p>{{ \Illuminate\Support\Carbon::parse($aviso->fecha)->format('d/m/Y H:i') }}</p></article>
        @empty
            <p>Por el momento no hay avisos publicados.</p>
        @endforelse
    </section>
@endsection
