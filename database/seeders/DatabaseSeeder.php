<?php

namespace Database\Seeders;

use App\Models\CategoriaSolicitud;
use App\Models\MatrizPrioridad;
use App\Models\Rol;
use App\Models\SeccionAlmacen;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Electrónico', 'Eléctrico', 'Inmueble', 'Accesorio', 'Plomería', 'Otro'] as $categoria) {
            CategoriaSolicitud::firstOrCreate(['nombre_categoria' => $categoria]);
        }

        foreach (['Crítica' => 2, 'Alta' => 8, 'Media' => 24, 'Baja' => 72] as $nivel => $horas) {
            MatrizPrioridad::firstOrCreate(['nivel' => $nivel], ['tiempo_respuesta_hrs' => $horas]);
        }

        foreach (['Cables', 'Muebles', 'Insumos', 'Herramientas'] as $seccion) {
            SeccionAlmacen::firstOrCreate(['nombre_seccion' => $seccion]);
        }

        if (! app()->environment('local', 'testing')) {
            return;
        }

        $cuentas = [
            'alumno' => ['Ana Torres', 'Luis Vega'],
            'profesor' => ['Elena Rojas', 'Mario Luna'],
            'coordinador' => ['Clara Ruiz', 'Diego Mora'],
            'tecnico' => ['Pablo Cruz', 'Sofia Reyes'],
            'almacen' => ['Rosa Silva', 'Hugo Leon'],
        ];

        DB::transaction(function () use ($cuentas): void {
            foreach ($cuentas as $tipo => $nombres) {
                $rol = Rol::firstOrCreate(['nombre_rol' => ucfirst($tipo)]);

                foreach ($nombres as $indice => $nombre) {
                    $correo = str_replace(' ', '.', strtolower($nombre)).'@'.$tipo.'.mx';

                    $usuario = User::firstOrCreate(['email' => $correo], [
                        'name' => $nombre,
                        'password' => 'MantrescDemo2026!',
                        'email_tipo' => $tipo,
                    ]);
                    if ($usuario->wasRecentlyCreated) {
                        $usuario->roles()->attach($rol->getKey(), $tipo === 'tecnico' ? [
                            'turno_tecnico' => $indice === 0 ? 'matutino' : 'vespertino',
                            'especialidad_tecnico' => $indice === 0 ? 'Electricidad' : 'Plomería',
                        ] : []);
                    }
                }
            }
        });
    }
}
