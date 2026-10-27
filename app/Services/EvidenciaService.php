<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\EvidenciaCierre;
use App\Models\OrdenTrabajo;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenciaService
{
    /**
     * Storage disk used for public accessible evidence files.
     */
    protected string $disco = 'public';

    /**
     * Allowed MIME types and their internal categorization.
     *
     * @var array<string, string>
     */
    protected array $mimesPermitidos = [
        // Fotografías
        'image/jpeg' => 'fotografia',
        'image/jpg' => 'fotografia',
        'image/png' => 'fotografia',
        'image/webp' => 'fotografia',
        // Videos
        'video/mp4' => 'video',
        'video/quicktime' => 'video',
        'video/webm' => 'video',
        'video/x-msvideo' => 'video',
        // Documentos y actas
        'application/pdf' => 'documento',
    ];

    /**
     * Maximum allowed file size in kilobytes (50 MB).
     */
    protected int $maxKilobytes = 51200;

    /**
     * Store and register closing evidence files for a work order.
     *
     * @param  list<UploadedFile|array{archivo?: UploadedFile|string, url_archivo?: string, tipo?: string, descripcion?: string|null}>  $items
     * @return Collection<int, EvidenciaCierre>
     *
     * @throws BusinessRuleException
     */
    public function registrarEvidenciasCierre(
        OrdenTrabajo $orden,
        array $items,
        ?string $descripcionPorDefecto = null
    ): Collection {
        if ($items === []) {
            throw new BusinessRuleException(
                'Es obligatorio proporcionar al menos un archivo de evidencia para el cierre de la orden.'
            );
        }

        $guardadas = new Collection();

        foreach ($items as $item) {
            $evidencia = $this->procesarItemEvidenciaCierre($orden, $item, $descripcionPorDefecto);
            $guardadas->push($evidencia);
        }

        return $guardadas;
    }

    /**
     * Process an individual evidence item (file upload or structured data array).
     *
     * @param  UploadedFile|array<string, mixed>  $item
     */
    protected function procesarItemEvidenciaCierre(
        OrdenTrabajo $orden,
        UploadedFile|array $item,
        ?string $descripcionPorDefecto
    ): EvidenciaCierre {
        if ($item instanceof UploadedFile) {
            $almacenado = $this->almacenarArchivo($item, "evidencias/cierre/{$orden->id_orden}");

            return EvidenciaCierre::create([
                'id_orden' => $orden->id_orden,
                'tipo' => $almacenado['tipo'],
                'url_archivo' => $almacenado['url'],
                'descripcion' => $descripcionPorDefecto,
            ]);
        }

        $archivo = $item['archivo'] ?? null;
        $urlArchivo = $item['url_archivo'] ?? null;
        $tipo = $item['tipo'] ?? null;
        $descripcion = $item['descripcion'] ?? $descripcionPorDefecto;

        if ($archivo instanceof UploadedFile) {
            $almacenado = $this->almacenarArchivo($archivo, "evidencias/cierre/{$orden->id_orden}");
            $urlArchivo = $almacenado['url'];
            $tipo = $tipo ?? $almacenado['tipo'];
        } elseif ($urlArchivo === null) {
            throw new BusinessRuleException('No se proporcionó un archivo o URL válido para la evidencia.');
        }

        return EvidenciaCierre::create([
            'id_orden' => $orden->id_orden,
            'tipo' => $tipo ?? 'fotografia',
            'url_archivo' => (string) $urlArchivo,
            'descripcion' => $descripcion,
        ]);
    }

    /**
     * Store an evidence file for an initial incident ticket (Solicitud).
     *
     * @throws BusinessRuleException
     */
    public function almacenarEvidenciaSolicitud(UploadedFile|string $archivo, ?int $idSolicitud = null): string
    {
        if (is_string($archivo)) {
            return trim($archivo);
        }

        $carpeta = $idSolicitud !== null
            ? "evidencias/solicitudes/{$idSolicitud}"
            : 'evidencias/solicitudes';

        $resultado = $this->almacenarArchivo($archivo, $carpeta);

        return $resultado['url'];
    }

    /**
     * Validate and physically store an uploaded file to storage disk.
     *
     * @return array{url: string, path: string, tipo: string, nombre_original: string, bytes: int}
     *
     * @throws BusinessRuleException
     */
    public function almacenarArchivo(UploadedFile $archivo, string $carpeta = 'evidencias'): array
    {
        $this->validarArchivo($archivo);

        $mime = $archivo->getMimeType() ?: 'application/octet-stream';
        $tipo = $this->mimesPermitidos[$mime] ?? 'documento';

        $extension = $archivo->getClientOriginalExtension() ?: 'bin';
        $nombreArchivo = Str::uuid()->toString().'.'.$extension;

        $path = $archivo->storeAs($carpeta, $nombreArchivo, $this->disco);

        if (! $path) {
            throw new BusinessRuleException('Error al escribir el archivo de evidencia en el almacenamiento físico.');
        }

        $url = Storage::disk($this->disco)->url($path);

        return [
            'url' => $url,
            'path' => $path,
            'tipo' => $tipo,
            'nombre_original' => $archivo->getClientOriginalName(),
            'bytes' => (int) $archivo->getSize(),
        ];
    }

    /**
     * Validate file type and size constraints.
     *
     * @throws BusinessRuleException
     */
    public function validarArchivo(UploadedFile $archivo): void
    {
        if (! $archivo->isValid()) {
            throw new BusinessRuleException('El archivo subido está corrupto o no se completó la transferencia.');
        }

        $mime = $archivo->getMimeType();
        if ($mime === null || ! array_key_exists($mime, $this->mimesPermitidos)) {
            throw new BusinessRuleException(
                'Formato de archivo no permitido. Solo se aceptan imágenes (JPG, PNG, WEBP), videos (MP4, MOV, WEBM) o documentos (PDF).'
            );
        }

        $kilobytes = (int) ceil($archivo->getSize() / 1024);
        if ($kilobytes > $this->maxKilobytes) {
            $maxMb = (int) ($this->maxKilobytes / 1024);
            throw new BusinessRuleException(
                "El archivo supera el tamaño máximo institucional permitido ({$maxMb} MB)."
            );
        }
    }

    /**
     * Retrieve all closing evidence files registered for a work order.
     *
     * @return Collection<int, EvidenciaCierre>
     */
    public function obtenerEvidenciasDeOrden(OrdenTrabajo $orden): Collection
    {
        return $orden->evidenciasCierre()->orderBy('created_at')->get();
    }

    /**
     * Physically delete an evidence file and remove its database record.
     */
    public function eliminarEvidenciaCierre(EvidenciaCierre $evidencia): bool
    {
        $url = (string) $evidencia->url_archivo;

        // Si la URL apunta a nuestro storage público, calcular ruta relativa para eliminarla
        $baseUrl = Storage::disk($this->disco)->url('');
        if (str_starts_with($url, $baseUrl)) {
            $relativePath = str_replace($baseUrl, '', $url);
            if (Storage::disk($this->disco)->exists($relativePath)) {
                Storage::disk($this->disco)->delete($relativePath);
            }
        }

        return (bool) $evidencia->delete();
    }
}
