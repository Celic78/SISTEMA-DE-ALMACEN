<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrdenTrabajo extends Model
{
    use HasFactory;

    protected $table = 'ordenes_trabajo';

    protected $primaryKey = 'id_orden';

    protected $fillable = [
        'id_solicitud',
        'id_tecnico',
        'monto_estimado',
        'requiere_aprobacion',
        'estado',
        'fecha_inicio',
        'fecha_fin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto_estimado' => 'decimal:2',
            'requiere_aprobacion' => 'boolean',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    /**
     * Get the solicitud that generated this work order.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud', 'id_solicitud');
    }

    /**
     * Get the technician assigned to this work order.
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tecnico', 'id');
    }

    /**
     * Get the tasks checklist for this work order.
     */
    public function tareas(): HasMany
    {
        return $this->hasMany(TareaOrden::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the approvals for this work order.
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the material movements associated with this work order.
     */
    public function movimientosMaterial(): HasMany
    {
        return $this->hasMany(MovimientoMaterial::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the status transition history for this work order.
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_orden', 'id_orden');
    }

    /**
     * Get closing evidence files (before/after photos, acts) for this work order.
     */
    public function evidenciasCierre(): HasMany
    {
        return $this->hasMany(EvidenciaCierre::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the final executive report for this work order.
     */
    public function reporteFinal(): HasOne
    {
        return $this->hasOne(ReporteFinal::class, 'id_orden', 'id_orden');
    }
}
