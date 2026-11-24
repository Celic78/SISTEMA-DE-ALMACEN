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
        $rolPerfil = match ($persona->email_tipo) {
            'alumno' => 'Alumno',
            'profesor' => 'Profesor',
            'coordinador' => 'Coordinador',
            'tecnico' => 'Técnico',
            'almacen' => 'Almacén',
            default => $persona->isAdmin() ? 'Administrador' : 'Solicitante',
        };
        $enlaces = match ($persona->email_tipo) {
            'coordinador' => ['coordinacion.index' => 'Incidencias', 'coordinacion.cuadrillas' => 'Cuadrillas', 'coordinacion.registros' => 'Perfiles', 'coordinacion.ubicaciones' => 'Edificios y espacios', 'coordinacion.avisos.index' => 'Avisos de mantenimiento', 'reportes.create' => 'Nuevo reporte', 'reportes.index' => 'Mis reportes'],
            'tecnico' => ['cuadrillas.index' => 'Órdenes asignadas'],
            'almacen' => [],
            default => ['reportes.create' => 'Nuevo reporte', 'reportes.index' => 'Mis reportes', 'reportes.estado' => 'Estado de reporte'],
        };
    @endphp
    <div class="workspace">
        <aside class="workspace-sidebar" aria-label="Panel de usuario">
            <div class="workspace-sidebar-header">
                <a class="brand" href="{{ route('home') }}"><img class="brand-mark" src="{{ asset('mantresc-logo.png') }}" alt="" width="72" height="72"> SIRE-M</a>
                <button class="button workspace-menu-toggle" type="button" aria-controls="workspace-menu" aria-expanded="true" hidden>Menú</button>
            </div>
            <div class="workspace-menu" id="workspace-menu">
                <nav aria-label="Módulos">
                    @if ($persona->email_tipo === 'almacen')
                        @include('almacen.navegacion')
                    @endif
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
            </div>
        </aside>
        <main class="workspace-content">
            <header class="workspace-topbar">
                <span class="workspace-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($persona->name, 0, 1)) }}</span>
                <div class="workspace-identity">
                    <strong>{{ $persona->name }}</strong>
                    <span>{{ $rolPerfil }}</span>
                </div>
                <a href="{{ route('perfil.show') }}" class="workspace-profile-link" @if (request()->routeIs('perfil.show')) aria-current="page" @endif>Ver perfil</a>
                <button type="button" class="theme-button" data-theme-toggle aria-label="Tema oscuro" aria-pressed="false">
                    <span>Claro</span>
                    <span class="theme-track" aria-hidden="true"></span>
                    <span>Oscuro</span>
                </button>
            </header>
            @if (($solicitudesDeAccesoPendientes ?? 0) || ($reportesPendientesDeRevision ?? 0))
                <nav class="pending-notifications" aria-label="Notificaciones pendientes">
                    @if ($solicitudesDeAccesoPendientes)
                        <a class="pending-notification pending-notification-access" href="{{ route('coordinacion.registros') }}">
                            <strong>{{ $solicitudesDeAccesoPendientes }}</strong>
                            {{ $solicitudesDeAccesoPendientes === 1 ? 'Solicitud de acceso pendiente' : 'Solicitudes de acceso pendientes' }}
                        </a>
                    @endif
                    @if ($reportesPendientesDeRevision)
                        <a class="pending-notification pending-notification-reports" href="{{ route('coordinacion.index', ['estado' => 'nuevas']) }}">
                            <strong>{{ $reportesPendientesDeRevision }}</strong>
                            {{ $reportesPendientesDeRevision === 1 ? 'Reporte por revisar' : 'Reportes por revisar' }}
                        </a>
                    @endif
                </nav>
            @endif
            @yield('content')
        </main>
    </div>
    <script src="{{ asset('mantresc-menu.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
