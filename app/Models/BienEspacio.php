<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BienEspacio extends Model
{
    public const ESTADOS = [
        'bueno' => 'En buen estado',
        'deteriorado' => 'Deteriorado',
        'en_reparacion' => 'En reparación',
        'fuera_de_servicio' => 'Fuera de servicio',
        'baja' => 'Dado de baja',
    ];

    protected $table = 'bienes_espacio';

    protected $fillable = [
        'codigo_inventario',
        'nombre',
        'tipo',
        'ubicacion_interna',
        'marca',
        'modelo',
        'numero_serie',
        'estado',
        'observaciones',
    ];

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'id_espacio');
    }
}
