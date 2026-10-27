<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaOrden extends Model
{
    use HasFactory;

    protected $table = 'tareas_orden';

    protected $primaryKey = 'id_tarea';

    protected $fillable = [
        'id_orden',
        'descripcion_tarea',
        'completada',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completada' => 'boolean',
        ];
    }

    /**
     * Get the work order this task belongs to.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }
}
