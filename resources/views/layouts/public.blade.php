<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Reportes Escolares y Mantenimiento') · SIRE-M</title>
    <link rel="stylesheet" href="{{ asset('mantresc.css') }}">
    <script src="{{ asset('mantresc-theme.js') }}"></script>
</head>
<body class="site-public @yield('body-class')">
    <header class="site-header">
        <div class="site-container navigation">
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-mark" src="{{ asset('mantresc-logo.png') }}" alt="" width="40" height="40">
                <span>
                    SIRE-M
                    @if (request()->routeIs('home'))
                        <small>Sistema de Reportes Escolares y Mantenimiento</small>
                    @endif
                </span>
            </a>
            @hasSection('access-header')
                @yield('access-header')
            @else
            <nav aria-label="Navegación principal">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Inicio</a>
                <a href="{{ route('servicios') }}" @if (request()->routeIs('servicios')) aria-current="page" @endif>Servicios</a>
                @php($usuarioAutorizado = auth()->check() && app(\App\Services\AuthService::class)->puedeUsarCuenta(auth()->user()))
                @if ($usuarioAutorizado)
                    <a class="button button-primary" href="{{ route('dashboard') }}">Mi panel</a>
                @else
                    <a class="button button-primary" href="{{ route('login') }}">Iniciar sesión</a>
                @endauth
                <button class="theme-button" type="button" data-theme-toggle aria-label="Tema oscuro" aria-pressed="false"><span>Claro</span><span class="theme-track" aria-hidden="true"></span><span>Oscuro</span></button>
            </nav>
            @endif
        </div>
    </header>
    <main class="site-container">
        @yield('content')
    </main>
    <footer class="site-container site-footer">SIRE-M · Sistema de Reportes Escolares y Mantenimiento</footer>
    @stack('scripts')
</body>
</html>
