<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\BusinessRuleException;
use App\Exceptions\InsufficientStockException;
use App\Models\BitacoraSistema;
use App\Models\Existencia;
use App\Models\Material;
use App\Models\MovimientoMaterial;
use App\Models\OrdenTrabajo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AlmacenService
{
    /**
     * Step 1: Pre-notice by Coordinator scheduling material collection for a work order.
     */
    public function registrarPreavisoCoordinacion(
        OrdenTrabajo $orden,
        User $coordinador,
        Carbon|string $fechaEstimadaRecoleccion,
        ?string $instrucciones = null
    ): void {
        $momento = $fechaEstimadaRecoleccion instanceof Carbon
            ? $fechaEstimadaRecoleccion
            : Carbon::parse($fechaEstimadaRecoleccion);

        BitacoraSistema::create([
            'id_usuario' => $coordinador->id,
            'accion' => 'PRENOTIFICACION_DESPACHO_ALMACEN',
            'descripcion' => json_encode([
                'id_orden' => $orden->id_orden,
                'fecha_estimada_recoleccion' => $momento->toDateTimeString(),
                'instrucciones' => $instrucciones,
            ], JSON_UNESCAPED_UNICODE),
            'fecha' => now(),
        ]);
    }

    /**
     * Step 3: Confirm and execute triangulated material dispatch for a work order.
     *
     * Business Rules:
     * 1. Validates order approval if high-risk or high-cost order.
     * 2. Checks real-time stock availability; fails if insufficient.
     * 3. Decrements inventory in database immediately.
     * 4. Logs movement in Kardex (movimientos_material) and audits in bitacora_sistema.
     *
     * @param  list<array{id_material: int, cantidad: float}>  $itemsDespacho
     * @return list<MovimientoMaterial>
     *
     * @throws ApprovalRequiredException
     * @throws InsufficientStockException
     * @throws BusinessRuleException
     */
    public function despacharMateriales(
        OrdenTrabajo $orden,
        User $usuarioAlmacen,
        array $itemsDespacho
    ): array {
        if ($itemsDespacho === []) {
            throw new BusinessRuleException('Debe incluir al menos un material para el despacho.');
        }

        // VALIDACIÓN DE NEGOCIO: Bloqueo de almacén si requiere visto bueno y no está aprobada
        if ($orden->requiere_aprobacion && mb_strtolower((string) $orden->estado) !== 'aprobada') {
            throw new ApprovalRequiredException(
                "Despacho bloqueado: La orden #{$orden->id_orden} requiere visto bueno previo de Coordinación."
            );
        }

        return DB::transaction(function () use ($orden, $usuarioAlmacen, $itemsDespacho): array {
            $movimientosGenerados = [];

            foreach ($itemsDespacho as $item) {
                $idMaterial = (int) $item['id_material'];
                $cantidad = (float) $item['cantidad'];

                if ($cantidad <= 0) {
                    continue;
                }

                $material = Material::with('existencia')->findOrFail($idMaterial);
                $existencia = $material->existencia;

                if ($existencia === null) {
                    throw new BusinessRuleException(
                        "El material '{$material->nombre_material}' no cuenta con registro de inventario configurado."
                    );
                }

                $stockActual = (float) $existencia->cantidad_disponible;

                if ($stockActual < $cantidad) {
                    throw new InsufficientStockException(
                        "Stock insuficiente para '{$material->nombre_material}'. Disponible: {$stockActual}, Solicitado: {$cantidad} {$material->unidad_medida}."
                    );
                }

                // Descuento en tiempo real de la existencia
                $nuevoStock = $stockActual - $cantidad;
                $existencia->update([
                    'cantidad_disponible' => $nuevoStock,
                ]);

                // Registro formal en el Kardex de Movimientos
                $movimiento = MovimientoMaterial::create([
                    'id_material' => $material->id_material,
                    'id_orden' => $orden->id_orden,
                    'id_usuario_almacen' => $usuarioAlmacen->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $cantidad,
                    'fecha_movimiento' => now(),
                ]);

                $movimientosGenerados[] = $movimiento;

                // Auditoría en bitácora de sistema
                BitacoraSistema::create([
                    'id_usuario' => $usuarioAlmacen->id,
                    'accion' => 'DESPACHO_MATERIAL_ALMACEN',
                    'descripcion' => json_encode([
                        'id_orden' => $orden->id_orden,
                        'id_material' => $material->id_material,
                        'nombre_material' => $material->nombre_material,
                        'cantidad' => $cantidad,
                        'unidad_medida' => $material->unidad_medida,
                        'stock_remanente' => $nuevoStock,
                        'alerta_stock_minimo' => $nuevoStock <= (float) $existencia->stock_minimo,
                    ], JSON_UNESCAPED_UNICODE),
                    'fecha' => now(),
                ]);
            }

            return $movimientosGenerados;
        });
    }

    /**
     * Register stock replenishments or new entries into the warehouse.
     */
    public function registrarEntradaStock(
        int $idMaterial,
        float $cantidad,
        User $usuarioAlmacen,
        string $motivo = 'Reabastecimiento regular'
    ): MovimientoMaterial {
        if ($cantidad <= 0) {
            throw new BusinessRuleException('La cantidad a ingresar debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($idMaterial, $cantidad, $usuarioAlmacen, $motivo): MovimientoMaterial {
            $material = Material::findOrFail($idMaterial);
            $existencia = Existencia::firstOrCreate(
                ['id_material' => $material->id_material],
                ['cantidad_disponible' => 0.00, 'stock_minimo' => 5.00]
            );

            $nuevoStock = (float) $existencia->cantidad_disponible + $cantidad;
            $existencia->update([
                'cantidad_disponible' => $nuevoStock,
            ]);

            $movimiento = MovimientoMaterial::create([
                'id_material' => $material->id_material,
                'id_orden' => null,
                'id_usuario_almacen' => $usuarioAlmacen->id,
                'tipo_movimiento' => 'entrada',
                'cantidad' => $cantidad,
                'fecha_movimiento' => now(),
            ]);

            BitacoraSistema::create([
                'id_usuario' => $usuarioAlmacen->id,
                'accion' => 'ENTRADA_STOCK_ALMACEN',
                'descripcion' => json_encode([
                    'id_material' => $material->id_material,
                    'cantidad' => $cantidad,
                    'stock_total' => $nuevoStock,
                    'motivo' => $motivo,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $movimiento;
        });
    }

    /**
     * Retrieve complete Kardex movement ledger for a given material.
     *
     * @return Collection<int, MovimientoMaterial>
     */
    public function obtenerKardexMaterial(int $idMaterial): Collection
    {
        return MovimientoMaterial::where('id_material', $idMaterial)
            ->with(['usuarioAlmacen', 'ordenTrabajo'])
            ->orderByDesc('fecha_movimiento')
            ->get();
    }

    /**
     * Retrieve materials where current stock is equal to or below the minimum threshold.
     *
     * @return Collection<int, Material>
     */
    public function obtenerMaterialesBajoStockMinimo(): Collection
    {
        return Material::whereHas('existencia', function ($q) {
            $q->whereColumn('cantidad_disponible', '<=', 'stock_minimo');
        })->with('existencia')->get();
    }
}
