<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoMaterial extends Model
{
    use HasFactory;

    protected $table = 'movimientos_material';

    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_material',
        'id_orden',
        'id_usuario_almacen',
        'tipo_movimiento',
        'cantidad',
        'fecha_movimiento',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_movimiento' => 'datetime',
        ];
    }

    /**
     * Get the material involved in this movement.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }

    /**
     * Get the work order associated with this movement, if any.
     */
    public function ordenTrabajo(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'id_orden', 'id_orden');
    }

    /**
     * Get the warehouse user who executed or logged this movement.
     */
    public function usuarioAlmacen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_almacen', 'id');
    }
}
