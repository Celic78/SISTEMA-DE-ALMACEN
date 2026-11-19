<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatrizPrioridad extends Model
{
    use HasFactory;

    protected $table = 'matrices_prioridad';

    protected $primaryKey = 'id_prioridad';

    protected $fillable = [
        'nivel',
        'tiempo_respuesta_hrs',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tiempo_respuesta_hrs' => 'integer',
        ];
    }

    /**
     * Get the solicitudes associated with this priority level.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_prioridad', 'id_prioridad');
    }
}
