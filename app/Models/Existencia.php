<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Existencia extends Model
{
    use HasFactory;

    protected $table = 'existencias';

    protected $primaryKey = 'id_existencia';

    protected $fillable = [
        'id_material',
        'cantidad_disponible',
        'stock_minimo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad_disponible' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
        ];
    }

    /**
     * Get the material that corresponds to this inventory record.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_material', 'id_material');
    }
}
