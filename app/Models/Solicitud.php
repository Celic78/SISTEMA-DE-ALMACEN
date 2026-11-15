<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $primaryKey = 'id_solicitud';

    protected $fillable = [
        'id_usuario',
        'id_espacio',
        'id_categoria',
        'id_prioridad',
        'id_solicitud_padre',
        'descripcion_falla',
        'fecha_hallazgo',
        'estado',
        'evidencia_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_hallazgo' => 'datetime',
        ];
    }

    /**
     * Get the user that reported the solicitud.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Get the espacio where the issue was located.
     */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Get the category of the solicitud.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaSolicitud::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Get the priority SLA definition for the solicitud.
     */
    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(MatrizPrioridad::class, 'id_prioridad', 'id_prioridad');
    }

    /**
     * Get the parent solicitud if this is a consolidated duplicate.
     */
    public function solicitudPadre(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class, 'id_solicitud_padre', 'id_solicitud');
    }

    /**
     * Get child duplicate solicitudes grouped under this request.
     */
    public function solicitudesHijas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_solicitud_padre', 'id_solicitud');
    }

    /**
     * Get the work order generated for this solicitud.
     */
    public function ordenTrabajo(): HasOne
    {
        return $this->hasOne(OrdenTrabajo::class, 'id_solicitud', 'id_solicitud');
    }
}
