<?php

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BienEspacio;
use App\Models\Espacio;
use App\Models\Plantel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BienEspacioController extends Controller
{
    public function index(Espacio $espacio): View
    {
        $this->validarPlantel($espacio);
        $bienes = $espacio->bienes()->orderBy('nombre')->orderBy('id')->paginate(20);
        $bien = new BienEspacio(['estado' => 'bueno']);

        return view('coordinacion.bienes.index', compact('espacio', 'bienes', 'bien'));
    }

    public function store(Request $request, Espacio $espacio): RedirectResponse
    {
        $this->validarPlantel($espacio);
        $datos = $this->validarBien($request);

        try {
            $espacio->bienes()->create($datos);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['codigo_inventario' => 'El código de inventario ya está registrado.']);
        }

        return redirect()->route('coordinacion.bienes.index', $espacio)->with('status', 'Bien agregado al espacio.');
    }

    public function edit(Espacio $espacio, BienEspacio $bien): View
    {
        $this->validarPlantel($espacio);
        abort_unless((int) $bien->id_espacio === (int) $espacio->getKey(), 404);

        return view('coordinacion.bienes.editar', compact('espacio', 'bien'));
    }

    public function update(Request $request, Espacio $espacio, BienEspacio $bien): RedirectResponse
    {
        $this->validarPlantel($espacio);
        abort_unless((int) $bien->id_espacio === (int) $espacio->getKey(), 404);
        $datos = $this->validarBien($request, $bien);

        try {
            $bien->update($datos);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['codigo_inventario' => 'El código de inventario ya está registrado.']);
        }

        return redirect()->route('coordinacion.bienes.index', $espacio)->with('status', 'Ficha del bien actualizada.');
    }

    private function validarPlantel(Espacio $espacio): void
    {
        $espacio->loadMissing('edificio.plantel');
        abort_unless($espacio->edificio?->plantel?->nombre === Plantel::PRINCIPAL, 404);
    }

    private function validarBien(Request $request, ?BienEspacio $bien = null): array
    {
        $codigo = $request->input('codigo_inventario');
        if (is_string($codigo)) {
            $request->merge(['codigo_inventario' => mb_strtoupper(trim($codigo))]);
        }
        $codigoUnico = Rule::unique('bienes_espacio', 'codigo_inventario');
        if ($bien) {
            $codigoUnico->ignore($bien);
        }

        return $request->validate([
            'codigo_inventario' => ['required', 'string', 'max:60', $codigoUnico],
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', 'string', 'max:80'],
            'ubicacion_interna' => ['required', 'string', 'max:150'],
            'marca' => ['nullable', 'string', 'max:80'],
            'modelo' => ['nullable', 'string', 'max:80'],
            'numero_serie' => ['nullable', 'string', 'max:80'],
            'estado' => ['required', Rule::in(array_keys(BienEspacio::ESTADOS))],
            'observaciones' => ['nullable', 'string', 'max:1500'],
        ], ['codigo_inventario.unique' => 'El código de inventario ya está registrado.']);
    }
}
