<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Espacio extends Model
{
    use HasFactory;

    protected $table = 'espacios';

    protected $primaryKey = 'id_espacio';

    protected $fillable = [
        'id_edificio',
        'nombre_espacio',
        'tipo_espacio',
        'piso',
    ];

    /**
     * Get the edificio that owns the espacio.
     */
    public function edificio(): BelongsTo
    {
        return $this->belongsTo(Edificio::class, 'id_edificio', 'id_edificio');
    }

    /**
     * Get the users assigned to this espacio.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Get the solicitudes for this espacio.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_espacio', 'id_espacio');
    }
}
