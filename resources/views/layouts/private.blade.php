<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIMIC') }} - @yield('title', 'Panel Operativo')</title>

    <!-- Tipografía Institucional -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Estilos y Scripts compilados con Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#f4f7fb] selection:bg-[#0b2239] selection:text-white flex overflow-hidden">

    <!-- Patrón de fondo geométrico sutil (Dot matrix) -->
    <div class="fixed inset-0 pointer-events-none opacity-30 z-0" 
         style="background-image: radial-gradient(#94a3b8 0.75px, transparent 0.75px); background-size: 20px 20px;">
    </div>

    <!-- Backdrop móvil para menú lateral -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-30 hidden lg:hidden transition-opacity"></div>

    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside id="main-sidebar" 
           class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col justify-between transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0 shadow-sm lg:shadow-none shrink-0"
           data-collapsed="false">
        
        <!-- Cabecera de Barra Lateral (Logo SIMIC) -->
        <div>
            <div class="h-16 px-5 border-b border-slate-200 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-[#0b2239] flex items-center justify-center text-white shadow-xs shrink-0">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                        </svg>
                    </div>
                    <div class="sidebar-text-element overflow-hidden">
                        <span class="block text-sm font-bold text-slate-900 tracking-tight leading-tight">SIMIC</span>
                        <span class="block text-[10px] font-semibold text-slate-400 tracking-wider uppercase">INFRAESTRUCTURA</span>
                    </div>
                </a>

                <!-- Botón colapsar para desktop -->
                <button id="toggle-sidebar-collapse" 
                        class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                        title="Expandir/Contraer Menú">
                    <svg class="w-4 h-4 sidebar-collapse-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Navegación de Módulos Operativos -->
            <div class="px-3 py-4">
                <div class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider sidebar-text-element">
                    OPERACIÓN DE CAMPUS
                </div>
                <nav class="space-y-1">
                    <!-- 1. Dashboard / Resumen -->
                    <a href="{{ route('dashboard', [], false) ?: '#' }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all group {{ request()->routeIs('dashboard') ? 'bg-[#0b2239] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="sidebar-text-element">Dashboard / Resumen</span>
                    </a>

                    <!-- 2. Reportar Falla (Portal Solicitante) -->
                    <a href="{{ route('reportes.create', [], false) ?: '#' }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all group {{ request()->routeIs('reportes.*') ? 'bg-[#0b2239] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reportes.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="sidebar-text-element">Reportar Falla</span>
                    </a>

                    <!-- 3. Panel de Coordinación -->
                    <a href="{{ route('coordinacion.index', [], false) ?: '#' }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all group {{ request()->routeIs('coordinacion.*') ? 'bg-[#0b2239] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('coordinacion.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span class="sidebar-text-element">Panel de Coordinación</span>
                    </a>

                    <!-- 4. Consola Técnica (Cuadrillas) -->
                    <a href="{{ route('cuadrillas.index', [], false) ?: '#' }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all group {{ request()->routeIs('cuadrillas.*') ? 'bg-[#0b2239] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('cuadrillas.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span class="sidebar-text-element">Consola Técnica</span>
                    </a>

                    <!-- 5. Inventario & Bitácora (Almacén) -->
                    <a href="{{ route('almacen.index', [], false) ?: '#' }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition-all group {{ request()->routeIs('almacen.*') ? 'bg-[#0b2239] text-white shadow-xs' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('almacen.*') ? 'text-teal-400' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="sidebar-text-element">Inventario & Bitácora</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Zona Inferior de Barra Lateral -->
        <div class="p-3 border-t border-slate-200 space-y-1">
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="sidebar-text-element">Configuración</span>
            </a>

            <!-- Cerrar Sesión -->
            <form method="POST" action="{{ route('logout', [], false) ?: '#' }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-text-element">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ÁREA DE CONTENIDO PRINCIPAL -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">

        <!-- BARRA SUPERIOR (HEADER) -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0">
            <!-- Bloque Izquierdo: Botón Menú Móvil + Breadcrumbs -->
            <div class="flex items-center gap-4 min-w-0">
                <button id="toggle-mobile-sidebar" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Breadcrumbs Dinámicos -->
                <nav class="hidden sm:flex items-center gap-2 text-xs text-slate-500 font-medium truncate">
                    <span>Campus Central</span>
                    <span class="text-slate-300">/</span>
                    <span>Dirección de Obras y Servicios</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-900 font-semibold truncate">
                        @yield('breadcrumb_active', 'Centro de Mando')
                    </span>
                </nav>
            </div>

            <!-- Bloque Derecho: Roles, Sincronización, Notificaciones y Usuario -->
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                
                <!-- Badge de Rol Institucional -->
                <div class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>@yield('user_role', 'ROL: Superusuario / Coordinador')</span>
                </div>

                <!-- Notificaciones con Contador -->
                <div class="relative">
                    <button class="relative p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Notificaciones del Sistema">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-[#0b2239] text-[10px] font-bold text-white ring-2 ring-white">
                            3
                        </span>
                    </button>
                </div>

                <!-- Tarjeta de Usuario / Perfil -->
                <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-slate-200">
                    <div class="w-9 h-9 rounded-full bg-[#0b2239] text-white flex items-center justify-center font-bold text-xs shadow-xs ring-2 ring-slate-100">
                        RM
                    </div>
                    <div class="hidden xl:block text-left">
                        <span class="block text-xs font-bold text-slate-900 leading-tight">Arq. Roberto Mendoza</span>
                        <span class="block text-[11px] text-slate-500 font-medium">EMP-884920 / Edif. Rectoría</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- BANNER DE ENCABEZADO DE PÁGINA (SUBHEADER) -->
        @hasSection('header_title')
        <div class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4 shrink-0">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <!-- Overline Institucional -->
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>@yield('header_overline', 'CONTROL CENTRAL OPERATIVO • AUDITORÍA ACTIVA')</span>
                    </div>

                    <!-- Título y Subtítulo -->
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        @yield('header_title')
                    </h1>
                    @hasSection('header_subtitle')
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            @yield('header_subtitle')
                        </p>
                    @endif
                </div>

                <!-- Acciones Principales y Badge de Sincronización -->
                <div class="flex items-center flex-wrap gap-3">
                    <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>SINCRONIZADO: 11:42 AM</span>
                    </div>

                    @yield('header_actions')
                </div>
            </div>
        </div>
        @endif

        <!-- CONTENEDOR CON SCROLL DEL CONTENIDO -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Alertas de Sesión -->
                @if (session('status'))
                    <div class="rounded-xl bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-800 flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-sm text-red-800 flex items-center gap-3 shadow-xs">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts de Interacción (Colapso de Sidebar y Responsive Drawer) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const toggleCollapse = document.getElementById('toggle-sidebar-collapse');
            const toggleMobile = document.getElementById('toggle-mobile-sidebar');

            // Alternar drawer en móvil
            if (toggleMobile && sidebar && backdrop) {
                toggleMobile.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    backdrop.classList.toggle('hidden');
                });

                backdrop.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }

            // Alternar colapso compacto en desktop
            if (toggleCollapse && sidebar) {
                toggleCollapse.addEventListener('click', () => {
                    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    if (isCollapsed) {
                        sidebar.setAttribute('data-collapsed', 'false');
                        sidebar.classList.remove('w-20');
                        sidebar.classList.add('w-64');
                        document.querySelectorAll('.sidebar-text-element').forEach(el => el.classList.remove('hidden'));
                    } else {
                        sidebar.setAttribute('data-collapsed', 'true');
                        sidebar.classList.remove('w-64');
                        sidebar.classList.add('w-20');
                        document.querySelectorAll('.sidebar-text-element').forEach(el => el.classList.add('hidden'));
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
