<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionAlmacen extends Model
{
    use HasFactory;

    public const CATEGORIAS = [
        'insumos' => ['nombre' => 'Insumos', 'etiqueta' => 'Insumos y Papelería'],
        'mobiliario' => ['nombre' => 'Muebles', 'etiqueta' => 'Mobiliario y Equipos'],
        'herramientas' => ['nombre' => 'Herramientas', 'etiqueta' => 'Herramientas'],
        'electricos' => ['nombre' => 'Cables', 'etiqueta' => 'Cables y Eléctricos'],
    ];

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
