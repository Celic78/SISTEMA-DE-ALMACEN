<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\SeccionAlmacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $catalogo = [
            'insumos' => [
                ['Papel bond tamaño carta', 'resma', 25],
                ['Bolígrafo tinta negra', 'pieza', 60],
                ['Marcador para pizarrón', 'pieza', 36],
                ['Cinta adhesiva transparente', 'rollo', 18],
                ['Carpeta de tres argollas', 'pieza', 0],
            ],
            'mobiliario' => [
                ['Silla apilable', 'pieza', 14],
                ['Mesa plegable', 'pieza', 8],
                ['Estante metálico', 'pieza', 6],
                ['Archivero de dos cajones', 'pieza', 4],
                ['Banco alto de taller', 'pieza', 0],
            ],
            'herramientas' => [
                ['Martillo de uña', 'pieza', 8],
                ['Pinza universal', 'pieza', 12],
                ['Destornillador Phillips', 'pieza', 16],
                ['Llave ajustable', 'pieza', 9],
                ['Flexómetro de cinco metros', 'pieza', 0],
            ],
            'electricos' => [
                ['Cable calibre 12', 'metro', 120],
                ['Contacto dúplex', 'pieza', 24],
                ['Interruptor sencillo', 'pieza', 30],
                ['Lámpara LED de 9 W', 'pieza', 40],
                ['Clavija polarizada', 'pieza', 0],
            ],
        ];

        DB::transaction(function () use ($catalogo): void {
            foreach ($catalogo as $categoria => $articulos) {
                $seccion = SeccionAlmacen::firstOrCreate([
                    'nombre_seccion' => SeccionAlmacen::CATEGORIAS[$categoria]['nombre'],
                ]);

                foreach ($articulos as $indice => [$nombre, $unidad, $cantidad]) {
                    $material = Material::firstOrCreate([
                        'codigo_qr_barras' => 'DEMO-'.strtoupper($categoria).'-'.($indice + 1),
                    ], [
                        'id_seccion' => $seccion->getKey(),
                        'nombre_material' => $nombre,
                        'unidad_medida' => $unidad,
                    ]);

                    $material->existencia()->firstOrCreate([], [
                        'cantidad_disponible' => $cantidad,
                        'stock_minimo' => 3,
                    ]);
                }
            }
        });
    }
}
