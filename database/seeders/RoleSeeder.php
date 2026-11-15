<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Coordinador',
            'Alumno',
            'Profesor',
            'Tecnico',
            'Almacen',
            'Administrador',
        ];

        foreach ($roles as $nombreRol) {
            Rol::firstOrCreate(['nombre_rol' => $nombreRol]);
        }
    }
}
