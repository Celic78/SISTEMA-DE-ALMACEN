@extends('layouts.app')

@section('title', 'Levantamiento de Incidencias de Infraestructura')

@section('header_overline', 'PORTAL INSTITUCIONAL DEL SOLICITANTE • CAMPUS DIGITAL')
@section('header_title', 'Reportar Falla o Requerimiento Técnico')
@section('header_subtitle', 'Registro y seguimiento oficial de incidencias en edificios, aulas, talleres y laboratorios del plantel.')

@section('header_actions')
<a href="{{ url('/reportes/mis-reportes') }}" 
   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors border border-slate-200">
    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <span>Ver Mis Reportes Anteriores</span>
</a>
@endsection

@section('content')
<div class="space-y-6">

    @include('partials.alerts')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- FORMULARIO DE LEVANTAMIENTO DE FALLAS -->
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0b2239] text-teal-400 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Ficha Técnica de Incidencia</h2>
                        <p class="text-xs text-slate-500">Capture la información de la falla para generar el folio oficial.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Recepción 24/7
                </span>
            </div>

            <form method="POST" action="{{ url('/reportes') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- SECCIÓN 1: UBICACIÓN JERÁRQUICA (EDIFICIO Y AULA/ESPACIO DINÁMICO) -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">1</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ubicación Jerárquica del Incidente</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Desplegable 1: Edificio -->
                        <div>
                            <label for="id_edificio" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Edificio de Campus <span class="text-red-500">*</span>
                            </label>
                            <select id="id_edificio" name="id_edificio" required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('id_edificio') border-red-500 @enderror">
                                <option value="" disabled selected>Seleccione el edificio...</option>
                                @foreach ($edificios ?? [] as $edificio)
                                    <option value="{{ $edificio->id_edificio }}" {{ old('id_edificio') == $edificio->id_edificio ? 'selected' : '' }}>
                                        {{ $edificio->nombre }} ({{ $edificio->descripcion_espacios ?? 'Campus' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_edificio')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Desplegable 2: Aula / Espacio Dinámico -->
                        <div>
                            <label for="id_espacio" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Aula, Laboratorio o Espacio <span class="text-red-500">*</span>
                            </label>
                            <select id="id_espacio" name="id_espacio" required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('id_espacio') border-red-500 @enderror">
                                <option value="" disabled selected>Primero elija un edificio...</option>
                            </select>
                            @error('id_espacio')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-[11px] text-slate-400">Filtrado automático por protocolo LBS.</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: CATEGORÍA TÉCNICA Y PRIORIDAD -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">2</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Clasificación Técnica y Criticidad</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Categorías Técnicas -->
                        <div>
                            <label for="id_categoria" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Especialidad Requerida <span class="text-red-500">*</span>
                            </label>
                            <select id="id_categoria" name="id_categoria" required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('id_categoria') border-red-500 @enderror">
                                <option value="" disabled selected>Seleccione la categoría técnica...</option>
                                @foreach ($categorias ?? [] as $categoria)
                                    <option value="{{ $categoria->id_categoria }}" {{ old('id_categoria') == $categoria->id_categoria ? 'selected' : '' }}>
                                        {{ $categoria->nombre_categoria }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_categoria')
                                <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prioridad / SLA Estimado -->
                        <div>
                            <label for="id_prioridad" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Nivel de Criticidad Estimado
                            </label>
                            <select id="id_prioridad" name="id_prioridad"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs">
                                <option value="">Automático por Coordinación (SLA Estándar)</option>
                                @foreach ($prioridades ?? [] as $prioridad)
                                    <option value="{{ $prioridad->id_prioridad }}" {{ old('id_prioridad') == $prioridad->id_prioridad ? 'selected' : '' }}>
                                        {{ $prioridad->nivel }} (Resolución max: {{ $prioridad->tiempo_respuesta_hrs }} hrs)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: DESCRIPCIÓN DETALLADA Y FECHA -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">3</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Detalles de la Incidencia</h3>
                    </div>

                    <div>
                        <label for="descripcion_falla" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Descripción de la Falla <span class="text-red-500">*</span>
                        </label>
                        <textarea id="descripcion_falla" name="descripcion_falla" rows="4" required
                                  placeholder="Ejemplo: Fuga continua de agua en lavabo 2 del sanitario de planta baja. Provoca encharcamiento cerca del cuadro eléctrico."
                                  class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('descripcion_falla') border-red-500 @enderror">{{ old('descripcion_falla') }}</textarea>
                        @error('descripcion_falla')
                            <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-[11px] text-slate-400">Detalle si compromete la seguridad de los alumnos o la continuidad de clases.</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_hallazgo" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Momento en que se detectó la falla
                        </label>
                        <input type="datetime-local" id="fecha_hallazgo" name="fecha_hallazgo" 
                               value="{{ old('fecha_hallazgo', now()->format('Y-m-d\TH:i')) }}"
                               class="w-full sm:w-72 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none shadow-xs">
                    </div>
                </div>

                <!-- SECCIÓN 4: INPUT FILE PARA EVIDENCIA FOTOGRÁFICA -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-1 border-b border-slate-100">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">4</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Evidencia Fotográfica o Documental</h3>
                    </div>

                    <div class="border-2 border-dashed border-slate-300 hover:border-sky-500 rounded-2xl p-6 text-center bg-slate-50/50 transition-colors">
                        <div class="mx-auto w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <label for="evidencia" class="cursor-pointer">
                            <span class="text-xs font-bold text-sky-700 hover:text-sky-800">Seleccionar imagen o documento</span>
                            <span class="text-xs text-slate-500"> o arrastre el archivo aquí</span>
                            <input type="file" id="evidencia" name="evidencia" accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only">
                        </label>
                        <p id="file-name-preview" class="text-[11px] text-slate-400 mt-2 font-mono">Formatos admitidos: JPG, PNG, WEBP, PDF (Hasta 10 MB)</p>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ url('/') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Generar Folio y Notificar a Cuadrilla</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- PANEL LATERAL: INFORMACIÓN Y ACCESO A MIS REPORTES -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Tarjeta Informativa de SLA y Proceso -->
            <div class="bg-gradient-to-br from-blue-900 to-[#0b2239] text-white rounded-2xl p-6 shadow-md space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-teal-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300">Protocolo Anti-Duplicados</h3>
                        <p class="text-sm font-semibold text-white">Consolidación Automática LBS</p>
                    </div>
                </div>
                <p class="text-xs text-blue-100 leading-relaxed">
                    Si otros docentes o alumnos ya reportaron una falla idéntica en este mismo espacio, su reporte se agrupará en el folio maestro correspondiente para evitar duplicidad de cuadrillas en campo.
                </p>
            </div>

            <!-- Resumen de Últimos Reportes con enlace a Historial Completo -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Mis Reportes Recientes</h3>
                        <p class="text-[11px] text-slate-400">Seguimiento rápido de folios emitidos</p>
                    </div>
                    <a href="{{ url('/reportes/mis-reportes') }}" class="text-[11px] font-bold text-sky-700 hover:text-sky-900">
                        Ver todos →
                    </a>
                </div>

                <div class="divide-y divide-slate-100 overflow-y-auto max-h-96 text-xs">
                    @forelse ($solicitudes ?? [] as $sol)
                        <div class="p-4 hover:bg-slate-50 transition-colors space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-900 text-xs">
                                    FOL-2026-{{ str_pad($sol->id_solicitud, 4, '0', STR_PAD_LEFT) }}
                                </span>
                                @php
                                    $estado = mb_strtolower($sol->estado);
                                    $badgeColor = match($estado) {
                                        'resuelta', 'concluida' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'en_proceso', 'asignada' => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'requiere_aprobacion' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                                    {{ $sol->estado }}
                                </span>
                            </div>
                            <p class="text-slate-600 font-medium line-clamp-2">{{ $sol->descripcion_falla }}</p>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ $sol->espacio?->nombre_espacio ?? 'Espacio general' }}</span>
                                <span>{{ $sol->created_at?->diffForHumans() ?? 'Hoy' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 text-xs">
                            No cuenta con reportes registrados aún.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT PARA DESPLEGABLE DINÁMICO DE EDIFICIOS Y ESPACIOS -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const edificiosData = @json($edificios ?? []);
        const edificioSelect = document.getElementById('id_edificio');
        const espacioSelect = document.getElementById('id_espacio');
        const evidenciaInput = document.getElementById('evidencia');
        const previewText = document.getElementById('file-name-preview');

        function actualizarEspacios() {
            if (!edificioSelect || !espacioSelect) return;
            const idEdificioSeleccionado = parseInt(edificioSelect.value, 10);
            espacioSelect.innerHTML = '';

            if (isNaN(idEdificioSeleccionado)) {
                espacioSelect.innerHTML = '<option value="" disabled selected>Primero elija un edificio...</option>';
                return;
            }

            const edificio = edificiosData.find(e => e.id_edificio === idEdificioSeleccionado);

            if (!edificio || !edificio.espacios || edificio.espacios.length === 0) {
                espacioSelect.innerHTML = '<option value="" disabled selected>No hay espacios registrados en este edificio</option>';
                return;
            }

            espacioSelect.innerHTML = '<option value="" disabled selected>Seleccione el aula o espacio...</option>';
            edificio.espacios.forEach(espacio => {
                const opt = document.createElement('option');
                opt.value = espacio.id_espacio;
                opt.textContent = `${espacio.nombre_espacio} (${espacio.tipo_espacio} - Piso ${espacio.piso})`;
                espacioSelect.appendChild(opt);
            });
        }

        if (edificioSelect) {
            edificioSelect.addEventListener('change', actualizarEspacios);

            @if (old('id_edificio'))
                actualizarEspacios();
                @if (old('id_espacio'))
                    espacioSelect.value = "{{ old('id_espacio') }}";
                @endif
            @endif
        }

        if (evidenciaInput && previewText) {
            evidenciaInput.addEventListener('change', () => {
                if (evidenciaInput.files && evidenciaInput.files[0]) {
                    previewText.textContent = `Archivo seleccionado: ${evidenciaInput.files[0].name} (${(evidenciaInput.files[0].size / 1024 / 1024).toFixed(2)} MB)`;
                    previewText.classList.remove('text-slate-400');
                    previewText.classList.add('text-teal-600', 'font-bold');
                }
            });
        }
    });
</script>
@endsection
