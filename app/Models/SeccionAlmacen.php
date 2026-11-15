<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionAlmacen extends Model
{
    use HasFactory;

    protected $table = 'secciones_almacen';

    protected $primaryKey = 'id_seccion';

    protected $fillable = [
        'nombre_seccion',
        'ubicacion_almacen',
    ];

    /**
     * Get the materials stored in this warehouse section.
     */
    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'id_seccion', 'id_seccion');
    }
}
