<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edificio extends Model
{
    use HasFactory;

    protected $table = 'edificios';

    protected $primaryKey = 'id_edificio';

    protected $fillable = [
        'id_plantel',
        'nombre',
        'descripcion_espacios',
    ];

    /**
     * Get the plantel that owns the edificio.
     */
    public function plantel(): BelongsTo
    {
        return $this->belongsTo(Plantel::class, 'id_plantel', 'id_plantel');
    }

    /**
     * Get the espacios for the edificio.
     */
    public function espacios(): HasMany
    {
        return $this->hasMany(Espacio::class, 'id_edificio', 'id_edificio');
    }
}
