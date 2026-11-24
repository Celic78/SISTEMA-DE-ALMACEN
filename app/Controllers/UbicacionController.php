<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Edificio;
use App\Models\Plantel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UbicacionController extends Controller
{
    public function index(): View
    {
        $plantel = Plantel::where('nombre', Plantel::PRINCIPAL)->first();
        $edificios = $plantel?->edificios()->with(['espacios' => fn ($espacios) => $espacios->withCount('bienes')])->orderBy('nombre')->get() ?? collect();

        return view('coordinacion.ubicaciones', compact('edificios'));
    }

    public function guardarEdificio(Request $request): RedirectResponse
    {
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:100']]);
        $plantel = Plantel::firstOrCreate(['nombre' => Plantel::PRINCIPAL], ['direccion' => 'Por registrar']);
        $edificio = $plantel->edificios()->firstOrCreate($datos);

        if (! $edificio->wasRecentlyCreated) {
            throw ValidationException::withMessages(['nombre' => 'Ese edificio ya está registrado.']);
        }

        return redirect()->route('coordinacion.ubicaciones')->with('status', 'Edificio agregado.');
    }

    public function guardarEspacio(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'id_edificio' => ['required', 'integer'],
            'nombre_espacio' => ['required', 'string', 'max:100'],
            'tipo_espacio' => ['required', Rule::in(['Aula', 'Laboratorio', 'Baño', 'Oficina', 'Otro'])],
            'piso' => ['nullable', 'string', 'max:100'],
        ]);

        $edificio = Edificio::whereHas('plantel', fn ($query) => $query->where('nombre', Plantel::PRINCIPAL))
            ->find($datos['id_edificio']);

        if (! $edificio) {
            throw ValidationException::withMessages(['id_edificio' => 'Selecciona un edificio del plantel.']);
        }

        $espacio = $edificio->espacios()->firstOrCreate(['nombre_espacio' => $datos['nombre_espacio']], [
            'tipo_espacio' => $datos['tipo_espacio'],
            'piso' => $datos['piso'] ?? 'Sin especificar',
        ]);

        if (! $espacio->wasRecentlyCreated) {
            throw ValidationException::withMessages(['nombre_espacio' => 'Ese espacio ya está registrado en el edificio.']);
        }

        return redirect()->route('coordinacion.ubicaciones')->with('status', 'Espacio agregado.');
    }
}
