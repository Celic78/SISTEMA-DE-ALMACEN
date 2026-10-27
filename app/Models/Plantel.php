<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plantel extends Model
{
    use HasFactory;

    protected $table = 'planteles';

    protected $primaryKey = 'id_plantel';

    protected $fillable = [
        'nombre',
        'direccion',
    ];

    /**
     * Get the edificios associated with the plantel.
     */
    public function edificios(): HasMany
    {
        return $this->hasMany(Edificio::class, 'id_plantel', 'id_plantel');
    }
}
