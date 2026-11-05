@extends('layouts.app')

@section('title', 'Portal Institucional de Infraestructura y Mantenimiento')

@section('content')
<div class="space-y-10 -m-4 sm:-m-6 lg:-m-8">

    <!-- NAVBAR INSTITUCIONAL DE ACCESO -->
    <nav class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 px-4 sm:px-8 py-3.5 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#1E3A8A] flex items-center justify-center text-teal-300 shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                    </svg>
                </div>
                <div>
                    <span class="block text-sm font-bold text-slate-900 tracking-tight leading-tight">Universidad Metropolitana</span>
                    <span class="block text-xs font-medium text-slate-500">Dirección de Infraestructura y Servicios de Campus</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Mesa de Operaciones Activa</span>
                </div>

                <a href="{{ route('login') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs sm:text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Iniciar Sesión / Acceso al Portal</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- SECCIÓN HERO EN AZUL INSTITUCIONAL (#1E3A8A) -->
    <section class="relative bg-[#1E3A8A] text-white overflow-hidden py-16 sm:py-24 px-4 sm:px-8 shadow-xl" style="font-family: 'Inter', system-ui, sans-serif;">
        <!-- Decoración geométrica institucional -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" 
             style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;">
        </div>
        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -top-20 w-80 h-80 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-teal-200 uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>SIMIC • Plataforma Oficial de Infraestructura</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight sm:leading-none text-white">
                    Gestión Integral de Mantenimiento y Almacén Escolar
                </h1>

                <p class="text-base sm:text-lg text-blue-100 max-w-2xl leading-relaxed">
                    Atención ágil de reportes de aulas, laboratorios y edificios universitarios con asignación en tiempo real de cuadrillas técnicas y trazabilidad digital de insumos.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('login') }}" 
                       class="px-6 py-3.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-900 font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Reportar Falla en Campus</span>
                    </a>

                    <a href="#metricas" 
                       class="px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/20 backdrop-blur-xs transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Ver Indicadores Públicos</span>
                    </a>
                </div>
            </div>

            <!-- Panel Hero de Estado en Vivo -->
            <div class="lg:col-span-5">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 sm:p-8 space-y-5 shadow-2xl">
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-teal-300 font-bold">Plantel Activo</span>
                            <h2 class="text-lg font-bold text-white">{{ $plantel->nombre ?? 'Campus Central Universidad' }}</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-teal-400/20 text-teal-200 border border-teal-300/30 text-xs font-semibold">
                            Operativo
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-xs text-blue-200">Tiempo SLA Promedio</span>
                            <p class="text-xl font-extrabold text-white mt-1">{{ $metricas['tiempo_promedio_sla'] ?? '4.2 hrs' }}</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-xs text-blue-200">Índice Satisfacción</span>
                            <p class="text-xl font-extrabold text-teal-300 mt-1">{{ $metricas['satisfaccion_usuarios'] ?? '96%' }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-blue-200 leading-relaxed">
                        {{ $plantel->direccion ?? 'Av. Universidad 1000, Ciudad Universitaria. Protocolos de mantenimiento escolar continuo bajo norma NOM-STPS.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- TARJETAS DE MÉTRICAS DEL PLANTEL -->
    <section id="metricas" class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-6" style="font-family: 'Inter', system-ui, sans-serif;">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-[#1E3A8A] uppercase tracking-wider">Tablero Abierto</span>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Métricas y Desempeño Operativo</h2>
                <p class="text-xs sm:text-sm text-slate-500">Transparencia en la resolución de incidencias e infraestructura escolar.</p>
            </div>
            <div class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 self-start sm:self-auto">
                Cifras actualizadas en tiempo real
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Métrica 1: Solicitudes Registradas -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total de Solicitudes</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $metricas['total_solicitudes'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 ml-2">reportes capturados</span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>Registro continuo institucional</span>
                </div>
            </div>

            <!-- Métrica 2: Solicitudes Resueltas -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Incidencias Concluidas</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-emerald-700">{{ $metricas['resueltas'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 ml-2">con dictamen técnico</span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Tasa de efectividad: {{ $metricas['tasa_efectividad'] ?? 98.4 }}%</span>
                </div>
            </div>

            <!-- Métrica 3: En Proceso -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">En Atención Activa</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-amber-700">{{ $metricas['en_proceso'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 ml-2">cuadrillas en campo</span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-amber-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Despacho matutino y vespertino</span>
                </div>
            </div>

            <!-- Métrica 4: Espacios e Infraestructura -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Infraestructura Monitoreada</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-slate-900">{{ $metricas['total_edificios'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500 ml-2">edificios / {{ $metricas['total_espacios'] ?? 0 }} aulas</span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-xs text-indigo-600 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Mapeo jerárquico LBS verificado</span>
                </div>
            </div>
        </div>
    </section>

    <!-- DIRECTORIO DE CONTACTO Y MESA DE AYUDA -->
    <section class="max-w-7xl mx-auto px-4 sm:px-8 pb-12" style="font-family: 'Inter', system-ui, sans-serif;">
        <div class="bg-gradient-to-br from-slate-900 to-[#1E3A8A] text-white rounded-3xl p-8 sm:p-12 shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <span class="text-xs font-bold text-teal-300 uppercase tracking-widest">Atención Institucional</span>
                    <h3 class="text-xl font-bold mt-1 text-white">Mesa de Ayuda de Campus</h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                        Canal directo para soporte de instalaciones eléctricas, hidrosanitarias, mobiliario y cerrajería de alta seguridad.
                    </p>
                </div>
                <div class="space-y-3 text-xs sm:text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-teal-300 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <span>55 1234 5678 (Ext. 101/102 Cuadrillas)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-teal-300 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span>soporte.infraestructura@universidad.edu.mx</span>
                    </div>
                </div>
                <div class="flex flex-col justify-center items-start md:items-end">
                    <a href="{{ route('login') }}" 
                       class="px-6 py-3 rounded-xl bg-white text-[#1E3A8A] font-bold text-xs sm:text-sm hover:bg-slate-100 transition-colors shadow-md">
                        Acceder al Sistema Institucional →
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
