<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Exceptions\DuplicateSolicitudException;
use App\Models\BitacoraSistema;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function __construct(
        protected ?EvidenciaService $evidenciaService = null
    ) {
        $this->evidenciaService ??= app(EvidenciaService::class);
    }
    /**
     * States considered active / unresolved for duplicate prevention.
     *
     * @var list<string>
     */
    protected array $estadosActivos = [
        'pendiente',
        'en_revision',
        'en_proceso',
        'abierta',
        'asignada',
        'requiere_aprobacion',
    ];

    /**
     * Retrieve all spaces strictly belonging to a specific building.
     *
     * Campus Hierarchy: Ensures spaces outside the building are never surfaced.
     *
     * @return Collection<int, Espacio>
     */
    public function obtenerEspaciosPorEdificio(int $idEdificio): Collection
    {
        return Espacio::where('id_edificio', $idEdificio)
            ->orderBy('tipo_espacio')
            ->orderBy('nombre_espacio')
            ->get();
    }

    /**
     * Validate that the chosen space belongs hierarchically to the given building.
     *
     * @throws BusinessRuleException
     */
    public function validarUbicacionJerarquica(int $idEdificio, int $idEspacio): bool
    {
        $pertenece = Espacio::where('id_espacio', $idEspacio)
            ->where('id_edificio', $idEdificio)
            ->exists();

        if (! $pertenece) {
            throw new BusinessRuleException(
                'Ubicación inválida: El espacio seleccionado no pertenece al edificio especificado.',
                422
            );
        }

        return true;
    }

    /**
     * Check if the user already submitted an identical active report.
     *
     * Rule: Checks combination of (id_usuario + id_espacio + id_categoria) with active status.
     */
    public function existeSolicitudDuplicadaUsuario(int $idUsuario, int $idEspacio, int $idCategoria): bool
    {
        return Solicitud::where('id_usuario', $idUsuario)
            ->where('id_espacio', $idEspacio)
            ->where('id_categoria', $idCategoria)
            ->whereIn('estado', $this->estadosActivos)
            ->exists();
    }

    /**
     * Create a new service ticket (Solicitud) applying all university business rules.
     *
     * @param  array{
     *     id_edificio: int,
     *     id_espacio: int,
     *     id_categoria: int,
     *     id_prioridad?: int|null,
     *     descripcion_falla: string,
     *     fecha_hallazgo?: string|Carbon|null,
     *     evidencia_url?: string|null
     * }  $datos
     *
     * @throws BusinessRuleException
     * @throws DuplicateSolicitudException
     */
    public function crearSolicitud(array $datos, User $usuario): Solicitud
    {
        $idEdificio = (int) ($datos['id_edificio'] ?? 0);
        $idEspacio = (int) ($datos['id_espacio'] ?? 0);
        $idCategoria = (int) ($datos['id_categoria'] ?? 0);

        if ($idEdificio <= 0 || $idEspacio <= 0 || $idCategoria <= 0) {
            throw new BusinessRuleException('Edificio, espacio y categoría son obligatorios para reportar la falla.');
        }

        // REGLA 1: Validación jerárquica de ubicación (Edificio -> Espacio)
        $this->validarUbicacionJerarquica($idEdificio, $idEspacio);

        // REGLA 2: Anti-duplicados por usuario
        if ($this->existeSolicitudDuplicadaUsuario($usuario->id, $idEspacio, $idCategoria)) {
            throw new DuplicateSolicitudException(
                'Ya cuenta con un reporte registrado para esta misma ubicación y categoría en proceso de atención.'
            );
        }

        // Resolución de prioridad por defecto si no se proporcionó
        $idPrioridad = $datos['id_prioridad'] ?? null;
        if ($idPrioridad === null) {
            $prioridadDefault = MatrizPrioridad::where('nivel', 'Media')->first()
                ?? MatrizPrioridad::first();
            $idPrioridad = $prioridadDefault?->id_prioridad;
        }

        // Procesamiento de evidencia física/digital si se adjuntó un archivo
        $evidenciaUrl = $datos['evidencia_url'] ?? null;
        if (isset($datos['archivo_evidencia']) && $datos['archivo_evidencia'] instanceof \Illuminate\Http\UploadedFile) {
            $evidenciaUrl = $this->evidenciaService->almacenarEvidenciaSolicitud($datos['archivo_evidencia']);
        }

        return DB::transaction(function () use ($datos, $usuario, $idEspacio, $idCategoria, $idPrioridad, $evidenciaUrl): Solicitud {
            $solicitud = Solicitud::create([
                'id_usuario' => $usuario->id,
                'id_espacio' => $idEspacio,
                'id_categoria' => $idCategoria,
                'id_prioridad' => $idPrioridad,
                'descripcion_falla' => trim((string) $datos['descripcion_falla']),
                'fecha_hallazgo' => $datos['fecha_hallazgo'] ?? now(),
                'estado' => 'pendiente',
                'evidencia_url' => $evidenciaUrl,
            ]);

            // Crear orden de trabajo inicial vinculada para control operativo
            OrdenTrabajo::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_tecnico' => null,
                'monto_estimado' => 0.00,
                'requiere_aprobacion' => false,
                'estado' => 'abierta',
            ]);

            // Auditoría en bitácora de sistema
            BitacoraSistema::create([
                'id_usuario' => $usuario->id,
                'accion' => 'SOLICITUD_CREADA',
                'descripcion' => json_encode([
                    'id_solicitud' => $solicitud->id_solicitud,
                    'id_espacio' => $idEspacio,
                    'id_categoria' => $idCategoria,
                ], JSON_UNESCAPED_UNICODE),
                'fecha' => now(),
            ]);

            return $solicitud;
        });
    }

    /**
     * List all buildings along with their child spaces for map rendering.
     *
     * @return Collection<int, Edificio>
     */
    public function obtenerEdificiosConEspacios(?int $idPlantel = null): Collection
    {
        $query = Edificio::with(['espacios' => function ($q) {
            $q->orderBy('nombre_espacio');
        }]);

        if ($idPlantel !== null) {
            $query->where('id_plantel', $idPlantel);
        }

        return $query->get();
    }
}
