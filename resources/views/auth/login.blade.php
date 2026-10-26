@extends('layouts.public')

@section('title', 'Acceso al Sistema')

@section('content')
<div class="bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden">

    <!-- Encabezado de la Tarjeta de Acceso -->
    <div class="p-6 sm:p-8 bg-slate-50/60 border-b border-slate-200">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[#0b2239] flex items-center justify-center text-teal-400 shadow-sm shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Universidad Metropolitana</h2>
                <p class="text-xs text-slate-500 font-medium">Portal de Mantenimiento y Recursos Materiales</p>
            </div>
        </div>

        <!-- Selector de Modo / Tabs de Acceso -->
        <div class="mt-6 grid grid-cols-2 gap-2 p-1 bg-slate-200/60 rounded-xl">
            <button type="button" class="py-2.5 text-xs font-bold text-white bg-[#0b2239] rounded-lg shadow-xs transition-all">
                Acceso al Sistema
            </button>
            <button type="button" class="py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-transparent hover:bg-white/60 rounded-lg transition-all">
                Registro de Nuevo Solicitante
            </button>
        </div>
    </div>

    <div class="p-6 sm:p-8 space-y-6">
        <!-- Banner de Conflicto / Alerta de Credenciales -->
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-xs">
            <div class="flex items-start gap-3">
                <div class="p-1 rounded-full bg-red-100 text-red-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-red-800">Conflicto de Credenciales Institucionales</h3>
                    <p class="mt-0.5 text-red-600 leading-relaxed">
                        Error: los datos ingresados no coinciden. El rol seleccionado no corresponde a la categoría de la cuenta institucional proporcionada.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario de Inicio de Sesión -->
        <form method="POST" action="{{ route('login', [], false) ?: '#' }}" class="space-y-5">
            @csrf

            <!-- Campo 1: Rol Institucional -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="role" class="text-xs font-bold text-slate-800">
                        Rol Institucional Obligatorio <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">SEC-POL-04</span>
                </div>
                <div class="relative">
                    <select id="role" name="role" required
                            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs">
                        <option value="" disabled selected>Seleccione la atribución operativa</option>
                        <option value="coordinador">Superusuario / Coordinador General</option>
                        <option value="tecnico">Cuadrilla Técnica Especializada</option>
                        <option value="almacen">Encargado de Almacén y Suministros</option>
                        <option value="solicitante">Solicitante (Docente / Administrativo / Alumno)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Seleccione el rol para validar el sufijo del dominio admitido.</p>
            </div>

            <!-- Campo 2: Correo Institucional -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="email" class="text-xs font-bold text-slate-800">
                        Correo Electrónico Institucional <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400 font-mono">dominio@universidad.edu.mx</span>
                </div>
                <input type="email" id="email" name="email" required
                       placeholder="usuario@alumnos.edu.mx"
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs">
                <p class="mt-1 text-[11px] text-slate-400">
                    Formatos válidos: <span class="font-mono text-slate-500">@alumnos.edu.mx, @profesores.edu.mx, @coordinacion.edu.mx, @tecnicos.edu.mx, @almacen.edu.mx</span>
                </p>
            </div>

            <!-- Campo 3: Contraseña -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="text-xs font-bold text-slate-800">
                        Contraseña Institucional <span class="text-red-500">*</span>
                    </label>
                    <a href="#" class="text-[11px] font-semibold text-sky-700 hover:text-sky-900 transition-colors">
                        ¿Olvidó su contraseña institucional?
                    </a>
                </div>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           value="••••••••••••"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs">
                    <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botón de Inicio de Sesión -->
            <button type="submit" 
                    class="w-full mt-2 py-3 px-4 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 flex items-center justify-center gap-2 group ring-2 ring-transparent focus:ring-sky-600">
                <span>Iniciar Sesión en el Portal</span>
                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <!-- Directorio y Tabla de Soporte en Tarjeta -->
        <div class="mt-8 pt-6 border-t border-slate-200">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-700 mb-3">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Estructura de Dominios y Directorio de Soporte</span>
            </div>

            <div class="overflow-hidden border border-slate-200 rounded-xl bg-slate-50/50 text-[11px]">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <tbody class="divide-y divide-slate-200 text-slate-600">
                        <tr>
                            <td class="px-3.5 py-2 font-medium text-slate-700">Alumnos</td>
                            <td class="px-3.5 py-2 font-mono text-slate-500 text-right">@alumnos.edu.mx</td>
                            <td class="px-3.5 py-2 font-medium text-slate-700 border-l border-slate-200">Docentes</td>
                            <td class="px-3.5 py-2 font-mono text-slate-500 text-right">@profesores.edu.mx</td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2 font-medium text-slate-700">Coordinación</td>
                            <td class="px-3.5 py-2 font-mono text-slate-500 text-right">@coordinacion.edu.mx</td>
                            <td class="px-3.5 py-2 font-medium text-slate-700 border-l border-slate-200">Técnicos</td>
                            <td class="px-3.5 py-2 font-mono text-slate-500 text-right">@tecnicos.edu.mx</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="px-3.5 py-2 font-medium text-slate-700">Almacén de Materiales</td>
                            <td colspan="2" class="px-3.5 py-2 font-mono text-slate-500 text-right">@almacen.edu.mx</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Datos de Contacto Directo -->
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Mesa de Ayuda TI: Ext. 4300 / 4301
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Servicios Generales Edificio C-Piso 1
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
