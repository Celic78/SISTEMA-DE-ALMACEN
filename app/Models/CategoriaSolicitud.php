<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaSolicitud extends Model
{
    use HasFactory;

    protected $table = 'categorias_solicitud';

    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nombre_categoria',
        'descripcion',
    ];

    /**
     * Get the subcategories for this category.
     */
    public function subcategorias(): HasMany
    {
        return $this->hasMany(Subcategoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Get the solicitudes belonging to this category.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_categoria', 'id_categoria');
    }
}
