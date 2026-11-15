<?php

namespace App\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Exceptions\DuplicateSolicitudException;
use App\Http\Controllers\Controller;
use App\Models\BitacoraSistema;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\MatrizPrioridad;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\EvidenciaService;
use App\Services\SolicitudService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function __construct(
        protected ?SolicitudService $solicitudService = null,
        protected ?EvidenciaService $evidenciaService = null
    ) {
        $this->solicitudService ??= app(SolicitudService::class);
        $this->evidenciaService ??= app(EvidenciaService::class);
    }

    /**
     * Lista el historial de reportes levantados por el usuario o de su área institucional.
     */
    public function index(Request $request): View|JsonResponse
    {
        $userId = Auth::id() ?? 1;

        $solicitudes = Solicitud::with(['espacio.edificio', 'categoria', 'prioridad', 'ordenTrabajo'])
            ->where(function ($q) use ($userId) {
                if (Auth::check() && ! Auth::user()->isAdmin() && ! Auth::user()->isCoordinador()) {
                    $q->where('id_usuario', $userId);
                }
            })
            ->latest('id_solicitud')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $solicitudes,
            ]);
        }

        $edificios = Edificio::with('espacios')->get();
        $categorias = CategoriaSolicitud::with('subcategorias')->get();
        $prioridades = MatrizPrioridad::orderBy('tiempo_respuesta_hrs')->get();

        return view('reportes.crear', compact('solicitudes', 'edificios', 'categorias', 'prioridades'));
    }

    /**
     * Muestra la interfaz de captura para el levantamiento de una nueva falla en campus.
     */
    public function crear(): View
    {
        $edificios = Edificio::with('espacios')->get();
        $categorias = CategoriaSolicitud::with('subcategorias')->get();
        $prioridades = MatrizPrioridad::orderBy('tiempo_respuesta_hrs')->get();

        $solicitudes = Solicitud::with(['espacio.edificio', 'categoria', 'prioridad'])
            ->when(Auth::check(), fn ($q) => $q->where('id_usuario', Auth::id()))
            ->latest('id_solicitud')
            ->limit(10)
            ->get();

        return view('reportes.crear', compact('edificios', 'categorias', 'prioridades', 'solicitudes'));
    }

    /**
     * Almacena una nueva solicitud de mantenimiento con evidencia fotográfica y generación de folio único.
     */
    public function guardar(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'id_edificio' => ['required', 'integer', 'exists:edificios,id_edificio'],
            'id_espacio' => ['required', 'integer', 'exists:espacios,id_espacio'],
            'id_categoria' => ['required', 'integer', 'exists:categorias_solicitud,id_categoria'],
            'id_prioridad' => ['nullable', 'integer', 'exists:matrices_prioridad,id_prioridad'],
            'descripcion_falla' => ['required', 'string', 'min:10', 'max:2000'],
            'fecha_hallazgo' => ['nullable', 'date'],
            'evidencia' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:10240'],
        ], [
            'id_edificio.required' => 'Debe seleccionar el edificio donde se ubica la incidencia.',
            'id_espacio.required' => 'Debe especificar el aula o espacio específico.',
            'id_categoria.required' => 'Seleccione la categoría técnica de la falla.',
            'descripcion_falla.required' => 'Describa detalladamente el problema detectado.',
            'descripcion_falla.min' => 'La descripción debe tener al menos 10 caracteres.',
            'evidencia.max' => 'El archivo de evidencia no debe superar 10 MB.',
        ]);

        /** @var User $usuario */
        $usuario = Auth::user() ?? User::first() ?? User::factory()->create();

        // Subida y procesamiento de evidencia fotográfica o documental
        $evidenciaUrl = null;
        if ($request->hasFile('evidencia') && $request->file('evidencia')->isValid()) {
            $evidenciaUrl = $this->evidenciaService->almacenarEvidenciaSolicitud($request->file('evidencia'));
        }

        try {
            $solicitud = $this->solicitudService->crearSolicitud([
                'id_edificio' => (int) $validated['id_edificio'],
                'id_espacio' => (int) $validated['id_espacio'],
                'id_categoria' => (int) $validated['id_categoria'],
                'id_prioridad' => isset($validated['id_prioridad']) ? (int) $validated['id_prioridad'] : null,
                'descripcion_falla' => $validated['descripcion_falla'],
                'fecha_hallazgo' => $validated['fecha_hallazgo'] ?? now(),
                'evidencia_url' => $evidenciaUrl,
            ], $usuario);

            // Generación de folio institucional estandarizado FOL-2026-XXXX
            $folio = 'FOL-'.date('Y').'-'.str_pad((string) $solicitud->id_solicitud, 4, '0', STR_PAD_LEFT);

            BitacoraSistema::create([
                'id_usuario' => $usuario->id,
                'accion' => 'SOLICITUD_CREADA',
                'descripcion' => json_encode([
                    'id_solicitud' => $solicitud->id_solicitud,
                    'folio' => $folio,
                    'espacio' => $solicitud->espacio?->nombre_espacio,
                    'categoria' => $solicitud->categoria?->nombre_categoria,
                    'evidencia_url' => $evidenciaUrl,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            $mensajeExito = "Reporte registrado exitosamente con el Folio Oficial {$folio}. Nuestro equipo técnico evaluará la prioridad de atención.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $mensajeExito,
                    'folio' => $folio,
                    'solicitud' => $solicitud->load(['espacio.edificio', 'categoria', 'prioridad']),
                ], 201);
            }

            return redirect()
                ->route('reportes.create')
                ->with('status', $mensajeExito)
                ->with('folio_generado', $folio);

        } catch (DuplicateSolicitudException|BusinessRuleException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
