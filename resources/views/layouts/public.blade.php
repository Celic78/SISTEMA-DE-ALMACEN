<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIMIC') }} - @yield('title', 'Portal Institucional')</title>

    <!-- Tipografía Institucional -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Estilos y Scripts compilados con Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-full font-sans antialiased text-slate-800 bg-[#f4f7fb] selection:bg-[#0b2239] selection:text-white flex flex-col justify-between relative overflow-x-hidden">

    <!-- Patrón de fondo geométrico sutil (Dot matrix) -->
    <div class="fixed inset-0 pointer-events-none opacity-40 z-0" 
         style="background-image: radial-gradient(#94a3b8 0.75px, transparent 0.75px); background-size: 20px 20px;">
    </div>

    <!-- Barra Superior Institucional Minimalista -->
    <header class="relative z-10 w-full border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0b2239] flex items-center justify-center text-white shadow-xs">
                    <!-- Icono Birrete / Campus -->
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 leading-tight">Universidad Metropolitana</h1>
                    <p class="text-xs text-slate-500 font-medium">Portal de Mantenimiento y Recursos Materiales</p>
                </div>
            </div>

            <!-- Accesos rápidos / Enlaces institucionales -->
            <div class="hidden sm:flex items-center gap-4 text-xs font-medium text-slate-600">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Servidores Operativos
                </span>
                <a href="mailto:soporte@universidad.edu.mx" class="hover:text-slate-900 transition-colors">
                    Mesa de Ayuda
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="relative z-10 flex-grow flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full @yield('container_width', 'max-w-xl')">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-800 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200 text-sm text-red-800 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>

    <!-- Pie de Página Institucional y Directorio de Soporte -->
    <footer class="relative z-10 border-t border-slate-200 bg-white/70 backdrop-blur-xs py-6 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-700">SIMIC v4.2</span>
                <span>•</span>
                <span>Dirección General de Obras, Servicios y Mantenimiento de Campus</span>
            </div>
            <div class="flex items-center gap-4 text-slate-600">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Mesa de Ayuda TI: Ext. 4300 / 4301
                </span>
                <span>•</span>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Edificio C - Planta Baja
                </span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
