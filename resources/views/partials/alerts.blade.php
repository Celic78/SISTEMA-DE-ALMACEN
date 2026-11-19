{{-- Componente Reutilizable de Alertas Institucionales SIMIC --}}
@if (session('status'))
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-emerald-900">Operación Exitosa</h4>
                <p class="mt-0.5 text-emerald-700 font-medium">{{ session('status') }}</p>
            </div>
        </div>
        @if (session('folio_generado'))
            <span class="px-3 py-1 bg-emerald-200 text-emerald-900 font-mono font-bold rounded-lg text-xs shadow-xs">
                {{ session('folio_generado') }}
            </span>
        @endif
    </div>
@endif

@if (session('error'))
    <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-red-800 text-xs flex items-start gap-3 shadow-xs">
        <div class="w-8 h-8 rounded-lg bg-red-100 text-red-700 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-bold text-red-900">Inconsistencia o Conflicto Detectado</h4>
            <p class="mt-0.5 text-red-700 font-medium leading-relaxed">{{ session('error') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-amber-900 text-xs shadow-xs space-y-1.5">
        <div class="flex items-center gap-2 font-bold text-amber-800">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Por favor verifique los siguientes campos requeridos:</span>
        </div>
        <ul class="list-disc list-inside text-amber-700 pl-2 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
