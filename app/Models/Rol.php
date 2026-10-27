<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre_rol',
    ];

    /**
     * Get the users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuario_rol', 'id_rol', 'id_usuario')
            ->using(UsuarioRol::class)
            ->withPivot('turno_tecnico', 'especialidad_tecnico')
            ->withTimestamps();
    }
}
