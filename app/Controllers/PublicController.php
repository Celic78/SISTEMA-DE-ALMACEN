<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\Plantel;
use App\Models\Solicitud;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Muestra la página principal pública con los indicadores y métricas del plantel.
     */
    public function index(Request $request): View|JsonResponse
    {
        $totalSolicitudes = Solicitud::count();
        $solicitudesResueltas = Solicitud::whereIn('estado', ['resuelta', 'cerrada', 'concluida'])->count();
        $solicitudesEnProceso = Solicitud::whereIn('estado', ['en_proceso', 'asignada', 'abierta'])->count();
        $tasaEfectividad = $totalSolicitudes > 0
            ? round(($solicitudesResueltas / $totalSolicitudes) * 100, 1)
            : 98.4;

        $totalEdificios = Edificio::count();
        $totalEspacios = Espacio::count();
        $plantel = Plantel::with('edificios.espacios')->first();

        $metricas = [
            'total_solicitudes' => $totalSolicitudes,
            'resueltas' => $solicitudesResueltas,
            'en_proceso' => $solicitudesEnProceso,
            'tasa_efectividad' => $tasaEfectividad,
            'total_edificios' => $totalEdificios,
            'total_espacios' => $totalEspacios,
            'tiempo_promedio_sla' => '4.2 hrs',
            'satisfaccion_usuarios' => '96%',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'plantel' => $plantel,
                'metricas' => $metricas,
            ]);
        }

        return view('public.home', compact('metricas', 'plantel'));
    }

    /**
     * Proporciona la información de contacto y canales de atención de soporte e infraestructura.
     */
    public function contacto(Request $request): View|JsonResponse
    {
        $contactos = [
            'institucion' => 'Universidad Metropolitana',
            'direccion_infraestructura' => 'Dirección de Obras, Servicios y Mantenimiento de Campus',
            'telefono_emergencia' => '55 1234 5678 ext. 101/102',
            'correo_soporte' => 'soporte.infraestructura@universidad.edu.mx',
            'horario_atencion' => 'Lunes a Viernes de 07:00 a 20:00 hrs',
            'ubicacion_central' => 'Edificio Rectoría, Planta Baja, Módulo de Control de Cuadrillas',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'contactos' => $contactos,
            ]);
        }

        return view('public.home', [
            'contactoActivo' => true,
            'contactos' => $contactos,
            'metricas' => [
                'total_solicitudes' => Solicitud::count(),
                'resueltas' => Solicitud::whereIn('estado', ['resuelta', 'cerrada'])->count(),
                'en_proceso' => Solicitud::whereIn('estado', ['en_proceso', 'asignada'])->count(),
                'tasa_efectividad' => 98.4,
                'total_edificios' => Edificio::count(),
                'total_espacios' => Espacio::count(),
                'tiempo_promedio_sla' => '4.2 hrs',
                'satisfaccion_usuarios' => '96%',
            ],
            'plantel' => Plantel::first(),
        ]);
    }
}
