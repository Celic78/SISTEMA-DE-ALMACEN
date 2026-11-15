@extends('layouts.app')

@section('title', 'Gestión de Requisiciones y Vales Digitales de Salida')

@section('breadcrumb_active', 'Almacén / Requisiciones')
@section('user_role', 'ROL: Encargado de Almacén y Suministros')
@section('header_overline', 'SISTEMA DE DESPACHO • VALES DIGITALES DE SALIDA')
@section('header_title', 'Gestión de Requisiciones & Vales Digitales')
@section('header_subtitle', 'Autorización de solicitudes de material de cuadrillas, validación de stock y emisión del Vale Oficial de Salida.')

@section('header_actions')
<a href="{{ url('/almacen/inventario') }}" 
   class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition-colors">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span>Consultar Catálogo de Inventario</span>
</a>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <!-- FILA DE MÉTRICAS RÁPIDAS DE DESPACHO -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Requisiciones Pendientes -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Por Despachar</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">3 Pedidos</div>
                <span class="text-[11px] text-amber-600/80">Cuadrillas en ventanilla</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Vales Emitidos Hoy -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Vales Emitidos Hoy</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">14 Vales</div>
                <span class="text-[11px] text-emerald-600 font-semibold">100% validados por QR</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Refacciones Despachadas -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Insumos Entregados</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">38 Unidades</div>
                <span class="text-[11px] text-slate-400">Descuento automático Kardex</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>

        <!-- Tiempo de Atención en Mostrador -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tiempo de Surtido</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">6.2 mins</div>
                <span class="text-[11px] text-teal-600 font-semibold">Excelente rendimiento</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- TABLA DE REQUISICIONES DE CUADRILLAS -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-[#0b2239] text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Cola de Solicitudes de Refacciones</h2>
                    <p class="text-xs text-slate-500">Verifique existencias y genere el Vale Digital de Salida oficial.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Filtrar:</span>
                <select class="text-xs rounded-xl border border-slate-300 py-1.5 px-3 bg-white text-slate-700 focus:outline-none">
                    <option>Todas las solicitudes</option>
                    <option>Pendientes de Salida</option>
                    <option>Despachadas Hoy</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Folio Requisición / OT</th>
                        <th class="px-4 py-3.5">Cuadrilla / Solicitante</th>
                        <th class="px-4 py-3.5">Material Solicitado</th>
                        <th class="px-4 py-3.5 text-center">Cantidad</th>
                        <th class="px-4 py-3.5 text-center">Disponibilidad en Almacén</th>
                        <th class="px-4 py-3.5 text-center">Estado del Pedido</th>
                        <th class="px-4 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                    
                    <!-- Fila 1: Requisición Pendiente -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">REQ-2026-0382</span>
                            <span class="text-[10px] text-slate-400 font-mono">OT: OT-2026-0412</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Cuadrilla Electricidad Alfa</div>
                            <div class="text-[11px] text-slate-500">Ing. Mateo Juárez • Aula 204 Edif 1</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800">Balastro Electrónico 2x32W T8</div>
                            <div class="text-[10px] text-slate-400 font-mono">SKU-ELEC-014 • Philips</div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-bold text-slate-900">
                            2 pzas
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                &check; 3 pzas en existencia
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Pendiente Despacho
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirVistaPreviaVale({
                                        vale: 'VAL-2026-0184',
                                        req: 'REQ-2026-0382',
                                        orden: 'OT-2026-0412',
                                        cuadrilla: 'Cuadrilla Electricidad Alfa (Ing. Mateo Juárez)',
                                        material: 'Balastro Electrónico 2x32W T8 Philips (SKU-ELEC-014)',
                                        cantidad: '2 Piezas',
                                        ubicacion_destino: 'Edificio 1 - Docencia Norte • Aula 204',
                                        anaquel: 'Anaquel E-02 • Gaveta 4',
                                        fecha: '2026-11-05 10:15 AM'
                                    })"
                                    class="px-3 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors shadow-xs">
                                Despachar Vale Digital
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 2: Requisición Pendiente -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block">REQ-2026-0381</span>
                            <span class="text-[10px] text-slate-400 font-mono">OT: OT-2026-0409</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Cuadrilla Mantenimiento General</div>
                            <div class="text-[11px] text-slate-500">Téc. Víctor Ramos • Taller Mecatrónica</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800">Cable de Cobre THW 12 AWG Negro</div>
                            <div class="text-[10px] text-slate-400 font-mono">SKU-ELEC-050 • Condumex</div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-bold text-slate-900">
                            25 mts
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                &check; 340 mts en existencia
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Pendiente Despacho
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirVistaPreviaVale({
                                        vale: 'VAL-2026-0183',
                                        req: 'REQ-2026-0381',
                                        orden: 'OT-2026-0409',
                                        cuadrilla: 'Cuadrilla Mantenimiento General (Téc. Víctor Ramos)',
                                        material: 'Cable de Cobre THW 12 AWG Negro (SKU-ELEC-050)',
                                        cantidad: '25 Metros',
                                        ubicacion_destino: 'Edificio 3 - Talleres • Taller Mecatrónica',
                                        anaquel: 'Rack Bobinas B-01',
                                        fecha: '2026-11-05 09:40 AM'
                                    })"
                                    class="px-3 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors shadow-xs">
                                Despachar Vale Digital
                            </button>
                        </td>
                    </tr>

                    <!-- Fila 3: Ya Despachado -->
                    <tr class="hover:bg-slate-50 transition-colors bg-slate-50/40">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-700 block">REQ-2026-0379</span>
                            <span class="text-[10px] text-slate-400 font-mono">OT: OT-2026-0401</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800">Cuadrilla Hidráulica Beta</div>
                            <div class="text-[11px] text-slate-500">Téc. Ramón Solares • Edif 4 Sanitarios</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-slate-700">Válvula de Esfera Latón 3/4" Urrea</div>
                            <div class="text-[10px] text-slate-400 font-mono">SKU-PLOM-008</div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap font-medium text-slate-700">
                            1 pza
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="text-[11px] text-slate-400 font-mono">Kardex Actualizado</span>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Despachado (VAL-2026-0182)
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <button type="button" 
                                    onclick="abrirVistaPreviaVale({
                                        vale: 'VAL-2026-0182',
                                        req: 'REQ-2026-0379',
                                        orden: 'OT-2026-0401',
                                        cuadrilla: 'Cuadrilla Hidráulica Beta (Téc. Ramón Solares)',
                                        material: 'Válvula de Esfera Latón 3/4 pulg (SKU-PLOM-008)',
                                        cantidad: '1 Pieza',
                                        ubicacion_destino: 'Edificio 4 • Sanitarios N1',
                                        anaquel: 'Anaquel P-03 • Gaveta 2',
                                        fecha: '2026-11-05 08:10 AM'
                                    })"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors border border-slate-200">
                                Ver Vale Digital
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL / VISTA PREVIA PARA DESPACHO DE VALE DIGITAL DE SALIDA -->
<div id="modal-vale-digital" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden transform transition-all">
        
        <!-- Header del Vale -->
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Vale Digital Oficial de Salida de Almacén</h3>
                    <p class="text-xs text-slate-300">Trazabilidad oficial de suministros y descuento en inventario.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarVistaPreviaVale()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Cuerpo del Vale (Diseño Institucional con Código QR Simulado) -->
        <form method="POST" action="{{ url('/almacen/despachar-vale') }}" class="p-6 space-y-5 text-xs text-slate-700">
            @csrf
            <input type="hidden" name="vale_id" id="vale-input-id" value="">

            <!-- Document Header -->
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase text-slate-400">Folio Oficial del Vale</span>
                    <div id="vale-folio" class="text-lg font-mono font-bold text-[#1E3A8A]">VAL-2026-0184</div>
                    <div class="text-[11px] text-slate-500">
                        Requisición: <strong id="vale-req" class="text-slate-700">REQ-2026-0382</strong> • Orden: <strong id="vale-orden" class="text-slate-700">OT-2026-0412</strong>
                    </div>
                </div>

                <!-- QR Token Simulado -->
                <div class="flex items-center gap-3 bg-white p-2.5 rounded-xl border border-slate-200 shadow-xs">
                    <div class="w-14 h-14 bg-slate-900 rounded-lg flex items-center justify-center text-teal-400 font-mono text-[10px] text-center p-1 leading-tight">
                        [QR-SIMIC-SECURE-2026]
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">Token de Firma</span>
                        <span class="block font-mono font-bold text-slate-900">#TK-8942-X</span>
                        <span class="text-[10px] text-emerald-600 font-semibold">Criptográfico SHA-256</span>
                    </div>
                </div>
            </div>

            <!-- Datos de Entrega -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Cuadrilla Receptora</span>
                    <span id="vale-cuadrilla" class="font-bold text-slate-900 block mt-0.5">Cuadrilla Electricidad Alfa</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Destino de la Instalación</span>
                    <span id="vale-destino" class="font-bold text-slate-900 block mt-0.5">Edificio 1 • Aula 204</span>
                </div>
            </div>

            <!-- Tabla de Material a Despachar -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-[#0b2239] text-white px-4 py-2 font-bold text-[11px] uppercase tracking-wider flex items-center justify-between">
                    <span>Insumos Asignados para Entrega Inmediata</span>
                    <span id="vale-anaquel" class="text-teal-300 text-[10px] font-mono">Anaquel E-02</span>
                </div>
                <div class="p-4 bg-white flex items-center justify-between">
                    <div>
                        <span id="vale-material" class="font-bold text-slate-900 text-sm block">Balastro Electrónico 2x32W T8</span>
                        <span class="text-[11px] text-slate-500">Unidad despachada del inventario del campus central</span>
                    </div>
                    <div class="text-right">
                        <span id="vale-cantidad" class="text-base font-bold text-[#1E3A8A]">2 Piezas</span>
                        <span class="block text-[10px] text-emerald-600 font-bold">&check; Existencia validada</span>
                    </div>
                </div>
            </div>

            <!-- Firma y Validación de Almacenista -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Almacenista Despachador en Turno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="almacenista" value="C.P. Mario Delgado Ruiz (EMP-49201)" readonly
                           class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-700 font-semibold focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Método de Autenticación de Cuadrilla <span class="text-red-500">*</span>
                    </label>
                    <select name="metodo_autenticacion" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="qr">Escaneo de Credencial Digital (QR)</option>
                        <option value="firma">Firma Digital en Tableta</option>
                        <option value="token">Token Institucional de Ventanilla</option>
                    </select>
                </div>
            </div>

            <!-- Footer con Botones -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="imprimirVale()" class="text-slate-500 hover:text-slate-800 flex items-center gap-1.5 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Imprimir Copia Física</span>
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="cerrarVistaPreviaVale()" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-[#0b2239] hover:bg-[#1E3A8A] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Confirmar Entrega y Descontar Stock</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirVistaPreviaVale(data) {
        document.getElementById('vale-folio').textContent = data.vale;
        document.getElementById('vale-input-id').value = data.vale;
        document.getElementById('vale-req').textContent = data.req;
        document.getElementById('vale-orden').textContent = data.orden;
        document.getElementById('vale-cuadrilla').textContent = data.cuadrilla;
        document.getElementById('vale-destino').textContent = data.ubicacion_destino;
        document.getElementById('vale-material').textContent = data.material;
        document.getElementById('vale-cantidad').textContent = data.cantidad;
        document.getElementById('vale-anaquel').textContent = data.anaquel;

        document.getElementById('modal-vale-digital').classList.remove('hidden');
    }

    function cerrarVistaPreviaVale() {
        document.getElementById('modal-vale-digital').classList.add('hidden');
    }

    function imprimirVale() {
        window.print();
    }
</script>
@endpush
@endsection
