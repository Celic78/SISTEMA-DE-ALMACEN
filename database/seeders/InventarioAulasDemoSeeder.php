<?php

namespace Database\Seeders;

use App\Models\Espacio;
use App\Models\Plantel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioAulasDemoSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = Espacio::with('edificio')
            ->where('tipo_espacio', 'Aula')
            ->whereHas('edificio.plantel', fn ($plantel) => $plantel->where('nombre', Plantel::PRINCIPAL))
            ->orderBy('id_espacio')->get();

        $ahora = now();
        DB::transaction(function () use ($aulas, $ahora): void {
            foreach ($aulas as $aula) {
                $prefijo = sprintf('DEMO-E%03d-A%03d', $aula->id_edificio, $aula->getKey());
                $bienes = [
                    ['MES-01', 'Escritorio docente', 'Mobiliario', 'Frente del aula, junto al pizarrón', 'bueno', 'Cubierta laminada, con cajón lateral.'],
                    ['SIL-01', 'Silla docente', 'Mobiliario', 'Detrás del escritorio docente', 'bueno', 'Respaldo rígido y estructura metálica.'],
                    ['PIZ-01', 'Pizarrón blanco', 'Instalación fija', 'Centro del muro frontal', 'bueno', 'Superficie magnética con canaleta para marcadores.'],
                    ['PUE-01', 'Puerta de acceso', 'Instalación fija', 'Entrada principal del aula', 'bueno', 'Puerta con manija y cerradura de paso.'],
                    ['PER-01', 'Persiana enrollable', 'Instalación fija', 'Ventana más próxima al pizarrón', 'bueno', 'Mecanismo de cadena lateral.'],
                    ['PER-02', 'Persiana enrollable', 'Instalación fija', 'Ventana más alejada del pizarrón', 'bueno', null],
                    ['BAS-01', 'Bote de residuos', 'Accesorio', 'Esquina interior junto a la puerta', 'bueno', 'Recipiente plástico con tapa.'],
                ];

                $filas = 4 + ($aula->getKey() % 3);
                for ($fila = 1; $fila <= $filas; $fila++) {
                    for ($puesto = 1; $puesto <= 6; $puesto++) {
                        $numero = ($fila - 1) * 6 + $puesto;
                        $desgaste = ($numero + $aula->getKey()) % 23 === 0;
                        $bienes[] = [
                            sprintf('PUP-%02d', $numero),
                            'Pupitre individual',
                            'Mobiliario',
                            sprintf('Fila %d desde el pizarrón, puesto %d de izquierda a derecha', $fila, $puesto),
                            $desgaste ? 'deteriorado' : 'bueno',
                            $desgaste ? 'Paleta con bordes desgastados. Revisar al programar mantenimiento.' : null,
                        ];
                    }
                }

                if ($aula->getKey() % 5 === 0) {
                    $bienes[] = ['ARM-01', 'Armario bajo con puertas', 'Mobiliario', 'Muro posterior, junto a la esquina', 'bueno', 'Dos entrepaños para material didáctico.'];
                }

                DB::table('bienes_espacio')->insertOrIgnore(array_map(
                    fn (array $bien): array => [
                        'id_espacio' => $aula->getKey(),
                        'codigo_inventario' => $prefijo.'-'.$bien[0],
                        'nombre' => $bien[1],
                        'tipo' => $bien[2],
                        'ubicacion_interna' => $bien[3],
                        'estado' => $bien[4],
                        'observaciones' => $bien[5],
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ],
                    $bienes,
                ));
            }
        });
    }
}
