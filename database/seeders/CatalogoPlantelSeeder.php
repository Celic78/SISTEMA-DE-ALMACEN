<?php

namespace Database\Seeders;

use App\Models\Plantel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoPlantelSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $plantel = Plantel::firstOrCreate(['nombre' => Plantel::PRINCIPAL], ['direccion' => 'Por registrar']);
            $catalogo = [
                1 => ['Aula' => 20, 'Baño' => 4, 'Cubículo de profesores' => 6, 'Otro' => ['Enfermería', 'Sala de maestros']],
                2 => ['Aula' => 16, 'Baño' => ['Baño de hombres', 'Baño de mujeres'], 'Laboratorio' => ['Laboratorio de cómputo 1', 'Laboratorio de cómputo 2', 'Laboratorio de cómputo 3']],
                3 => ['Sala para alumnos' => 2, 'Baño' => ['Baño de mujeres', 'Baño de hombres'], 'Oficina' => ['Coordinación escolar', 'Coordinación de inglés', 'Servicios escolares', 'Enfermería'], 'Otro' => ['Biblioteca']],
                4 => ['Aula' => 12, 'Laboratorio' => ['Laboratorio médico 1', 'Laboratorio médico 2', 'Laboratorio médico 3'], 'Baño' => 1, 'Cubículo para alumnos' => 3, 'Otro' => ['Cafetería']],
                5 => ['Aula' => 20, 'Baño' => 4],
            ];

            foreach ($catalogo as $numero => $tipos) {
                $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio '.$numero]);
                $tipos['Otro'][] = 'Escaleras';

                foreach ($tipos as $tipo => $espacios) {
                    $nombres = is_int($espacios)
                        ? array_map(fn (int $numero): string => $tipo.' '.$numero, range(1, $espacios))
                        : $espacios;

                    foreach ($nombres as $nombre) {
                        $edificio->espacios()->firstOrCreate(['nombre_espacio' => $nombre], [
                            'tipo_espacio' => $tipo,
                            'piso' => 'Sin especificar',
                        ]);
                    }
                }
            }
        });
    }
}
