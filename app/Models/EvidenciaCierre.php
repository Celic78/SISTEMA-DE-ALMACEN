<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenciaCierre extends Model
{
    use HasFactory;

    protected $table = 'evidencias_cierre';

    protected $primaryKey = 'id_evidencia';

    protected $fillable = [
        'id_orden',
        'tipo',
        'url_archivo',
        'descripcion',
    ];

    /**
     * Get the work order to which this evidence belongs.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }
}
