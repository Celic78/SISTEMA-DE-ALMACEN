<?php

namespace App\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\CategoriaSolicitud;
use App\Models\Espacio;
use App\Models\Plantel;
use App\Services\SolicitudService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    private const DIAS_DE_ANTIGUEDAD = 7;

    private const HORA_MINIMA = '07:00';

    private const HORA_MAXIMA = '19:00';

    public function __construct(protected SolicitudService $solicitudService) {}

    public function index(Request $request): View
    {
        $solicitudes = $request->user()->solicitudes()
            ->with(['espacio.edificio', 'categoria', 'ordenTrabajo.evidenciasCierre', 'solicitudPadre.ordenTrabajo.evidenciasCierre'])
            ->latest('id_solicitud')
            ->paginate(15);

        return view('reportes.mis-reportes', compact('solicitudes'));
    }

    public function crear(): View
    {
        $plantel = Plantel::where('nombre', Plantel::PRINCIPAL)->first();
        $edificios = $plantel?->edificios()->with('espacios')->orderBy('nombre')->get() ?? collect();

        $momentoDelReporte = now('America/Mexico_City');

        return view('reportes.crear', [
            'momentoDelReporte' => $momentoDelReporte,
            'fechaMinima' => $momentoDelReporte->copy()->subDays(self::DIAS_DE_ANTIGUEDAD),
            'edificios' => $edificios,
            'horaMinima' => self::HORA_MINIMA,
            'horaMaxima' => self::HORA_MAXIMA,
            'horaPredeterminada' => min(self::HORA_MAXIMA, max(self::HORA_MINIMA, $momentoDelReporte->format('H:i'))),
        ]);
    }

    public function bienes(Espacio $espacio): JsonResponse
    {
        $espacio->loadMissing('edificio.plantel');
        abort_unless($espacio->edificio?->plantel?->nombre === Plantel::PRINCIPAL, 404);

        return response()->json($espacio->bienes()->where('estado', '!=', 'baja')
            ->orderBy('nombre')->orderBy('codigo_inventario')
            ->get(['id', 'nombre', 'codigo_inventario', 'ubicacion_interna']));
    }

    public function guardar(Request $request): RedirectResponse
    {
        if ($request->has('hora_hallazgo')) {
            $fecha = $request->validate([
                'fecha_hallazgo' => ['required', 'date_format:Y-m-d'],
                'hora_hallazgo' => ['required', 'date_format:H:i'],
            ]);
            $request->merge(['fecha_hallazgo' => $fecha['fecha_hallazgo'].'T'.$fecha['hora_hallazgo']]);
        }
        $datos = $request->validate([
            'id_edificio' => ['required', 'integer'],
            'id_espacio' => ['required', 'integer'],
            'bien' => ['sometimes', 'required', 'string', 'max:120'],
            'id_bien_espacio' => ['exclude_if:bien,__otro__', 'nullable', 'required_with:bien', 'integer'],
            'nombre_bien' => ['exclude_unless:bien,__otro__', 'required', 'string', 'max:120'],
            'numero_bien' => ['exclude_unless:bien,__otro__', 'nullable', 'string', 'max:60'],
            'descripcion_falla' => ['required', 'string', 'min:10', 'max:2000'],
            'fecha_hallazgo' => ['required', 'date_format:Y-m-d\TH:i'],
            'evidencia' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $hoy = now('America/Mexico_City');
        $dia = substr($datos['fecha_hallazgo'], 0, 10);
        if ($dia < $hoy->copy()->subDays(self::DIAS_DE_ANTIGUEDAD)->toDateString() || $dia > $hoy->toDateString()) {
            throw ValidationException::withMessages([
                'fecha_hallazgo' => 'Selecciona hoy o uno de los siete días anteriores.',
            ]);
        }

        $hora = substr($datos['fecha_hallazgo'], 11, 5);
        if ($hora < self::HORA_MINIMA || $hora > self::HORA_MAXIMA) {
            throw ValidationException::withMessages([
                'hora_hallazgo' => 'La hora del hallazgo debe estar entre las 07:00 y las 19:00.',
            ]);
        }

        $espacio = Espacio::with('edificio')->where('id_edificio', $datos['id_edificio'])
            ->whereHas('edificio.plantel', fn ($query) => $query->where('nombre', Plantel::PRINCIPAL))
            ->find($datos['id_espacio']);

        if (! $espacio) {
            throw ValidationException::withMessages(['id_espacio' => 'Selecciona un espacio que pertenezca al edificio elegido.']);
        }

        if (isset($datos['id_bien_espacio'])) {
            $bien = $espacio->bienes()->where('estado', '!=', 'baja')->find($datos['id_bien_espacio']);
            if (! $bien || (isset($datos['bien']) && $bien->nombre !== $datos['bien'])) {
                throw ValidationException::withMessages(['id_bien_espacio' => 'Selecciona un bien activo del espacio elegido.']);
            }
            $datos['bien_reportado'] = $bien->nombre.' · '.$bien->codigo_inventario;
        } elseif (isset($datos['nombre_bien'])) {
            $datos['bien_reportado'] = $datos['nombre_bien'].(isset($datos['numero_bien']) ? ' · '.$datos['numero_bien'] : '');
        }

        $datos['ubicacion_libre'] = $espacio->edificio->nombre.' · '.$espacio->nombre_espacio;
        $datos['id_categoria'] = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => CategoriaSolicitud::INMUEBLE])->getKey();
        $archivo = $request->file('evidencia');
        $datos['fecha_hallazgo'] = Carbon::parse($datos['fecha_hallazgo'], 'America/Mexico_City')->format('Y-m-d H:i:s');
        $datos['huella_reporte'] = hash('sha256', json_encode([
            $espacio->getKey(),
            $datos['id_bien_espacio'] ?? $datos['bien_reportado'] ?? null,
            (int) $datos['id_categoria'],
            $datos['descripcion_falla'],
            $datos['fecha_hallazgo'],
            hash_file('sha256', $archivo->getPathname()),
        ], JSON_THROW_ON_ERROR));
        $datos['archivo_evidencia'] = $archivo;

        try {
            $solicitud = $this->solicitudService->crearSolicitud($datos, $request->user());
        } catch (BusinessRuleException $exception) {
            throw ValidationException::withMessages(['reporte' => $exception->getMessage()]);
        }

        return redirect()->route('reportes.index')->with('status', 'Reporte #'.$solicitud->getKey().' registrado.');
    }
}
