<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteFinal extends Model
{
    use HasFactory;

    protected $table = 'reportes_finales';

    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_orden',
        'resumen_ejecutivo',
        'fecha_generacion',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_generacion' => 'datetime',
        ];
    }

    /**
     * Get the work order for which this final report was generated.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }
}
