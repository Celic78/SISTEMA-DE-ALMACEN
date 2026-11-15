@extends('layouts.app')

@section('title', 'Consola Técnica de Órdenes de Trabajo')

@section('breadcrumb_active', 'Consola Técnica')
@section('user_role', 'ROL: Cuadrilla Técnica Especializada')
@section('header_overline', 'SISTEMA OPERATIVO DE CUADRILLAS • TERMINAL DE CAMPO')
@section('header_title', 'Consola de Trabajo & Órdenes de Mantenimiento')
@section('header_subtitle', 'Gestión de flujo de trabajo técnico, actualización de fases operativas, solicitud de refacciones y cumplimiento de SLA.')

@section('header_actions')
<button type="button" 
        onclick="abrirModalRequisicionDirecta()" 
        class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span>Solicitar Refacciones al Almacén</span>
</button>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <!-- BARRA DE INFORMACIÓN DE CUADRILLA Y MÉTRICAS DE TURNO -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#0b2239] text-teal-400 flex items-center justify-center font-bold text-base shadow-sm">
                    E-01
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900">Cuadrilla Electricidad Alfa</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">En Turno Activo</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Responsable: <strong class="text-slate-800">Ing. Mateo Juárez</strong> • Turno Matutino (07:00 - 13:00) • 2 Operativos</p>
                </div>
            </div>

            <!-- Indicador Global de SLA Personal -->
            <div class="flex items-center gap-4 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200">
                <div class="text-right">
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Cumplimiento de SLA</span>
                    <span class="text-sm font-bold text-emerald-700">97.8% dentro de tiempo</span>
                </div>
                <div class="w-10 h-10 rounded-full border-4 border-emerald-500 flex items-center justify-center text-xs font-bold text-slate-800">
                    4/4
                </div>
            </div>
        </div>

        <!-- 4 Contadores Rápidos de la Consola -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Órdenes Asignadas</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5">4 Activas</div>
                <span class="text-[11px] text-slate-500">2 con alta prioridad</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-sky-600">En Diagnóstico / Sitio</span>
                <div class="text-xl font-bold text-sky-700 mt-0.5">2 Órdenes</div>
                <span class="text-[11px] text-sky-600/80">Evaluación de fallas</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-amber-600">Espera de Material</span>
                <div class="text-xl font-bold text-amber-700 mt-0.5">1 Vale Pendiente</div>
                <span class="text-[11px] text-amber-600/80">Despacho en Almacén</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-emerald-600">Concluidas Hoy</span>
                <div class="text-xl font-bold text-emerald-700 mt-0.5">3 Cerradas</div>
                <span class="text-[11px] text-emerald-600/80">Firmas verificadas</span>
            </div>
        </div>
    </div>

    <!-- TABLA DE ÓRDENES DE TRABAJO ACTIVAS -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-[#0b2239] text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Órdenes de Trabajo en Atención de Campo</h3>
                    <p class="text-xs text-slate-500">Actualice la fase de atención y registre la trazabilidad técnica.</p>
                </div>
            </div>

            <!-- Filtro de Estado -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Filtrar Fase:</span>
                <select class="text-xs rounded-xl border border-slate-300 py-1.5 px-3 bg-white text-slate-700 focus:outline-none">
                    <option>Todas las fases activas</option>
                    <option>Fase 1: Diagnóstico</option>
                    <option>Fase 2: Espera de Material</option>
                    <option>Fase 3: Pruebas</option>
                    <option>Fase 4: Concluido</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Orden / Folio Origen</th>
                        <th class="px-4 py-3.5">Ubicación de Campus</th>
                        <th class="px-4 py-3.5">Fase de Trabajo (Stepper)</th>
                        <th class="px-4 py-3.5">Estimación de SLA</th>
                        <th class="px-4 py-3.5">Detalle Técnico</th>
                        <th class="px-4 py-3.5 text-right">Acciones Operativas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                    
                    <!-- Orden 1: En Diagnóstico -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block text-xs">OT-2026-0412</span>
                            <span class="text-[10px] text-slate-400 font-mono">Ref: FOL-2026-0834</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Edificio 1 - Docencia Norte</div>
                            <div class="text-[11px] text-slate-500">Aula 204 (2do Nivel)</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                    <span class="w-2 h-2 rounded-full bg-sky-600 animate-pulse"></span>
                                    1. Diagnóstico Inicial
                                </span>
                                <!-- Mini stepper -->
                                <div class="flex items-center gap-1 text-[9px] text-slate-400">
                                    <span class="text-sky-700 font-bold">Diag</span>
                                    <span>&rarr;</span>
                                    <span>Material</span>
                                    <span>&rarr;</span>
                                    <span>Pruebas</span>
                                    <span>&rarr;</span>
                                    <span>Fin</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                <span class="font-bold text-slate-900">01h 45m restantes</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5">SLA Total: 03:00 hrs</span>
                        </td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <p class="truncate font-medium text-slate-800">Lámparas centrales parpadean. Ruido de balastro y caída de voltaje.</p>
                            <span class="text-[11px] text-slate-400">Reportado por: Prof. Carlos Estrada</span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                            <button type="button" 
                                    onclick="abrirModalActualizarEstado({
                                        orden: 'OT-2026-0412',
                                        folio: 'FOL-2026-0834',
                                        ubicacion: 'Edificio 1 • Aula 204',
                                        fase: 'diagnostico',
                                        sla: 3,
                                        notas: 'Se identificó balastro electrónico quemado en luminaria 3.'
                                    })"
                                    class="px-2.5 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                Actualizar Estado
                            </button>
                            <button type="button" 
                                    onclick="abrirModalSolicitudRefaccion('OT-2026-0412', 'FOL-2026-0834', 'Edificio 1 • Aula 204')"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors border border-slate-200">
                                Pedir Material
                            </button>
                        </td>
                    </tr>

                    <!-- Orden 2: En Espera de Material -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block text-xs">OT-2026-0409</span>
                            <span class="text-[10px] text-slate-400 font-mono">Ref: FOL-2026-0819</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Edificio 3 - Talleres</div>
                            <div class="text-[11px] text-slate-500">Taller de Máquinas y Herramientas</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                    2. Espera de Material
                                </span>
                                <div class="flex items-center gap-1 text-[9px] text-slate-400">
                                    <span class="text-emerald-600 font-bold">&check; Diag</span>
                                    <span>&rarr;</span>
                                    <span class="text-amber-700 font-bold">Material</span>
                                    <span>&rarr;</span>
                                    <span>Pruebas</span>
                                    <span>&rarr;</span>
                                    <span>Fin</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                <span class="font-bold text-amber-800">Vale VAL-2026-0182</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5">En mostrador de Almacén</span>
                        </td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <p class="truncate font-medium text-slate-800">Reemplazo de pastilla termomagnética trifásica 3x50A Schneider.</p>
                            <span class="text-[11px] text-slate-400">Solicitado: 2 unidades</span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                            <button type="button" 
                                    onclick="abrirModalActualizarEstado({
                                        orden: 'OT-2026-0409',
                                        folio: 'FOL-2026-0819',
                                        ubicacion: 'Edificio 3 • Taller Máquinas',
                                        fase: 'espera_material',
                                        sla: 6,
                                        notas: 'En espera de despacho físico en ventanilla de almacén.'
                                    })"
                                    class="px-2.5 py-1.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-lg text-xs transition-colors">
                                Actualizar Estado
                            </button>
                            <button type="button" 
                                    onclick="abrirModalSolicitudRefaccion('OT-2026-0409', 'FOL-2026-0819', 'Edificio 3 • Taller Máquinas')"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors border border-slate-200">
                                Pedir Material
                            </button>
                        </td>
                    </tr>

                    <!-- Orden 3: En Pruebas -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#1E3A8A] block text-xs">OT-2026-0401</span>
                            <span class="text-[10px] text-slate-400 font-mono">Ref: FOL-2026-0805</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">Biblioteca Central</div>
                            <div class="text-[11px] text-slate-500">Pasillo de Acervo Reservado</div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                    <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                                    3. Pruebas y Calibración
                                </span>
                                <div class="flex items-center gap-1 text-[9px] text-slate-400">
                                    <span class="text-emerald-600 font-bold">&check; Diag</span>
                                    <span>&rarr;</span>
                                    <span class="text-emerald-600 font-bold">&check; Mat</span>
                                    <span>&rarr;</span>
                                    <span class="text-purple-700 font-bold">Pruebas</span>
                                    <span>&rarr;</span>
                                    <span>Fin</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                <span class="font-bold text-slate-900">Listo para entrega</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Verificación de carga 120V</span>
                        </td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <p class="truncate font-medium text-slate-800">Conexión de nuevo circuito con cable THW Calibre 12 y contactos polarizados.</p>
                            <span class="text-[11px] text-slate-400">Tensión estable a 122.4V</span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap space-x-1">
                            <button type="button" 
                                    onclick="abrirModalActualizarEstado({
                                        orden: 'OT-2026-0401',
                                        folio: 'FOL-2026-0805',
                                        ubicacion: 'Biblioteca Central • Pasillo Reservado',
                                        fase: 'pruebas',
                                        sla: 2,
                                        notas: 'Medición de aislamiento exitosa. Pendiente firma de visto bueno.'
                                    })"
                                    class="px-2.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs transition-colors">
                                Concluir Trabajo
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL 1: ACTUALIZACIÓN DE FASES Y ESTADOS (STEPPER DE 4 FASES + SLA) -->
<div id="modal-actualizar-estado" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden transform transition-all">
        
        <!-- Header -->
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Actualizar Fase Operativa de la Orden</h3>
                    <p class="text-xs text-slate-300" id="modal-fase-orden-title">OT-2026-0412</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalActualizarEstado()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ url('/tecnico/actualizar-orden') }}" enctype="multipart/form-data" class="p-6 space-y-5 text-xs">
            @csrf
            <input type="hidden" name="orden_id" id="modal-input-orden-id" value="">

            <!-- Resumen de Orden -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <span class="block text-[10px] font-bold uppercase text-slate-400">Localización</span>
                <span id="modal-fase-ubicacion" class="font-bold text-slate-900 text-sm mt-0.5 block">Edificio 1 • Aula 204</span>
            </div>

            <!-- SELECCIÓN DE FASE OPERATIVA (4 FASES REQUERIDAS) -->
            <div>
                <label class="block font-bold text-slate-800 mb-2">
                    Fase Técnica Actual <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <!-- Fase 1: Diagnóstico -->
                    <label class="cursor-pointer">
                        <input type="radio" name="fase_estado" value="diagnostico" class="peer sr-only" id="fase-radio-diagnostico">
                        <div class="p-3 rounded-xl border border-slate-200 text-center peer-checked:border-sky-600 peer-checked:bg-sky-50 peer-checked:text-sky-900 hover:bg-slate-50 transition-all">
                            <span class="block text-[10px] font-bold uppercase text-slate-400 peer-checked:text-sky-700">Paso 1</span>
                            <span class="font-bold block mt-1">Diagnóstico</span>
                        </div>
                    </label>

                    <!-- Fase 2: Espera de Material -->
                    <label class="cursor-pointer">
                        <input type="radio" name="fase_estado" value="espera_material" class="peer sr-only" id="fase-radio-material">
                        <div class="p-3 rounded-xl border border-slate-200 text-center peer-checked:border-amber-600 peer-checked:bg-amber-50 peer-checked:text-amber-900 hover:bg-slate-50 transition-all">
                            <span class="block text-[10px] font-bold uppercase text-slate-400 peer-checked:text-amber-700">Paso 2</span>
                            <span class="font-bold block mt-1">Espera Material</span>
                        </div>
                    </label>

                    <!-- Fase 3: Pruebas -->
                    <label class="cursor-pointer">
                        <input type="radio" name="fase_estado" value="pruebas" class="peer sr-only" id="fase-radio-pruebas">
                        <div class="p-3 rounded-xl border border-slate-200 text-center peer-checked:border-purple-600 peer-checked:bg-purple-50 peer-checked:text-purple-900 hover:bg-slate-50 transition-all">
                            <span class="block text-[10px] font-bold uppercase text-slate-400 peer-checked:text-purple-700">Paso 3</span>
                            <span class="font-bold block mt-1">Pruebas</span>
                        </div>
                    </label>

                    <!-- Fase 4: Concluido -->
                    <label class="cursor-pointer">
                        <input type="radio" name="fase_estado" value="concluido" class="peer sr-only" id="fase-radio-concluido">
                        <div class="p-3 rounded-xl border border-slate-200 text-center peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-900 hover:bg-slate-50 transition-all">
                            <span class="block text-[10px] font-bold uppercase text-slate-400 peer-checked:text-emerald-700">Paso 4</span>
                            <span class="font-bold block mt-1">Concluido</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Estimación de SLA Restante -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="modal-input-sla" class="block font-bold text-slate-700 mb-1">
                        Ajuste de Estimación SLA (Horas) <span class="text-red-500">*</span>
                    </label>
                    <select id="modal-input-sla" name="horas_restantes" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="1">1 Hora (Cierre inmediato)</option>
                        <option value="2" selected>2 Horas</option>
                        <option value="4">4 Horas</option>
                        <option value="8">8 Horas (Turno siguiente)</option>
                        <option value="24">24 Horas (Espera de proveedor)</option>
                    </select>
                </div>

                <div>
                    <label for="modal-input-evidencia" class="block font-bold text-slate-700 mb-1">
                        Foto de Evidencia de Campo (Opcional)
                    </label>
                    <input type="file" id="modal-input-evidencia" name="evidencia_tecnica" accept="image/*"
                           class="w-full text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                </div>
            </div>

            <!-- Bitácora de Observaciones Técnicas -->
            <div>
                <label for="modal-input-notas" class="block font-bold text-slate-700 mb-1">
                    Bitácora / Dictamen Técnico de la Cuadrilla <span class="text-red-500">*</span>
                </label>
                <textarea id="modal-input-notas" name="notas_tecnicas" rows="3" required
                          placeholder="Describa el trabajo realizado, refacciones instaladas o motivo del retraso..."
                          class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 focus:border-sky-600 focus:outline-none"></textarea>
            </div>

            <!-- Footer con Botones -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalActualizarEstado()" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#0b2239] hover:bg-[#1E3A8A] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Guardar Actualización en Bitácora</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: SOLICITUD DE REFACCIONES AL ALMACÉN -->
<div id="modal-solicitar-refaccion" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform transition-all">
        
        <!-- Header -->
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Requisición de Insumos al Almacén</h3>
                    <p class="text-xs text-slate-300" id="modal-ref-orden-sub">OT-2026-XXXX</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalSolicitudRefaccion()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ url('/tecnico/solicitar-material') }}" class="p-6 space-y-4 text-xs">
            @csrf
            <input type="hidden" name="orden_id" id="modal-ref-input-orden-id" value="">

            <!-- Selección de Insumo del Catálogo de Almacén -->
            <div>
                <label for="ref_material_id" class="block font-bold text-slate-700 mb-1">
                    Material / Insumo Requerido <span class="text-red-500">*</span>
                </label>
                <select id="ref_material_id" name="id_material" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                    <option value="1">MAT-ELEC-01: Balastro Electrónico 2x32W T8 Philips (Stock: 14 pzas)</option>
                    <option value="2">MAT-ELEC-04: Pastilla Termomagnética 1x20A Square D (Stock: 8 pzas)</option>
                    <option value="3">MAT-ELEC-09: Cable THW Calibre 12 AWG (Stock: 120 mts)</option>
                    <option value="4">MAT-PLOM-02: Válvula de Esfera de Latón 3/4 pulg Urrea (Stock: 5 pzas)</option>
                    <option value="5">MAT-CLIM-01: Capacitor de Arranque 45 uF 450V (Stock: 3 pzas)</option>
                </select>
            </div>

            <!-- Cantidad y Unidad -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="ref_cantidad" class="block font-bold text-slate-700 mb-1">
                        Cantidad Requerida <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="ref_cantidad" name="cantidad" value="1" min="1" max="50" required
                           class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">
                        Urgencia de Despacho
                    </label>
                    <select name="urgencia" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">
                        <option value="inmediata">Inmediata (&lt; 15 mins)</option>
                        <option value="normal" selected>Normal (Turno actual)</option>
                    </select>
                </div>
            </div>

            <!-- Justificación Técnica -->
            <div>
                <label for="ref_justificacion" class="block font-bold text-slate-700 mb-1">
                    Justificación Técnica para el Vale Digital <span class="text-red-500">*</span>
                </label>
                <textarea id="ref_justificacion" name="justificacion" rows="2" required
                          placeholder="Indique el motivo por el cual la refacción existente no puede ser reparada..."
                          class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-800 focus:border-sky-600 focus:outline-none">Reemplazo por desgaste y daño irreversible en componentes internos.</textarea>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="cerrarModalSolicitudRefaccion()" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#1E3A8A] hover:bg-[#172554] text-white font-bold rounded-xl shadow-md transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Generar Requisición Electrónica</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalActualizarEstado(data) {
        document.getElementById('modal-fase-orden-title').textContent = data.orden + ' • ' + data.folio;
        document.getElementById('modal-input-orden-id').value = data.orden;
        document.getElementById('modal-fase-ubicacion').textContent = data.ubicacion;
        document.getElementById('modal-input-notas').value = data.notas || '';

        // Seleccionar radio button correspondiente a la fase
        const radios = {
            'diagnostico': 'fase-radio-diagnostico',
            'espera_material': 'fase-radio-material',
            'pruebas': 'fase-radio-pruebas',
            'concluido': 'fase-radio-concluido'
        };

        if (radios[data.fase]) {
            document.getElementById(radios[data.fase]).checked = true;
        }

        document.getElementById('modal-actualizar-estado').classList.remove('hidden');
    }

    function cerrarModalActualizarEstado() {
        document.getElementById('modal-actualizar-estado').classList.add('hidden');
    }

    function abrirModalSolicitudRefaccion(orden, folio, ubicacion) {
        document.getElementById('modal-ref-orden-sub').textContent = orden + ' (Ref: ' + folio + ' - ' + ubicacion + ')';
        document.getElementById('modal-ref-input-orden-id').value = orden;
        document.getElementById('modal-solicitar-refaccion').classList.remove('hidden');
    }

    function abrirModalRequisicionDirecta() {
        abrirModalSolicitudRefaccion('OT-2026-DIRECTA', 'Requisición de Cuadrilla', 'Almacén Central de Refacciones');
    }

    function cerrarModalSolicitudRefaccion() {
        document.getElementById('modal-solicitar-refaccion').classList.add('hidden');
    }
</script>
@endpush
@endsection
