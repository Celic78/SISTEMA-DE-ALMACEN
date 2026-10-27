<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_tipo',
        'id_espacio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the espacio assigned to the user.
     */
    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'id_espacio', 'id_espacio');
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
            ->using(UsuarioRol::class)
            ->withPivot('turno_tecnico', 'especialidad_tecnico')
            ->withTimestamps();
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRol(string $nombreRol): bool
    {
        return $this->roles->contains(function (Rol $rol) use ($nombreRol): bool {
            return mb_strtolower(trim($rol->nombre_rol)) === mb_strtolower(trim($nombreRol));
        });
    }

    /**
     * Check if the user has any of the specified roles.
     *
     * @param  list<string>  $roles
     */
    public function hasAnyRol(array $roles): bool
    {
        $normalized = array_map(static fn (string $rol): string => mb_strtolower(trim($rol)), $roles);

        return $this->roles->contains(function (Rol $rol) use ($normalized): bool {
            return in_array(mb_strtolower(trim($rol->nombre_rol)), $normalized, true);
        });
    }

    /**
     * Determine if the user has administrative privileges.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRol(['Administrador', 'Admin']);
    }

    /**
     * Determine if the user has coordinator privileges.
     */
    public function isCoordinador(): bool
    {
        return $this->isAdmin() || $this->hasAnyRol(['Coordinador', 'Coordinación']) || $this->email_tipo === 'coordinador';
    }

    /**
     * Determine if the user is a technician.
     */
    public function isTecnico(): bool
    {
        return $this->hasAnyRol(['Tecnico', 'Técnico', 'Cuadrilla']) || $this->email_tipo === 'tecnico';
    }

    /**
     * Determine if the user belongs to warehouse staff.
     */
    public function isAlmacen(): bool
    {
        return $this->hasAnyRol(['Almacen', 'Almacén', 'Almacenista']) || $this->email_tipo === 'almacen';
    }

    /**
     * Get the solicitudes created by the user.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'id_usuario', 'id');
    }

    /**
     * Get the work orders assigned to the user as a technician.
     */
    public function ordenesTrabajo(): HasMany
    {
        return $this->hasMany(OrdenTrabajo::class, 'id_tecnico', 'id');
    }

    /**
     * Get the approvals made by the user as coordinator.
     */
    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class, 'id_coordinador', 'id');
    }

    /**
     * Get warehouse material movements logged by the user.
     */
    public function movimientosMaterial(): HasMany
    {
        return $this->hasMany(MovimientoMaterial::class, 'id_usuario_almacen', 'id');
    }

    /**
     * Get the status changes made by the user.
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_usuario_cambio', 'id');
    }

    /**
     * Get system audit logs recorded for the user.
     */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(BitacoraSistema::class, 'id_usuario', 'id');
    }
}
