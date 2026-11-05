@extends('layouts.app')

@section('title', 'Control de Inventario y Stock de Insumos')

@section('breadcrumb_active', 'Almacén / Inventario')
@section('user_role', 'ROL: Encargado de Almacén y Suministros')
@section('header_overline', 'GESTIÓN DE RECURSOS MATERIALES • CONTROL DE STOCK & KARDEX')
@section('header_title', 'Catálogo de Insumos & Alertas de Stock Mínimo')
@section('header_subtitle', 'Monitoreo de existencias en almacén central, puntos de reorden, códigos QR y kardex de suministros.')

@section('header_actions')
<div class="flex items-center gap-2">
    <a href="{{ url('/almacen/requisiciones') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-colors">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Ver Requisiciones y Vales</span>
    </a>
    <button type="button" 
            onclick="abrirModalEntrada()" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
        <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Registrar Entrada / Compra</span>
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <!-- FILA DE MÉTRICAS RÁPIDAS DE ALMACÉN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Insumos -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Insumos Catalogados</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">284 SKUs</div>
                <span class="text-[11px] text-slate-400">5 Familias Técnicas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>

        <!-- Alertas de Stock Mínimo (Crítico) -->
        <div class="bg-white border border-red-200 rounded-2xl p-5 shadow-xs flex items-center justify-between bg-red-50/20">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-red-700">Stock Bajo Mínimo</span>
                <div class="text-2xl font-bold text-red-700 mt-1">6 Insumos</div>
                <span class="text-[11px] font-semibold text-red-600">Requiere reorden urgente</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-100 text-red-700 flex items-center justify-center animate-bounce">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>

        <!-- Vales Despachados Hoy -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vales Despachados</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">18 Vales</div>
                <span class="text-[11px] text-teal-600 font-semibold">42 refacciones entregadas</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
        </div>

        <!-- Valorización de Existencias -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Valor de Existencia</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">$482,900</div>
                <span class="text-[11px] text-slate-400">Auditoría Trimestral 2026</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- BANNER DE ALERTA PROMINENTE SI HAY STOCK CRÍTICO -->
    <div class="rounded-2xl bg-red-50 border border-red-200 p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-red-900">Alerta de Suministro: 6 Insumos Críticos Han Rebasado el Punto Mínimo</h3>
                <p class="text-xs text-red-700 mt-0.5">
                    Pastillas termomagnéticas, balastros T8 y válvulas de esfera presentan disponibilidad menor a la reserva operativa establecida por la Dirección.
                </p>
            </div>
        </div>
        <button type="button" 
                onclick="filtrarPorCritico()" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl transition-colors shadow-xs shrink-0">
            <span>Ver Insumos Críticos</span>
        </button>
    </div>

    <!-- TABLA DE INSUMOS Y EXISTENCIAS -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Controles de Búsqueda y Filtro de Familias -->
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-[#0b2239] text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Inventario Central de Refacciones</h2>
                    <p class="text-xs text-slate-500">Kardex de existencias, anaqueles de ubicación y semáforo de reabastecimiento.</p>
                </div>
            </div>

            <!-- Filtros por Familia Técnica -->
            <div class="flex items-center flex-wrap gap-2 text-xs">
                <button type="button" class="tab-filtro px-3 py-1.5 rounded-lg font-semibold bg-[#1E3A8A] text-white shadow-xs" data-cat="todas">
                    Todos
                </button>
                <button type="button" class="tab-filtro px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-cat="criticos">
                    Bajo Mínimo (6)
                </button>
                <button type="button" class="tab-filtro px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-cat="electrico">
                    Eléctrico
                </button>
                <button type="button" class="tab-filtro px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-cat="plomeria">
                    Plomería
                </button>
                <button type="button" class="tab-filtro px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-cat="clima">
                    Climatización
                </button>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Código SKU / QR</th>
                        <th class="px-4 py-3.5">Descripción de Insumo</th>
                        <th class="px-4 py-3.5">Ubicación en Almacén</th>
                        <th class="px-4 py-3.5 text-center">Unidad</th>
                        <th class="px-4 py-3.5 text-center">Stock Actual / Mínimo</th>
                        <th class="px-4 py-3.5 text-center">Estado de Stock</th>
                        <th class="px-4 py-3.5 text-right">Operaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white" id="tabla-inventario">
                    
                    <!-- Fila 1: Balastro T8 (CRÍTICO) -->
                    <tr class="hover:bg-red-50/30 transition-colors item-inventario" data-cat="electrico" data-critico="true">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">SKU-ELEC-014</span>
                            <span class="text-[10px] text-slate-400 font-mono">QR: 750102938411</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Balastro Electrónico 2x32W T8</div>
                            <div class="text-[11px] text-slate-500">Marca Philips • Multivoltaje 120-277V • Alto Factor Potencia</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px]">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Anaquel E-02 • Gaveta 4
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium">Pieza</td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-block text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-red-700">3 pzas</span>
                                    <span class="text-[11px] text-slate-400">/ Mín: 15</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden mt-1">
                                    <div class="bg-red-600 h-1.5 rounded-full" style="width: 20%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-200">
                                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                                Stock Crítico (Déficit: 12)
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirModalAjuste('SKU-ELEC-014', 'Balastro Electrónico 2x32W T8', 3, 15, 'Pieza')"
                                    class="px-2.5 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                Surtir / Ajustar
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 2: Pastilla Termomagnética (CRÍTICO) -->
                    <tr class="hover:bg-red-50/30 transition-colors item-inventario" data-cat="electrico" data-critico="true">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">SKU-ELEC-029</span>
                            <span class="text-[10px] text-slate-400 font-mono">QR: 750102938455</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Interruptor Termomagnético 1x20A</div>
                            <div class="text-[11px] text-slate-500">Square D QO120 • Enchufable 10kA • 120/240V</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px]">
                                Anaquel E-01 • Gaveta 1
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium">Pieza</td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-block text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-red-700">2 pzas</span>
                                    <span class="text-[11px] text-slate-400">/ Mín: 10</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden mt-1">
                                    <div class="bg-red-600 h-1.5 rounded-full" style="width: 20%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-200">
                                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                                Stock Crítico (Déficit: 8)
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirModalAjuste('SKU-ELEC-029', 'Interruptor Termomagnético 1x20A', 2, 10, 'Pieza')"
                                    class="px-2.5 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                Surtir / Ajustar
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 3: Válvula de Esfera (CRÍTICO) -->
                    <tr class="hover:bg-red-50/30 transition-colors item-inventario" data-cat="plomeria" data-critico="true">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">SKU-PLOM-008</span>
                            <span class="text-[10px] text-slate-400 font-mono">QR: 750102938801</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Válvula de Esfera Latón 3/4" Rosca NPT</div>
                            <div class="text-[11px] text-slate-500">Marca Urrea • Paso completo • 600 WOG</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px]">
                                Anaquel P-03 • Gaveta 2
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium">Pieza</td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-block text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-red-700">1 pza</span>
                                    <span class="text-[11px] text-slate-400">/ Mín: 8</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden mt-1">
                                    <div class="bg-red-600 h-1.5 rounded-full" style="width: 12.5%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-200">
                                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                                Stock Crítico (Déficit: 7)
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirModalAjuste('SKU-PLOM-008', 'Válvula de Esfera Latón 3/4 pulg', 1, 8, 'Pieza')"
                                    class="px-2.5 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                Surtir / Ajustar
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 4: Cable THW (ÓPTIMO) -->
                    <tr class="hover:bg-slate-50 transition-colors item-inventario" data-cat="electrico" data-critico="false">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">SKU-ELEC-050</span>
                            <span class="text-[10px] text-slate-400 font-mono">QR: 750102939100</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Cable de Cobre THW-LS Calibre 12 AWG</div>
                            <div class="text-[11px] text-slate-500">IUSA / Condumex • Color Negro • 90°C Antiflama</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px]">
                                Rack Bobinas B-01
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium">Metro</td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-block text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900">340 mts</span>
                                    <span class="text-[11px] text-slate-400">/ Mín: 100</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden mt-1">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 100%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Stock Saludable
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirModalAjuste('SKU-ELEC-050', 'Cable THW Calibre 12', 340, 100, 'Metro')"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors border border-slate-200">
                                Kardex
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 5: Filtro de Aire HVAC (PRECAUCIÓN) -->
                    <tr class="hover:bg-slate-50 transition-colors item-inventario" data-cat="clima" data-critico="false">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">SKU-CLIM-019</span>
                            <span class="text-[10px] text-slate-400 font-mono">QR: 750102939332</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Filtro de Aire Lavable para Minisplit 2 Ton</div>
                            <div class="text-[11px] text-slate-500">Malla electrostática de alta densidad • Mirage / Carrier</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px]">
                                Anaquel C-01 • Nivel 3
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium">Juego</td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <div class="inline-block text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-amber-700">6 juegos</span>
                                    <span class="text-[11px] text-slate-400">/ Mín: 6</span>
                                </div>
                                <div class="w-24 bg-slate-100 rounded-full h-1.5 overflow-hidden mt-1">
                                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 50%;"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                En Punto de Reorden
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirModalAjuste('SKU-CLIM-019', 'Filtro de Aire Minisplit 2 Ton', 6, 6, 'Juego')"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors border border-slate-200">
                                Kardex
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <span>Visualizando <strong class="text-slate-800">5</strong> insumos destacados del catálogo de almacén</span>
            <div class="flex gap-1.5">
                <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50" disabled>Anterior</button>
                <span class="px-3 py-1.5 rounded-lg bg-[#0b2239] text-white font-bold">1</span>
                <button class="px-3 py-1.5 border border-slate-200 rounded-lg bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50" disabled>Siguiente</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE ENTRADA / COMPRA / REORDEN DE STOCK -->
<div id="modal-entrada-stock" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform transition-all">
        
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Registro de Entrada a Almacén</h3>
                    <p class="text-xs text-slate-300">Recepción de factura institucional o ajuste de inventario físico.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalEntrada()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ url('/almacen/entrada') }}" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label for="entrada_sku" class="block font-bold text-slate-700 mb-1">
                    Insumo o Refacción <span class="text-red-500">*</span>
                </label>
                <select id="entrada_sku" name="sku" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                    <option value="SKU-ELEC-014">SKU-ELEC-014: Balastro Electrónico 2x32W T8 (Stock actual: 3)</option>
                    <option value="SKU-ELEC-029">SKU-ELEC-029: Interruptor Termomagnético 1x20A (Stock actual: 2)</option>
                    <option value="SKU-PLOM-008">SKU-PLOM-008: Válvula de Esfera Latón 3/4" (Stock actual: 1)</option>
                    <option value="SKU-ELEC-050">SKU-ELEC-050: Cable de Cobre THW 12 AWG (Stock actual: 340)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="entrada_cantidad" class="block font-bold text-slate-700 mb-1">
                        Cantidad Recibida <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="entrada_cantidad" name="cantidad" value="20" min="1" required
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                </div>

                <div>
                    <label for="entrada_tipo" class="block font-bold text-slate-700 mb-1">
                        Concepto de Movimiento <span class="text-red-500">*</span>
                    </label>
                    <select id="entrada_tipo" name="tipo_movimiento" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="compra" selected>Compra Institucional / Proveedor</option>
                        <option value="donacion">Donación / Transferencia Campus</option>
                        <option value="ajuste">Ajuste de Conteo Físico</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="entrada_factura" class="block font-bold text-slate-700 mb-1">
                        No. Factura / Folio de Recepción
                    </label>
                    <input type="text" id="entrada_factura" name="factura" value="FAC-2026-904"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                </div>

                <div>
                    <label for="entrada_anaquel" class="block font-bold text-slate-700 mb-1">
                        Ubicación Asignada
                    </label>
                    <input type="text" id="entrada_anaquel" name="anaquel" value="Anaquel E-02 • Gaveta 4"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalEntrada()" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Ingresar a Inventario Activo</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalEntrada() {
        document.getElementById('modal-entrada-stock').classList.remove('hidden');
    }

    function cerrarModalEntrada() {
        document.getElementById('modal-entrada-stock').classList.add('hidden');
    }

    function abrirModalAjuste(sku, nombre, actual, min, unidad) {
        document.getElementById('entrada_sku').value = sku;
        abrirModalEntrada();
    }

    function filtrarPorCritico() {
        const btnCritico = document.querySelector('[data-cat="criticos"]');
        if (btnCritico) {
            btnCritico.click();
        }
    }

    // Filtros de tabla
    document.querySelectorAll('.tab-filtro').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-filtro').forEach(b => {
                b.classList.remove('bg-[#1E3A8A]', 'text-white', 'shadow-xs');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            this.classList.remove('bg-slate-100', 'text-slate-700');
            this.classList.add('bg-[#1E3A8A]', 'text-white', 'shadow-xs');

            const cat = this.getAttribute('data-cat');
            const items = document.querySelectorAll('.item-inventario');

            items.forEach(item => {
                if (cat === 'todas') {
                    item.style.display = '';
                } else if (cat === 'criticos') {
                    item.style.display = (item.getAttribute('data-critico') === 'true') ? '' : 'none';
                } else {
                    item.style.display = (item.getAttribute('data-cat') === cat) ? '' : 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
