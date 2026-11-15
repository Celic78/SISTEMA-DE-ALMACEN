<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aprobacion extends Model
{
    use HasFactory;

    protected $table = 'aprobaciones';

    protected $primaryKey = 'id_aprobacion';

    protected $fillable = [
        'id_orden',
        'id_coordinador',
        'estado',
        'motivo',
    ];

    /**
     * Get the work order requiring this approval.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the coordinator user who evaluated this approval.
     */
    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_coordinador', 'id');
    }
}
