<?php

namespace App\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Models\CategoriaSolicitud;
use App\Services\SolicitudService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReporteController extends Controller
{
    public function __construct(protected SolicitudService $solicitudService) {}

    public function index(Request $request): View
    {
        $solicitudes = $request->user()->solicitudes()
            ->with(['espacio.edificio', 'categoria', 'ordenTrabajo', 'solicitudPadre.ordenTrabajo'])
            ->latest('id_solicitud')
            ->paginate(15);

        return view('reportes.mis-reportes', compact('solicitudes'));
    }

    public function crear(): View
    {
        return view('reportes.crear', ['categorias' => CategoriaSolicitud::orderBy('nombre_categoria')->get()]);
    }

    public function guardar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'ubicacion_libre' => ['required', 'string', 'max:255'],
            'id_categoria' => ['required', 'integer', 'exists:categorias_solicitud,id_categoria'],
            'descripcion_falla' => ['required', 'string', 'min:10', 'max:2000'],
            'fecha_hallazgo' => ['required', 'date_format:Y-m-d\TH:i'],
            'evidencia' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $archivo = $request->file('evidencia');
        $datos['fecha_hallazgo'] = Carbon::parse($datos['fecha_hallazgo'])->format('Y-m-d H:i:s');
        $datos['huella_reporte'] = hash('sha256', json_encode([
            $datos['ubicacion_libre'],
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
