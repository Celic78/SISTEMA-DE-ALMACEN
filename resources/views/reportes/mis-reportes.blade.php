@extends('layouts.app')

@section('title', 'Historial de Mis Reportes')

@section('breadcrumb_active', 'Mis Reportes')
@section('header_overline', 'PORTAL INSTITUCIONAL DEL SOLICITANTE • SEGUIMIENTO DE INCIDENCIAS')
@section('header_title', 'Historial de Mis Reportes de Campus')
@section('header_subtitle', 'Consulte el estado operativo, cuadrillas asignadas y avance de solución de sus requerimientos técnicos.')

@section('header_actions')
<a href="{{ url('/reportes/crear') }}" 
   class="inline-flex items-center gap-2 px-4 py-2 bg-[#1E3A8A] hover:bg-[#172554] text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
    <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    <span>Levantar Nuevo Reporte</span>
</a>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <!-- FILA DE MÉTRICAS RÁPIDAS DEL SOLICITANTE -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tarjeta 1: Total Reportes -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Levantados</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ isset($reportes) ? (is_countable($reportes) ? count($reportes) : $reportes->count()) : 5 }}</div>
                <span class="text-[11px] text-slate-400">Ciclo Escolar 2026-1</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        <!-- Tarjeta 2: En Proceso -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">En Atención</span>
                <div class="text-2xl font-bold text-amber-700 mt-1">2</div>
                <span class="text-[11px] text-amber-600/80">Cuadrilla en sitio</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Tarjeta 3: Resueltos -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Concluidos</span>
                <div class="text-2xl font-bold text-emerald-700 mt-1">3</div>
                <span class="text-[11px] text-emerald-600/80">Solución verificada</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <!-- Tarjeta 4: Tiempo Promedio de Respuesta -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">SLA Promedio</span>
                <div class="text-2xl font-bold text-slate-900 mt-1">3.4 hrs</div>
                <span class="text-[11px] text-teal-600 font-semibold">Cumplimiento 96.8%</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- TABLA DE HISTORIAL DE REPORTES CON FOLIOS (FOL-2026-XXXX) Y BADGES -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Barra de Herramientas y Filtros -->
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-[#0b2239] text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Bitácora Oficial de Solicitudes</h2>
                    <p class="text-xs text-slate-500">Folios institucionales con trazabilidad en tiempo real.</p>
                </div>
            </div>

            <!-- Filtros rápidos por estado -->
            <div class="flex items-center flex-wrap gap-2 text-xs">
                <button type="button" class="filter-btn active px-3 py-1.5 rounded-lg font-semibold bg-[#1E3A8A] text-white shadow-xs" data-status="todos">
                    Todos (5)
                </button>
                <button type="button" class="filter-btn px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-status="pendiente">
                    Pendientes
                </button>
                <button type="button" class="filter-btn px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-status="en_proceso">
                    En Proceso
                </button>
                <button type="button" class="filter-btn px-3 py-1.5 rounded-lg font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" data-status="resuelta">
                    Concluidos
                </button>
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-[#0b2239] text-white text-[11px] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Folio Institucional</th>
                        <th class="px-4 py-3.5">Ubicación (Edificio / Espacio)</th>
                        <th class="px-4 py-3.5">Categoría Técnica</th>
                        <th class="px-4 py-3.5">Descripción de Falla</th>
                        <th class="px-4 py-3.5">Fecha / Hora</th>
                        <th class="px-4 py-3.5 text-center">Estado Oficial</th>
                        <th class="px-4 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700 bg-white" id="reportes-tbody">
                    @php
                        // Si no viene variable desde el controlador, usamos registros institucionales para exhibir la vista
                        $items = isset($reportes) && (is_countable($reportes) ? count($reportes) : $reportes->count()) > 0 ? $reportes : [
                            (object)[
                                'id_solicitud' => 101,
                                'folio' => 'FOL-2026-0842',
                                'edificio' => 'Edificio 1 - Docencia Norte',
                                'espacio' => 'Aula 204 (2do Nivel)',
                                'categoria' => 'Electricidad e Iluminación',
                                'categoria_slug' => 'electrico',
                                'prioridad' => 'Alta',
                                'descripcion' => 'Cortocircuito en luminaria fluorescente con parpadeo constante y olor a baquelita.',
                                'fecha' => '2026-11-05 08:30',
                                'estado' => 'en_proceso',
                                'estado_label' => 'En Atención Técnica',
                                'cuadrilla' => 'Cuadrilla Electricidad Alfa (Ing. Mateo Juárez)',
                                'evidencia' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80',
                            ],
                            (object)[
                                'id_solicitud' => 102,
                                'folio' => 'FOL-2026-0840',
                                'edificio' => 'Edificio 4 - Laboratorios',
                                'espacio' => 'Sanitarios Planta Baja',
                                'categoria' => 'Plomería y Red Hidráulica',
                                'categoria_slug' => 'plomeria',
                                'prioridad' => 'Crítica',
                                'descripcion' => 'Fuga continua en tubería de alimentación principal con riesgo de inundación.',
                                'fecha' => '2026-11-04 14:15',
                                'estado' => 'en_proceso',
                                'estado_label' => 'Espera de Refacción',
                                'cuadrilla' => 'Cuadrilla Hidráulica Beta (Téc. Ramón Solares)',
                                'evidencia' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?auto=format&fit=crop&w=600&q=80',
                            ],
                            (object)[
                                'id_solicitud' => 103,
                                'folio' => 'FOL-2026-0835',
                                'edificio' => 'Edificio 2 - Posgrado',
                                'espacio' => 'Laboratorio de Cómputo 3',
                                'categoria' => 'Climatización y HVAC',
                                'categoria_slug' => 'clima',
                                'prioridad' => 'Media',
                                'descripcion' => 'Unidad Minisplit no enfría y gotea sobre la canaleta plástica perimetral.',
                                'fecha' => '2026-11-03 11:20',
                                'estado' => 'pendiente',
                                'estado_label' => 'Registrado / En Cola',
                                'cuadrilla' => 'Por Asignar (Coordinación)',
                                'evidencia' => null,
                            ],
                            (object)[
                                'id_solicitud' => 104,
                                'folio' => 'FOL-2026-0821',
                                'edificio' => 'Biblioteca Central',
                                'espacio' => 'Sala de Consulta Silenciosa',
                                'categoria' => 'Cerrajería y Cancelería',
                                'categoria_slug' => 'cerrajeria',
                                'prioridad' => 'Baja',
                                'descripcion' => 'Chapa de puerta principal con pestillo trabado impidiendo el cierre completo.',
                                'fecha' => '2026-11-01 09:40',
                                'estado' => 'resuelta',
                                'estado_label' => 'Concluido y Verificado',
                                'cuadrilla' => 'Mant. General Gamma (Téc. Víctor Ramos)',
                                'evidencia' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80',
                            ],
                            (object)[
                                'id_solicitud' => 105,
                                'folio' => 'FOL-2026-0808',
                                'edificio' => 'Edificio 3 - Talleres',
                                'espacio' => 'Taller de Mecatrónica T-1',
                                'categoria' => 'Mobiliario Escolar',
                                'categoria_slug' => 'mobiliario',
                                'prioridad' => 'Media',
                                'descripcion' => 'Soldadura suelta en mesa de trabajo número 4 con riesgo de desprendimiento.',
                                'fecha' => '2026-10-28 16:05',
                                'estado' => 'resuelta',
                                'estado_label' => 'Concluido y Verificado',
                                'cuadrilla' => 'Mant. General Gamma (Téc. Víctor Ramos)',
                                'evidencia' => null,
                            ]
                        ];
                    @endphp

                    @forelse ($items as $r)
                        @php
                            $folio = $r->folio ?? ('FOL-2026-' . str_pad($r->id_solicitud ?? rand(100, 999), 4, '0', STR_PAD_LEFT));
                            $edificio = $r->espacio->edificio->nombre_edificio ?? ($r->edificio ?? 'Edificio Central');
                            $espacio = $r->espacio->nombre_espacio ?? ($r->espacio ?? 'Aula General');
                            $categoria = $r->categoria->nombre_categoria ?? ($r->categoria ?? 'Mantenimiento General');
                            $descripcion = $r->descripcion_falla ?? ($r->descripcion ?? 'Falla reportada por el usuario solicitante.');
                            $fecha = isset($r->fecha_hallazgo) ? \Carbon\Carbon::parse($r->fecha_hallazgo)->format('Y-m-d H:i') : ($r->fecha ?? date('Y-m-d H:i'));
                            $estado = $r->estado ?? 'pendiente';
                            $cuadrilla = $r->cuadrilla ?? 'Pendiente de Asignación';
                            $evidencia = $r->evidencia_url ?? ($r->evidencia ?? null);
                        @endphp

                        <tr class="hover:bg-slate-50 transition-colors reporte-row" data-status="{{ $estado }}">
                            <!-- Folio -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-[#1E3A8A] bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                        {{ $folio }}
                                    </span>
                                    <button type="button" 
                                            onclick="copiarFolio('{{ $folio }}')" 
                                            title="Copiar Folio"
                                            class="text-slate-400 hover:text-slate-600 p-1 rounded hover:bg-slate-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            <!-- Ubicación -->
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $edificio }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $espacio }}</span>
                                </div>
                            </td>

                            <!-- Categoría Técnica -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#1E3A8A]"></span>
                                    {{ $categoria }}
                                </span>
                            </td>

                            <!-- Descripción de la Falla -->
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="truncate text-slate-700" title="{{ $descripcion }}">{{ $descripcion }}</p>
                                @if ($evidencia)
                                    <span class="inline-flex items-center gap-1 text-[10px] text-teal-700 font-semibold mt-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Foto adjunta
                                    </span>
                                @endif
                            </td>

                            <!-- Fecha -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-slate-500 text-[11px]">
                                {{ $fecha }}
                            </td>

                            <!-- Badges de Estado -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if ($estado === 'en_proceso')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                                        </span>
                                        En Atención
                                    </span>
                                @elseif ($estado === 'pendiente')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        En Espera
                                    </span>
                                @elseif ($estado === 'resuelta' || $estado === 'concluido')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Concluido
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($estado) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <button type="button" 
                                        onclick="abrirDetalleModal({
                                            folio: '{{ $folio }}',
                                            edificio: '{{ addslashes($edificio) }}',
                                            espacio: '{{ addslashes($espacio) }}',
                                            categoria: '{{ addslashes($categoria) }}',
                                            descripcion: '{{ addslashes($descripcion) }}',
                                            fecha: '{{ $fecha }}',
                                            estado: '{{ $estado }}',
                                            cuadrilla: '{{ addslashes($cuadrilla) }}',
                                            evidencia: '{{ $evidencia }}'
                                        })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-[#0b2239] hover:text-white text-slate-700 font-semibold transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Detalles</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-slate-700">No se encontraron solicitudes</h4>
                                <p class="text-xs text-slate-500 mt-1">Usted no cuenta con incidencias registradas en este período.</p>
                                <a href="{{ url('/reportes/crear') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-[#1E3A8A] text-white rounded-xl text-xs font-semibold">
                                    Reportar la primera falla
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación y Resumen -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <span>Mostrando <strong class="text-slate-800">5</strong> reportes institucionales registrados</span>
            <div class="flex items-center gap-1.5">
                <button class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-50" disabled>Anterior</button>
                <span class="px-3 py-1.5 rounded-lg bg-[#0b2239] text-white font-bold">1</span>
                <button class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-50" disabled>Siguiente</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE DETALLES DEL REPORTE -->
<div id="modal-detalle-reporte" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full overflow-hidden transform transition-all">
        
        <!-- Modal Header -->
        <div class="p-5 bg-[#0b2239] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold tracking-tight">Expediente Oficial de Falla</h3>
                    <p class="text-xs text-slate-300" id="modal-folio-subtitle">FOL-2026-XXXX</p>
                </div>
            </div>
            <button type="button" onclick="cerrarDetalleModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-5 text-xs text-slate-700 max-h-[75vh] overflow-y-auto">
            
            <!-- Bloque de Estado y Cuadrilla -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Estado de Atención</span>
                    <span id="modal-estado-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold mt-1 bg-blue-100 text-blue-800">
                        En Atención
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Cuadrilla Asignada</span>
                    <span id="modal-cuadrilla" class="block font-semibold text-slate-900 mt-1">Cuadrilla Electricidad Alfa</span>
                </div>
            </div>

            <!-- Datos de Localización -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Edificio de Campus</span>
                    <span id="modal-edificio" class="font-bold text-slate-900 text-sm mt-0.5 block">Edificio 1</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Aula / Espacio Físico</span>
                    <span id="modal-espacio" class="font-semibold text-slate-800 text-sm mt-0.5 block">Aula 204</span>
                </div>
            </div>

            <!-- Categoría y Fecha -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Especialidad Técnica</span>
                    <span id="modal-categoria" class="font-medium text-slate-800 mt-0.5 block">Electricidad</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase text-slate-400">Fecha y Hora de Hallazgo</span>
                    <span id="modal-fecha" class="font-mono text-slate-600 mt-0.5 block">2026-11-05 08:30</span>
                </div>
            </div>

            <!-- Descripción Completa -->
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Descripción del Incidente</span>
                <div id="modal-descripcion" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 leading-relaxed font-normal">
                    --
                </div>
            </div>

            <!-- Evidencia Fotográfica -->
            <div id="modal-evidencia-container">
                <span class="block text-[10px] font-bold uppercase text-slate-400 mb-2">Evidencia Fotográfica Adjunta</span>
                <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-100 max-h-60 flex items-center justify-center">
                    <img id="modal-evidencia-img" src="" alt="Evidencia de falla" class="w-full h-auto object-cover max-h-60">
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-[11px] text-slate-400">SIMIC • Auditoría y Trazabilidad Escolar</span>
            <button type="button" onclick="cerrarDetalleModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl text-xs transition-colors">
                Cerrar Ventana
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copiarFolio(folio) {
        navigator.clipboard.writeText(folio).then(() => {
            alert('Folio ' + folio + ' copiado al portapapeles.');
        });
    }

    function abrirDetalleModal(data) {
        document.getElementById('modal-folio-subtitle').textContent = data.folio;
        document.getElementById('modal-edificio').textContent = data.edificio;
        document.getElementById('modal-espacio').textContent = data.espacio;
        document.getElementById('modal-categoria').textContent = data.categoria;
        document.getElementById('modal-descripcion').textContent = data.descripcion;
        document.getElementById('modal-fecha').textContent = data.fecha;
        document.getElementById('modal-cuadrilla').textContent = data.cuadrilla;

        const badge = document.getElementById('modal-estado-badge');
        if (data.estado === 'en_proceso') {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold mt-1 bg-blue-100 text-blue-800 border border-blue-200';
            badge.textContent = 'En Atención Técnica';
        } else if (data.estado === 'pendiente') {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold mt-1 bg-amber-100 text-amber-800 border border-amber-200';
            badge.textContent = 'Pendiente / En Espera';
        } else if (data.estado === 'resuelta' || data.estado === 'concluido') {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold mt-1 bg-emerald-100 text-emerald-800 border border-emerald-200';
            badge.textContent = 'Concluido Oficialmente';
        } else {
            badge.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mt-1 bg-slate-100 text-slate-700';
            badge.textContent = data.estado;
        }

        const imgContainer = document.getElementById('modal-evidencia-container');
        const img = document.getElementById('modal-evidencia-img');
        if (data.evidencia && data.evidencia.length > 5) {
            img.src = data.evidencia;
            imgContainer.classList.remove('hidden');
        } else {
            imgContainer.classList.add('hidden');
        }

        document.getElementById('modal-detalle-reporte').classList.remove('hidden');
    }

    function cerrarDetalleModal() {
        document.getElementById('modal-detalle-reporte').classList.add('hidden');
    }

    // Filtros rápidos
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-[#1E3A8A]', 'text-white', 'shadow-xs');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            this.classList.remove('bg-slate-100', 'text-slate-700');
            this.classList.add('bg-[#1E3A8A]', 'text-white', 'shadow-xs');

            const status = this.getAttribute('data-status');
            const rows = document.querySelectorAll('.reporte-row');

            rows.forEach(row => {
                if (status === 'todos') {
                    row.style.display = '';
                } else if (status === 'resuelta') {
                    const rowStatus = row.getAttribute('data-status');
                    row.style.display = (rowStatus === 'resuelta' || rowStatus === 'concluido') ? '' : 'none';
                } else {
                    row.style.display = (row.getAttribute('data-status') === status) ? '' : 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
