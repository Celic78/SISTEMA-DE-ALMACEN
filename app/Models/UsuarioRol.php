<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UsuarioRol extends Pivot
{
    protected $table = 'usuario_rol';

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'turno_tecnico',
        'especialidad_tecnico',
    ];

    /**
     * Get the user associated with this pivot entry.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Get the role associated with this pivot entry.
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}
