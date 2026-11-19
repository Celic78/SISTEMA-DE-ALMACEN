@extends('layouts.private')

@section('title', 'Centro de Mando & Coordinación Operativa')
@section('breadcrumb_active', 'Centro de Mando')
@section('header_overline', 'CONTROL CENTRAL OPERATIVO • AUDITORÍA ACTIVA')
@section('header_title', 'Centro de Mando & Coordinación Operativa')
@section('header_subtitle', 'Supervisión en tiempo real de cuadrillas, validación presupuestaria y optimización de despachos de campus.')

@section('header_actions')
<button type="button" 
        class="inline-flex items-center gap-2 px-4 py-2 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
    </svg>
    <span>Pre-notificar Almacén</span>
</button>
@endsection

@section('content')
<!-- FILA 1: TARJETAS KPI DE ALTO IMPACTO (4 COLUMNAS) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- KPI 1: Incidencias Activas -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Incidencias Activas</span>
            <div class="p-1.5 rounded-lg bg-sky-50 text-sky-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
            <span class="text-3xl font-bold text-slate-900 tracking-tight">142</span>
            <span class="text-xs font-medium text-slate-500">+12 turno matutino</span>
        </div>
        <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div class="bg-[#0b2239] h-1.5 rounded-full" style="width: 65%;"></div>
        </div>
    </div>

    <!-- KPI 2: Duplicados Agrupados -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Reportes Agrupados</span>
            <div class="p-1.5 rounded-lg bg-teal-50 text-teal-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
            <span class="text-3xl font-bold text-slate-900 tracking-tight">19</span>
            <span class="text-xs font-medium text-slate-500">52 unificados</span>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[11px] text-emerald-700 font-medium">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Ahorro de 36 hrs de servicio</span>
        </div>
    </div>

    <!-- KPI 3: Requisitos de Aprobación Especial -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-red-700">Autorizaciones Pendientes</span>
            <div class="p-1.5 rounded-lg bg-red-50 text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-baseline justify-between">
            <span class="text-3xl font-bold text-red-700 tracking-tight">4</span>
            <span class="text-xs font-bold text-red-600">&gt; $15,000 / Alturas</span>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-[11px] text-red-600 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Requiere visto bueno formal</span>
        </div>
    </div>

    <!-- KPI 4: Cuadrillas Operativas -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cuadrillas Operativas</span>
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
            <span>3 cuadrillas en base</span>
        </div>
    </div>
</div>

<!-- FILA 2: FILTRO DE DETECCIÓN CRUZADA ANTI-DUPLICADOS (ALGORITMO LBS v2.4) -->
<div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
    <!-- Header del Módulo de Inteligencia -->
    <div class="p-4 sm:p-5 bg-sky-50/50 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl bg-sky-600 text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900">Consolidación de Incidencias Duplicadas</h2>
                    <span class="px-2 py-0.5 rounded-full bg-[#0b2239] text-white text-[10px] font-mono font-semibold">Algoritmo LBS v2.4</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Coincidencia por zona: <strong class="text-slate-700">Edificio 1 • Aula 204</strong> (±10m)</p>
            </div>
        </div>

        <button type="button" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-xl transition-all shadow-xs shrink-0">
            <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Consolidar en Folio Maestro [FM-2024-0988]</span>
        </button>
    </div>

    <!-- Tabla de Detección Duplicada con Cabecera Oscura -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
            <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">Folio</th>
                    <th class="px-4 py-3">Reportante</th>
                    <th class="px-4 py-3">Hora</th>
                    <th class="px-4 py-3">Descripción Reportada</th>
                    <th class="px-4 py-3 text-right">Clasificación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-mono font-bold text-slate-900">#INC-8834</td>
                    <td class="px-4 py-3 font-medium">Prof. Carlos Estrada <span class="text-slate-400">• Docente</span></td>
                    <td class="px-4 py-3 font-mono text-slate-500">08:14</td>
                    <td class="px-4 py-3 text-slate-600">Lámparas centrales parpadean y despiden zumbido continuo.</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                            Base Maestro
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-mono font-bold text-slate-900">#INC-8839</td>
                    <td class="px-4 py-3 font-medium">Mariana Treviño <span class="text-slate-400">• Delegada 2B</span></td>
                    <td class="px-4 py-3 font-mono text-slate-500">08:42</td>
                    <td class="px-4 py-3 text-slate-600">No prenden 4 focos en el fondo del salón 204. No hay luz suficiente.</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                            Duplicado
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-mono font-bold text-slate-900">#INC-8851</td>
                    <td class="px-4 py-3 font-medium">Lic. Rodrigo Alba <span class="text-slate-400">• Asistente Coord.</span></td>
                    <td class="px-4 py-3 font-mono text-slate-500">09:10</td>
                    <td class="px-4 py-3 text-slate-600">Luminaria del salón 204 huele a quemado y se apagó en plena clase.</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                            Duplicado
                        </span>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-mono font-bold text-slate-900">#INC-8862</td>
                    <td class="px-4 py-3 font-medium">Intendencia Zona Norte</td>
                    <td class="px-4 py-3 font-mono text-slate-500">09:55</td>
                    <td class="px-4 py-3 text-slate-600">Lámparas no encienden en Edif 1 Salón 204. Interruptor botado.</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                            Duplicado
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- FILA 3: DOS COLUMNAS OPERATIVAS (MATRIZ DE REPORTES + ASIGNACIÓN DE CUADRILLAS) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Columna Izquierda: Matriz de Reportes Entrantes (7 Cols) -->
    <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden flex flex-col justify-between">
        <div>
            <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Matriz de Reportes Entrantes</h3>
                    <p class="text-xs text-slate-500">Registro y clasificación técnica según normativa campus.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">Filtrar:</span>
                    <select class="text-xs rounded-lg border border-slate-300 py-1 px-2.5 bg-white text-slate-700 focus:outline-none">
                        <option>Todos los Estados</option>
                        <option>Crítica</option>
                        <option>Alta</option>
                        <option>Media</option>
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
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 font-mono block">#INC-8902</span>
                                <span class="text-[11px] text-slate-500">Subestación B • Tablero C</span>
                            </td>
                            <td class="px-4 py-3 font-medium">Eléctrico</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                                    Crítica
                                </span>
                            </td>
                            <td class="px-4 py-3 text-red-600 font-medium text-[11px]">Alta Tensión Requerida</td>
                            <td class="px-4 py-3 text-right">
                                <a href="#" class="font-bold text-sky-700 hover:text-sky-900">Priorizar</a>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 font-mono block">#INC-8898</span>
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
                                <a href="#" class="font-bold text-slate-600 hover:text-slate-900">Revisar</a>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 font-mono block">#INC-8891</span>
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
                                <a href="#" class="font-bold text-slate-600 hover:text-slate-900">Revisar</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="p-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 bg-slate-50/40">
            <span>Mostrando 3 de 142 registros</span>
            <div class="flex gap-1.5">
                <button class="px-2.5 py-1 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100">Anterior</button>
                <button class="px-2.5 py-1 border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-100">Siguiente</button>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Asignación de Cuadrillas (5 Cols) -->
    <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Asignación de Cuadrillas</h3>
                    <p class="text-xs text-slate-500">Disponibilidad de personal y turnos laborales.</p>
                </div>
                <div class="p-1 rounded-full bg-slate-100 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Selector de Turnos -->
            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl mb-4 text-xs font-semibold">
                <button class="py-2 rounded-lg bg-[#0b2239] text-white shadow-xs">
                    Matutino (07:00 - 13:00)
                </button>
                <button class="py-2 rounded-lg text-slate-600 hover:text-slate-900">
                    Vespertino (13:00 - 19:00)
                </button>
            </div>

            <!-- Listado de Cuadrillas -->
            <div class="space-y-3">
                <!-- Cuadrilla 1 -->
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:border-slate-300 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-1 rounded-lg bg-sky-50 text-sky-700 font-mono text-[11px] font-bold">E-01</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Cuadrilla Electricidad Alfa</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500" title="Disponible"></span>
                            </div>
                            <span class="text-[11px] text-slate-500">Ing. Mateo Juárez • 2 Operativos</span>
                        </div>
                    </div>
                    <button class="px-3 py-1.5 bg-[#0b2239] text-white text-xs font-semibold rounded-lg hover:bg-[#071626]">
                        Asignar
                    </button>
                </div>

                <!-- Cuadrilla 2 -->
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/40">
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 font-mono text-[11px] font-bold">P-03</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Cuadrilla Hidráulica Beta</span>
                                <span class="w-2 h-2 rounded-full bg-red-500" title="Ocupada"></span>
                            </div>
                            <span class="text-[11px] text-slate-500">Téc. Ramón Solares • 2 Operativos</span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">Ocupada</span>
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
                            <span class="text-[11px] text-slate-500">Téc. David Peñaloza • 3 Operativos</span>
                        </div>
                    </div>
                    <button class="px-3 py-1.5 bg-[#0b2239] text-white text-xs font-semibold rounded-lg hover:bg-[#071626]">
                        Asignar
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
            <span>Relevo vespertino programado 13:00 hrs.</span>
            <a href="#" class="font-bold text-sky-700 hover:text-sky-900">Ver Bitácora</a>
        </div>
    </div>
</div>

<!-- FILA 4: MÓDULO DE AUTORIZACIONES ESPECIALES (PRESUPUESTO Y TRABAJO EN ALTURAS) -->
<div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Autorizaciones Especiales</h3>
            <p class="text-xs text-slate-500">Revisión obligatoria de partidas extraordinarias (&gt; $15,000 MXN) y protocolos de seguridad.</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
            2 Casos Pendientes de Visado
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Tarjeta de Autorización 1: Presupuesto Excedido -->
        <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/40 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-xs font-bold text-slate-600">EXP-AUT-2024-041</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Presupuesto Excedido</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900">Sustitución de Compresor Chiller #2</h4>
                <p class="text-xs text-slate-500 mt-0.5">Laboratorio Químico-Biológicas • Partida: 5120-MANT-CAMPUS</p>

                <div class="mt-4 flex items-baseline justify-between border-y border-slate-200 py-3">
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Monto Solicitado</span>
                        <span class="text-2xl font-bold text-red-600 tracking-tight">$38,450.00 MXN</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Límite Automático</span>
                        <span class="text-xs font-semibold text-slate-600">$15,000.00 MXN</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
                <button class="px-3 py-1.5 border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-100">
                    Solicitar Cotización
                </button>
                <button class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg">
                    Rechazar Solicitud
                </button>
                <button class="px-3 py-1.5 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-lg">
                    Aprobar Presupuesto
                </button>
            </div>
        </div>

        <!-- Tarjeta de Autorización 2: Trabajo en Alturas -->
        <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/40 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="font-mono text-xs font-bold text-slate-600">SEG-ALT-2024-019</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Trabajo en Alturas (&gt;12m)</span>
                </div>
                <h4 class="text-sm font-bold text-slate-900">Inspección de Domos y Canalones</h4>
                <p class="text-xs text-slate-500 mt-0.5">Auditorio Mayor • Requiere grúa articulada y líneas de vida</p>

                <div class="mt-4 flex items-baseline justify-between border-y border-slate-200 py-3">
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Normativa Aplicable</span>
                        <span class="text-xs font-bold text-slate-800 font-mono">NOM-009-STPS-2011</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Costo Adicional Grúa</span>
                        <span class="text-xl font-bold text-slate-900 tracking-tight">$18,200.00 MXN</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
                <button class="px-3 py-1.5 border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-100">
                    Exigir Dictamen PC
                </button>
                <button class="px-3 py-1.5 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-lg">
                    Aprobar Protocolo
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
