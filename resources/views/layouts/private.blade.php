<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · SIRE-M</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('mantresc.css') }}">
    <script src="{{ asset('mantresc-theme.js') }}"></script>
    @stack('styles')
</head>
<body class="workspace-body">
    @php
        $persona = auth()->user();
        $enlaces = match ($persona->email_tipo) {
            'coordinador' => ['coordinacion.index' => 'Incidencias', 'coordinacion.cuadrillas' => 'Cuadrillas', 'coordinacion.registros' => 'Acceso y programación', 'reportes.create' => 'Nuevo reporte', 'reportes.index' => 'Mis reportes', 'mensajes.index' => 'Mensajes'],
            'tecnico' => ['cuadrillas.index' => 'Órdenes asignadas', 'mensajes.index' => 'Mensajes'],
            'almacen' => ['almacen.index' => 'Inventario', 'almacen.requisiciones' => 'Requisiciones', 'mensajes.index' => 'Mensajes'],
            default => ['reportes.create' => 'Nuevo reporte', 'reportes.index' => 'Mis reportes', 'reportes.estado' => 'Estado de reporte'],
        };
    @endphp
    <div class="workspace">
        <aside class="workspace-sidebar" aria-label="Panel de usuario">
            <a class="brand" href="{{ route('home') }}"><img class="brand-mark" src="{{ asset('mantresc-logo.png') }}" alt="" width="40" height="40"> SIRE-M</a>
            <div class="workspace-profile">
                <strong>{{ $persona->name }}</strong>
                <span>{{ $persona->email }}</span>
            </div>
            <nav aria-label="Módulos">
                @foreach ($enlaces as $nombreRuta => $etiqueta)
                    @php
                        $filtrosPropios = match ($nombreRuta) {
                            'coordinacion.index' => ['duplicados', 'autorizaciones'],
                            'cuadrillas.index' => ['proceso', 'terminadas'],
                            default => [],
                        };
                    @endphp
                    <a href="{{ route($nombreRuta) }}" @if (request()->routeIs($nombreRuta) && ! in_array(request('estado'), $filtrosPropios, true)) aria-current="page" @endif>{{ $etiqueta }}</a>
                    @if ($nombreRuta === 'coordinacion.index')
                        @foreach (['duplicados' => 'Posibles duplicados', 'autorizaciones' => 'Autorizaciones'] as $estado => $seccion)
                            <a href="{{ route($nombreRuta, ['estado' => $estado]) }}" @if (request()->routeIs($nombreRuta) && request('estado') === $estado) aria-current="page" @endif>{{ $seccion }}</a>
                        @endforeach
                    @endif
                @endforeach
                @if ($persona->email_tipo === 'tecnico')
                    <a href="{{ route('cuadrillas.index', ['estado' => 'proceso']) }}" @if (request()->routeIs('cuadrillas.index') && request('estado') === 'proceso') aria-current="page" @endif>Proceso de trabajo</a>
                    <a href="{{ route('cuadrillas.index', ['estado' => 'terminadas']) }}" @if (request()->routeIs('cuadrillas.index') && request('estado') === 'terminadas') aria-current="page" @endif>Trabajos terminados</a>
                @endif
            </nav>
            <div class="workspace-controls">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="button button-secondary">Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <main class="workspace-content">
            <header class="workspace-topbar">
                <span class="workspace-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($persona->name, 0, 1)) }}</span>
                <strong>{{ $persona->name }}</strong>
                <button type="button" class="theme-button" data-theme-toggle aria-label="Tema oscuro" aria-pressed="false">
                    <span>Claro</span>
                    <span class="theme-track" aria-hidden="true"></span>
                    <span>Oscuro</span>
                </button>
            </header>
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
