<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcategoria extends Model
{
    use HasFactory;

    protected $table = 'subcategorias';

    protected $primaryKey = 'id_subcategoria';

    protected $fillable = [
        'id_categoria',
        'nombre',
    ];

    /**
     * Get the category that owns the subcategory.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaSolicitud::class, 'id_categoria', 'id_categoria');
    }
}
