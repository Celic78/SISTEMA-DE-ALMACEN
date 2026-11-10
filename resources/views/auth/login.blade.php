@extends('layouts.public')

@section('title', 'Acceso al Sistema')
@section('standalone_auth', 'true')
@section('container_width', 'max-w-md')

@section('content')
<div class="bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden">

    <!-- Encabezado de la Tarjeta de Acceso -->
    <div class="p-6 sm:p-7 bg-white border-b-0">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-[#0b4a5b] flex items-center justify-center text-teal-200 shadow-sm shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">Universidad Metropolitana</h2>
                <p class="text-[11px] text-slate-500 font-medium">Portal de Mantenimiento y Recursos Materiales</p>
            </div>
        </div>

        <!-- Selector de Modo / Tabs de Acceso -->
        <div class="mt-4 grid grid-cols-2 gap-1 p-1 bg-sky-100 rounded-sm">
            <button type="button" class="py-2 text-xs font-bold text-white bg-[#003b4b] rounded-sm shadow-xs transition-all">
                Acceso al Sistema
            </button>
            <a href="{{ url('/') }}" class="text-center py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-transparent hover:bg-white/60 rounded-sm transition-all">
                Registro de Nuevo Solicitante
            </a>
        </div>
    </div>

    <div class="px-6 pb-6 sm:px-7 sm:pb-7 space-y-5">
        <!-- Bloque de Alertas Dinámicas por Sesión -->
        @include('partials.alerts')

        <!-- Formulario de Inicio de Sesión -->
        <form method="POST" action="{{ url('/login') }}" class="space-y-5">
            @csrf

            <!-- Campo 1: Selección Obligatoria de Rol Institucional -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="rol" class="text-xs font-bold text-slate-800">
                        Rol Institucional Obligatorio <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">SEC-POL-04</span>
                </div>
                <div class="relative">
                    <select id="rol" name="rol" required
                            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('rol') border-red-500 @enderror">
                        <option value="" disabled {{ old('rol') ? '' : 'selected' }}>Seleccione la atribución operativa</option>
                        <option value="coordinador" {{ old('rol') === 'coordinador' ? 'selected' : '' }}>Superusuario / Coordinador General</option>
                        <option value="tecnico" {{ old('rol') === 'tecnico' ? 'selected' : '' }}>Cuadrilla Técnica Especializada</option>
                        <option value="almacen" {{ old('rol') === 'almacen' ? 'selected' : '' }}>Encargado de Almacén y Suministros</option>
                        <option value="profesor" {{ old('rol') === 'profesor' ? 'selected' : '' }}>Docente / Profesor de Campus</option>
                        <option value="alumno" {{ old('rol') === 'alumno' ? 'selected' : '' }}>Alumno / Estudiante Solicitante</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                @error('rol')
                    <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                @else
                    <p class="mt-1 text-[11px] text-slate-400">Seleccione el rol para validar el sufijo del dominio admitido.</p>
                @enderror
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
                       value="{{ old('email') }}"
                       placeholder="usuario@alumnos.edu.mx"
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                @else
                    <p class="mt-1 text-[11px] text-slate-400">
                        Formatos válidos: <span class="font-mono text-slate-500">@alumnos.edu.mx, @profesores.edu.mx, @admin.edu.mx, @tecnicos.edu.mx, @almacen.edu.mx</span>
                    </p>
                @enderror
            </div>

            <!-- Campo 3: Contraseña -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="text-xs font-bold text-slate-800">
                        Contraseña Institucional <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Protección BCrypt</span>
                </div>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           placeholder="Ingrese su contraseña institucional"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-600 focus:ring-1 focus:ring-sky-600 focus:outline-none transition-shadow shadow-xs @error('password') border-red-500 @enderror">
                </div>
                @error('password')
                    <p class="mt-1 text-[11px] text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Opciones Adicionales: Recordar Credenciales -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-600">
                    <span>Mantener sesión activa</span>
                </label>
                <a href="mailto:soporte@universidad.edu.mx" class="text-sky-700 hover:text-sky-900 font-semibold transition-colors">
                    ¿Problemas con sus accesos?
                </a>
            </div>

            <!-- Botón de Inicio de Sesión -->
            <button type="submit" 
                    class="w-full mt-2 py-3 px-4 bg-[#0b2239] hover:bg-[#071626] text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 flex items-center justify-center gap-2 group ring-2 ring-transparent focus:ring-sky-600">
                <span>Ingresar al Portal Institucional</span>
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
                            <td class="px-3.5 py-2 font-mono text-slate-500 text-right">@admin.edu.mx</td>
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
