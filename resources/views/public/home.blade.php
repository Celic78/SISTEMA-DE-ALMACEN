@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col justify-between">
    <!-- Hero -->
    <header class="bg-[#1E3A8A] text-white py-12 px-6 shadow-md">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-3">
                <span class="bg-blue-800 text-blue-200 text-xs font-semibold px-3 py-1 rounded-full uppercase">Plataforma Institucional</span>
                <h1 class="text-3xl font-bold">Gestión de Recursos Materiales y Mantenimiento</h1>
                <p class="text-blue-100 text-sm">Portal para el reporte y seguimiento transparente de incidencias en el campus.</p>
                <div class="pt-2">
                    <a href="{{ route('login') }}" class="bg-white text-[#1E3A8A] hover:bg-blue-50 font-bold px-5 py-2.5 rounded-lg shadow inline-flex items-center gap-2 text-sm">
                        Iniciar Sesión / Acceso al Portal
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Transparencia -->
    <main class="max-w-6xl mx-auto px-6 py-8 w-full flex-grow space-y-6">
        <h2 class="text-xl font-bold text-slate-800">Transparencia Institucional</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-2xl font-bold text-emerald-600">{{ $metricas['concluidas'] ?? '100%' }}</span>
                <p class="text-slate-500 text-xs mt-1">Atenciones Concluidas</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-2xl font-bold text-amber-600">{{ $metricas['en_proceso'] ?? '24 hrs' }}</span>
                <p class="text-slate-500 text-xs mt-1">SLA Promedio</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-2xl font-bold text-[#1E3A8A]">{{ $metricas['edificios_atendidos'] ?? '12' }}</span>
                <p class="text-slate-500 text-xs mt-1">Edificios Monitoreados</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm text-sm text-slate-600">
            <strong>Avisos de Mantenimiento:</strong> Inicia sesión con tu correo institucional para reportar o dar seguimiento a fallas de infraestructura.
        </div>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-4 text-center text-xs">
        <p>&copy; 2026 Sistema de Gestión de Recursos Materiales y Mantenimiento Escolar.</p>
    </footer>
</div>
@endsection