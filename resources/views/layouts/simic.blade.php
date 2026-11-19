<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mantenimiento escolar') · SIMIC</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('simic-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (_) {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="simic">
@auth
    @php
        $user = auth()->user();
        $isCoordinator = $user->isCoordinador();
        $role = $isCoordinator ? 'Coordinador General' : ($user->isAlmacen() ? 'Encargado de Almacén y Suministros' : ($user->isTecnico() ? 'Técnico' : 'Solicitante'));
        $navigation = [];
        if ($isCoordinator) {
            $navigation += ['coordinacion.index' => ['Resumen', '◫'], 'coordinacion.incidencias' => ['Incidencias', '▤'], 'coordinacion.duplicados' => ['Posibles duplicados', '▧'], 'coordinacion.cuadrillas' => ['Cuadrillas', '♧'], 'coordinacion.autorizaciones' => ['Autorizaciones', '▣']];
        }
        if ($user->isTecnico() || $user->isAdmin()) { $navigation += ['cuadrillas.index' => ['Mis órdenes', '▤']]; }
        if ($user->isAlmacen() || $user->isAdmin()) { $navigation += ['almacen.solicitudes' => ['Solicitudes', '▤'], 'almacen.index' => ['Inventario', '▧'], 'almacen.bitacora' => ['Bitácora', '≡']]; }
        if (! $isCoordinator && ! $user->isTecnico() && ! $user->isAlmacen()) { $navigation += ['reportes.create' => ['Nuevo reporte', '⊕'], 'reportes.index' => ['Mis reportes', '▤']]; }
    @endphp
    <div class="workspace">
        <button class="sidebar-shade" data-close-menu aria-label="Cerrar menú" hidden></button>
        <aside class="sidebar {{ $isCoordinator ? 'sidebar-coordination' : '' }}" id="workspace-menu" aria-label="Navegación principal">
            <a class="wordmark" href="{{ route('dashboard') }}">SIMIC</a>
            <p class="sidebar-caption">Recursos materiales<br>y mantenimiento escolar</p>
            <span class="nav-heading">ESPACIO DE TRABAJO</span>
            <nav>
                @foreach ($navigation as $route => [$label, $icon])
                    @php($active = request()->routeIs($route) || ($route === 'cuadrillas.index' && request()->routeIs('cuadrillas.*')) || ($route === 'almacen.index' && request()->routeIs('almacen.entrada*')) || ($route === 'almacen.solicitudes' && request()->routeIs('almacen.entrega')) || ($route === 'reportes.index' && request()->routeIs('reportes.show')))
                    <a href="{{ route($route) }}" class="nav-item {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif><span aria-hidden="true">{{ $icon }}</span>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="sidebar-bottom"><span>Sesión institucional</span><a href="{{ route('help') }}">Ayuda y soporte</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión <span aria-hidden="true">↗</span></button></form></div>
        </aside>
        <div class="workspace-body">
            <header class="topbar">
                <div class="flex items-center gap-3"><button class="mobile-menu" data-open-menu aria-label="Abrir menú" aria-controls="workspace-menu" aria-expanded="false">☰</button><span class="breadcrumb">Campus <span>/</span> @yield('title')</span></div>
                <div class="topbar-actions">@include('partials.simic-theme')<span class="avatar">{{ mb_substr($user->name, 0, 1) }}</span><div class="account"><strong>{{ $user->name }}</strong><span>{{ $role }}</span></div></div>
            </header>
            <main class="main-content" id="contenido">
                <div class="page-heading"><div><h1>@yield('title')</h1><p>Gestión de recursos materiales y mantenimiento escolar</p></div><div class="actions">@yield('actions')</div></div>
                @include('partials.simic-alerts')
                @yield('content')
                <footer class="workspace-footer"><span>SIMIC · Mantenimiento escolar</span><span>Versión 2 · <span data-theme-label>Claro</span></span></footer>
            </main>
        </div>
    </div>
@else
    <header class="public-header"><a class="public-brand" href="{{ route('home') }}"><strong>SIMIC</strong><span>Mantenimiento escolar</span></a>@include('partials.simic-theme')</header>
    <main class="public-main">@include('partials.simic-alerts')@yield('content')</main>
    <footer class="public-footer">SIMIC · Gestión de recursos materiales y mantenimiento escolar · Versión 2</footer>
@endauth
@stack('scripts')
</body>
</html>
