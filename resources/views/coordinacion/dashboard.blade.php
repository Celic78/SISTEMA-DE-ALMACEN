@extends('layouts.app')

@section('title', 'Centro de Mando & Coordinación Operativa')

@section('breadcrumb_active', 'Coordinación Operativa')
@section('header_overline', 'CONTROL CENTRAL OPERATIVO • MATRIZ ANTI-DUPLICADOS & DESPACHO')
@section('header_title', 'Centro de Mando & Coordinación de Cuadrillas')
@section('header_subtitle', 'Supervisión en tiempo real de cuadrillas técnicas, unificación algorítmica de incidencias y autorización presupuestal.')

@section('header_actions')
<div class="flex items-center gap-2">
    <button type="button" 
            onclick="abrirModalAsignacionRapida()" 
            class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
        <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <span>Asignar Cuadrilla Manual</span>
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <!-- FILA 1: MÉTRICAS GLOBALES DEL CAMPUS (KPIs DE ALTO IMPACTO) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- KPI 1: Incidencias Activas -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Incidencias Activas</span>
                <div class="p-1.5 rounded-lg bg-sky-50 text-[#1E3A8A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-bold text-slate-900 tracking-tight">{{ $metricas['activas'] ?? '142' }}</span>
                <span class="text-xs font-medium text-slate-500">+12 turno matutino</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-[#1E3A8A] h-1.5 rounded-full" style="width: 65%;"></div>
            </div>
        </div>

        <!-- KPI 2: Duplicados Agrupados (Algoritmo LBS) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-700">Reportes Agrupados</span>
                <div class="p-1.5 rounded-lg bg-teal-50 text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-bold text-teal-800 tracking-tight">{{ $metricas['duplicados'] ?? '19' }}</span>
                <span class="text-xs font-semibold text-teal-700">52 unificados</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-emerald-700 font-medium">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Ahorro estimado: 36 hrs cuadrilla</span>
            </div>
        </div>

        <!-- KPI 3: Autorizaciones Especiales Pendientes -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-700">Autorizaciones Mayores</span>
                <div class="p-1.5 rounded-lg bg-red-50 text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-bold text-red-700 tracking-tight">{{ $metricas['autorizaciones'] ?? '4' }}</span>
                <span class="text-xs font-bold text-red-600">&gt; $15,000 / Alturas</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-red-600 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Requiere dictamen de Coordinación</span>
            </div>
        </div>

        <!-- KPI 4: Cuadrillas Operativas y Cobertura -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cuadrillas en Turno</span>
                <div class="p-1.5 rounded-lg bg-slate-100 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-bold text-slate-900 tracking-tight">8 / 11</span>
                <span class="text-xs font-medium text-slate-500">Turno Matutino</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-emerald-700 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>3 disponibles para asignación inmediata</span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE MATRIZ ANTI-DUPLICADOS (ALGORITMO LBS v2.4) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <!-- Header del Módulo de Inteligencia Anti-Duplicados -->
        <div class="p-4 sm:p-5 bg-sky-50/50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-[#0b2239] text-teal-400 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900">Matriz de Detección Cruzada Anti-Duplicados</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#1E3A8A] text-white text-[10px] font-mono font-bold">LBS Engine v2.4</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Agrupación automática por cercanía espacial: <strong class="text-slate-700">Edificio 1 • Aula 204</strong> (Umbral ±15m / Mismo Espacio Físico).
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ url('/coordinacion/consolidar-duplicados') }}">
                @csrf
                <input type="hidden" name="folio_maestro" value="FOL-2026-0834">
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0b2239] hover:bg-[#1E3A8A] text-white text-xs font-bold rounded-xl transition-all shadow-xs shrink-0">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Consolidar en Folio Maestro [FOL-2026-0834]</span>
                </button>
            </form>
        </div>

        <!-- Tabla de Duplicados Detectados -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">Folio Incidencia</th>
                        <th class="px-4 py-3">Solicitante Institucional</th>
                        <th class="px-4 py-3">Hora Reporte</th>
                        <th class="px-4 py-3">Descripción de la Falla</th>
                        <th class="px-4 py-3 text-right">Clasificación Algorítmica</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                    <tr class="bg-blue-50/50 hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-[#1E3A8A]">FOL-2026-0834</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">Prof. Carlos Estrada <span class="text-slate-400 font-normal">• Docente Física</span></td>
                        <td class="px-4 py-3 font-mono text-slate-500">08:14</td>
                        <td class="px-4 py-3 text-slate-700">Lámparas centrales parpadean y despiden zumbido continuo.</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#1E3A8A] text-white shadow-xs">
                                Folio Maestro (Pivote)
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700">FOL-2026-0839</td>
                        <td class="px-4 py-3 font-medium">Mariana Treviño <span class="text-slate-400 font-normal">• Delegada Grupo 2B</span></td>
                        <td class="px-4 py-3 font-mono text-slate-500">08:42</td>
                        <td class="px-4 py-3 text-slate-600">No prenden 4 focos en el fondo del salón 204. No hay luz suficiente.</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                Duplicado 94% Similitud
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700">FOL-2026-0851</td>
                        <td class="px-4 py-3 font-medium">Lic. Rodrigo Alba <span class="text-slate-400 font-normal">• Asistente Coord.</span></td>
                        <td class="px-4 py-3 font-mono text-slate-500">09:10</td>
                        <td class="px-4 py-3 text-slate-600">Luminaria del salón 204 huele a quemado y se apagó en plena clase.</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                Duplicado 91% Similitud
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-slate-700">FOL-2026-0862</td>
                        <td class="px-4 py-3 font-medium">Intendencia Zona Norte</td>
                        <td class="px-4 py-3 font-mono text-slate-500">09:55</td>
                        <td class="px-4 py-3 text-slate-600">Lámparas no encienden en Edif 1 Salón 204. Interruptor botado.</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                Duplicado 88% Similitud
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-3 bg-slate-50 border-t border-slate-200 text-right">
            <span class="text-[11px] text-slate-500">Al consolidar, todas las notificaciones se unificarán en el Folio Maestro FOL-2026-0834 evitando doble despacho de cuadrilla.</span>
        </div>
    </div>

    <!-- FILA 3: DOS COLUMNAS (MATRIZ DE REPORTES ENTRANTES + ASIGNACIÓN DE CUADRILLAS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Columna Izquierda: Matriz de Reportes Entrantes (7 Cols) -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Cola de Incidencias Técnicas</h3>
                        <p class="text-xs text-slate-500">Priorización y despacho de órdenes de trabajo.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Filtrar:</span>
                        <select class="text-xs rounded-lg border border-slate-300 py-1 px-2.5 bg-white text-slate-700 focus:outline-none">
                            <option>Todas las Criticidades</option>
                            <option>Crítica (&lt; 2 hrs)</option>
                            <option>Alta (&lt; 6 hrs)</option>
                            <option>Media (&lt; 24 hrs)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold text-[11px] uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Folio / Ubicación</th>
                                <th class="px-4 py-3">Especialidad</th>
                                <th class="px-4 py-3">Criticidad</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            <!-- Fila 1 -->
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#1E3A8A] font-mono block">FOL-2026-0902</span>
                                    <span class="text-[11px] text-slate-500">Subestación B • Tablero C</span>
                                </td>
                                <td class="px-4 py-3 font-medium">Electricidad</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                                        Crítica
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-red-600 font-medium text-[11px]">Sin Asignar</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" 
                                            onclick="abrirModalAsignacion('FOL-2026-0902', 'Electricidad', 'Subestación B - Tablero C', 'Alta Tensión / Falla de Fases')"
                                            class="px-2.5 py-1 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                        Asignar Cuadrilla
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila 2 -->
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#1E3A8A] font-mono block">FOL-2026-0898</span>
                                    <span class="text-[11px] text-slate-500">Edificio 4 • Sanitarios N1</span>
                                </td>
                                <td class="px-4 py-3 font-medium">Plomería</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Alta
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-[11px]">Pre-asignado C2</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" 
                                            onclick="abrirModalAsignacion('FOL-2026-0898', 'Plomería', 'Edificio 4 - Sanitarios N1', 'Fuga en tubería de 2 pulgadas')"
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors">
                                        Reasignar
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila 3 -->
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-bold text-[#1E3A8A] font-mono block">FOL-2026-0891</span>
                                    <span class="text-[11px] text-slate-500">Biblioteca • Pasillo 3</span>
                                </td>
                                <td class="px-4 py-3 font-medium">Inmueble / Civil</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                                        Media
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500 text-[11px]">En cola de trabajo</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" 
                                            onclick="abrirModalAsignacion('FOL-2026-0891', 'Inmueble / Civil', 'Biblioteca Central Pasillo 3', 'Falso plafón desprendido')"
                                            class="px-2.5 py-1 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                        Asignar Cuadrilla
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 bg-slate-50/40">
                <span>Mostrando 3 de 142 incidencias registradas</span>
                <div class="flex gap-1.5">
                    <button class="px-2.5 py-1 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100">Anterior</button>
                    <button class="px-2.5 py-1 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100">Siguiente</button>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Disponibilidad de Cuadrillas por Turno (5 Cols) -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Cuadrillas Técnicas por Turno</h3>
                        <p class="text-xs text-slate-500">Estado operativo y asignaciones vigentes.</p>
                    </div>
                    <div class="p-1 rounded-full bg-slate-100 text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>

                <!-- Selector Interactivo de Turno (Matutino / Vespertino) -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl mb-4 text-xs font-semibold">
                    <button type="button" id="btn-turno-matutino" onclick="cambiarTurno('matutino')" class="py-2 rounded-lg bg-[#0b2239] text-white shadow-xs">
                        Matutino (07:00 - 13:00)
                    </button>
                    <button type="button" id="btn-turno-vespertino" onclick="cambiarTurno('vespertino')" class="py-2 rounded-lg text-slate-600 hover:text-slate-900">
                        Vespertino (13:00 - 19:00)
                    </button>
                </div>

                <!-- Listado Dinámico de Cuadrillas -->
                <div class="space-y-3" id="lista-cuadrillas">
                    <!-- Cuadrilla 1 -->
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded-lg bg-sky-50 text-sky-700 font-mono text-[11px] font-bold">E-01</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Cuadrilla Electricidad Alfa</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="Disponible"></span>
                                </div>
                                <span class="text-[11px] text-slate-500">Ing. Mateo Juárez • Matutino (Disponible)</span>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="abrirModalAsignacion('FOL-2026-0902', 'Electricidad', 'Subestación B', 'Alta Tensión', 'E-01 - Electricidad Alfa')"
                                class="px-3 py-1.5 bg-[#1E3A8A] text-white text-xs font-semibold rounded-lg hover:bg-[#172554] transition-colors">
                            Asignar
                        </button>
                    </div>

                    <!-- Cuadrilla 2 -->
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 font-mono text-[11px] font-bold">P-03</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Cuadrilla Hidráulica Beta</span>
                                    <span class="w-2 h-2 rounded-full bg-red-500" title="Ocupada"></span>
                                </div>
                                <span class="text-[11px] text-slate-500">Téc. Ramón Solares • En Sanitario Edif 4</span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md">En Servicio</span>
                    </div>

                    <!-- Cuadrilla 3 -->
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">MG-02</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Mant. General Gamma</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="Disponible"></span>
                                </div>
                                <span class="text-[11px] text-slate-500">Téc. Víctor Ramos • Matutino (Disponible)</span>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="abrirModalAsignacion('FOL-2026-0891', 'Inmueble / Civil', 'Biblioteca Central', 'Plafón', 'MG-02 - General Gamma')"
                                class="px-3 py-1.5 bg-[#1E3A8A] text-white text-xs font-semibold rounded-lg hover:bg-[#172554] transition-colors">
                            Asignar
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                <span>Capacidad actual del turno: 72%</span>
                <span class="font-semibold text-emerald-700">3 de 8 cuadrillas libres</span>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE ASIGNACIÓN DE CUADRILLAS TÉCNICAS (POR ESPECIALIDAD Y TURNO) -->
<div id="modal-asignacion-cuadrilla" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden transform transition-all">
        
        <!-- Modal Header -->
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Despacho Oficial de Cuadrilla Técnica</h3>
                    <p class="text-xs text-slate-300">Asignación por especialidad técnica, turno laboral y SLA.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalAsignacion()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Formulario Modal con CSRF -->
        <form method="POST" action="{{ url('/coordinacion/asignar-cuadrilla') }}" class="p-6 space-y-4 text-xs">
            @csrf

            <!-- Tarjeta resumen del folio a asignar -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Folio Seleccionado</span>
                    <span id="modal-asig-folio" class="font-mono font-bold text-[#1E3A8A] text-sm">FOL-2026-0902</span>
                </div>
                <div class="text-slate-700">
                    <strong id="modal-asig-ubicacion">Subestación B • Tablero C</strong>
                    <p id="modal-asig-descripcion" class="text-slate-500 text-[11px] mt-0.5">Alta Tensión Requerida</p>
                </div>
            </div>

            <!-- Especialidad Requerida y Turno -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="asig_especialidad" class="block font-bold text-slate-700 mb-1">
                        Especialidad Técnica Requerida <span class="text-red-500">*</span>
                    </label>
                    <select id="asig_especialidad" name="especialidad" required onchange="filtrarTecnicosPorEspecialidad()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="electricidad">Electricidad e Iluminación</option>
                        <option value="plomeria">Plomería y Red Hidráulica</option>
                        <option value="climatizacion">Climatización y HVAC</option>
                        <option value="civil">Mantenimiento General / Obra Civil</option>
                        <option value="cerrajeria">Cerrajería y Cancelería</option>
                    </select>
                </div>

                <div>
                    <label for="asig_turno" class="block font-bold text-slate-700 mb-1">
                        Turno Laboral Asignado <span class="text-red-500">*</span>
                    </label>
                    <select id="asig_turno" name="turno" required onchange="filtrarTecnicosPorEspecialidad()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="matutino" selected>Matutino (07:00 - 13:00)</option>
                        <option value="vespertino">Vespertino (13:00 - 19:00)</option>
                    </select>
                </div>
            </div>

            <!-- Selección de Cuadrilla Técnica -->
            <div>
                <label for="asig_cuadrilla" class="block font-bold text-slate-700 mb-1">
                    Seleccionar Cuadrilla / Técnico Responsable <span class="text-red-500">*</span>
                </label>
                <select id="asig_cuadrilla" name="id_tecnico" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                    <option value="1">E-01: Ing. Mateo Juárez (Electricidad - Matutino) [Disponible]</option>
                    <option value="2">E-02: Téc. Luis Cárdenas (Electricidad - Vespertino)</option>
                    <option value="3">P-01: Téc. Ramón Solares (Plomería - Matutino) [En Ruta]</option>
                    <option value="4">MG-01: Téc. Víctor Ramos (General - Matutino) [Disponible]</option>
                </select>
            </div>

            <!-- Estimación de SLA y Prioridad -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="asig_sla" class="block font-bold text-slate-700 mb-1">
                        Tiempo Máximo de Atención (SLA) <span class="text-red-500">*</span>
                    </label>
                    <select id="asig_sla" name="horas_sla" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="2">2 Horas (Emergencia Crítica)</option>
                        <option value="6" selected>6 Horas (Atención Prioritaria)</option>
                        <option value="24">24 Horas (Mantenimiento Estándar)</option>
                        <option value="48">48 Horas (Programado Fin de Semana)</option>
                    </select>
                </div>

                <div>
                    <label for="asig_autorizacion" class="block font-bold text-slate-700 mb-1">
                        Presupuesto Estimado Inicial
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold">$</span>
                        <input type="number" id="asig_autorizacion" name="monto_estimado" value="2500" min="0" step="100"
                               class="w-full rounded-xl border border-slate-300 bg-white pl-7 pr-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Instrucciones Técnicas al Personal -->
            <div>
                <label for="asig_instrucciones" class="block font-bold text-slate-700 mb-1">
                    Instrucciones Especiales y Protocolo de Seguridad
                </label>
                <textarea id="asig_instrucciones" name="instrucciones" rows="3"
                          placeholder="Indique si se requiere equipo de protección personal (EPP), corte de energía general o acceso con llave maestra..."
                          class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">Acudir con multímetro industrial y guantes dieléctricos Clase 0. Notificar al prefecto antes de interrumpir el suministro.</textarea>
            </div>

            <!-- Footer con Botones -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalAsignacion()" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#0b2239] hover:bg-[#1E3A8A] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Emitir Orden de Trabajo Oficial</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function cambiarTurno(turno) {
        const btnMat = document.getElementById('btn-turno-matutino');
        const btnVesp = document.getElementById('btn-turno-vespertino');

        if (turno === 'matutino') {
            btnMat.className = 'py-2 rounded-lg bg-[#0b2239] text-white shadow-xs';
            btnVesp.className = 'py-2 rounded-lg text-slate-600 hover:text-slate-900';
        } else {
            btnVesp.className = 'py-2 rounded-lg bg-[#0b2239] text-white shadow-xs';
            btnMat.className = 'py-2 rounded-lg text-slate-600 hover:text-slate-900';
        }
    }

    function abrirModalAsignacion(folio, especialidad, ubicacion, descripcion, cuadrillaPrevia = null) {
        document.getElementById('modal-asig-folio').textContent = folio;
        document.getElementById('modal-asig-ubicacion').textContent = ubicacion;
        document.getElementById('modal-asig-descripcion').textContent = descripcion;

        // Auto-seleccionar especialidad si coincide
        const selectEsp = document.getElementById('asig_especialidad');
        for (let i = 0; i < selectEsp.options.length; i++) {
            if (selectEsp.options[i].text.toLowerCase().includes(especialidad.toLowerCase())) {
                selectEsp.selectedIndex = i;
                break;
            }
        }

        document.getElementById('modal-asignacion-cuadrilla').classList.remove('hidden');
    }

    function abrirModalAsignacionRapida() {
        abrirModalAsignacion('FOL-2026-NUEVO', 'Mantenimiento General', 'Selección de Área Manual', 'Asignación directa por Coordinación');
    }

    function cerrarModalAsignacion() {
        document.getElementById('modal-asignacion-cuadrilla').classList.add('hidden');
    }

    function filtrarTecnicosPorEspecialidad() {
        // Enlace visual para feedback interactivo del usuario
        const select = document.getElementById('asig_cuadrilla');
        const esp = document.getElementById('asig_especialidad').value;
        const turno = document.getElementById('asig_turno').value;
        
        console.log('Filtrando cuadrillas para:', esp, turno);
    }
</script>
@endpush
@endsection
