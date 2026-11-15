<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Material extends Model
{
    use HasFactory;

    protected $table = 'materiales';

    protected $primaryKey = 'id_material';

    protected $fillable = [
        'id_seccion',
        'nombre_material',
        'especificacion',
        'unidad_medida',
        'codigo_qr_barras',
    ];

    /**
     * Get the warehouse section where the material is assigned.
     */
    public function seccion(): BelongsTo
    {
        return $this->belongsTo(SeccionAlmacen::class, 'id_seccion', 'id_seccion');
    }

    /**
     * Get the current stock inventory record for this material.
     */
    public function existencia(): HasOne
    {
        return $this->hasOne(Existencia::class, 'id_material', 'id_material');
    }

    /**
     * Get the movement history entries for this material.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoMaterial::class, 'id_material', 'id_material');
    }
}
