<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estados';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_orden',
        'estado_anterior',
        'estado_nuevo',
        'id_usuario_cambio',
        'fecha_cambio',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
        ];
    }

    /**
     * Get the work order for which the status changed.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the user who executed the status change.
     */
    public function usuarioCambio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_cambio', 'id');
    }
}
