<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\EvidenciaCierre;
use App\Models\MatrizPrioridad;
use App\Models\OrdenTrabajo;
use App\Models\Plantel;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\EvidenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenciaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EvidenciaService $evidenciaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evidenciaService = app(EvidenciaService::class);
        Storage::fake('public');
    }

    public function test_almacenar_archivo_guarda_en_disco_publico_y_detecta_tipo_fotografia(): void
    {
        $foto = UploadedFile::fake()->image('falla_tuberia.jpg', 600, 400);

        $resultado = $this->evidenciaService->almacenarArchivo($foto, 'evidencias/pruebas');

        $this->assertSame('fotografia', $resultado['tipo']);
        $this->assertNotEmpty($resultado['url']);
        $this->assertNotEmpty($resultado['path']);
        Storage::disk('public')->assertExists($resultado['path']);
    }

    public function test_almacenar_archivo_detecta_video(): void
    {
        $video = UploadedFile::fake()->create('recorrido_tecnico.mp4', 1024, 'video/mp4');

        $resultado = $this->evidenciaService->almacenarArchivo($video, 'evidencias/videos');

        $this->assertSame('video', $resultado['tipo']);
        Storage::disk('public')->assertExists($resultado['path']);
    }

    public function test_almacenar_archivo_detecta_documento_pdf(): void
    {
        $acta = UploadedFile::fake()->create('acta_entrega_recepcion.pdf', 500, 'application/pdf');

        $resultado = $this->evidenciaService->almacenarArchivo($acta, 'evidencias/actas');

        $this->assertSame('documento', $resultado['tipo']);
        Storage::disk('public')->assertExists($resultado['path']);
    }

    public function test_almacenar_archivo_rechaza_extensiones_y_mimes_no_permitidos(): void
    {
        $binario = UploadedFile::fake()->create('archivo_ejecutable.bat', 100, 'application/x-bat');

        $this->expectException(BusinessRuleException::class);
        $this->expectExceptionMessage('Formato de archivo no permitido');

        $this->evidenciaService->almacenarArchivo($binario);
    }

    public function test_registrar_evidencias_cierre_asocia_archivos_a_orden_de_trabajo(): void
    {
        $orden = $this->crearOrdenPrueba();
        $fotoAntes = UploadedFile::fake()->image('antes_reparacion.png', 400, 300);
        $fotoDespues = UploadedFile::fake()->image('despues_reparacion.png', 400, 300);

        $guardadas = $this->evidenciaService->registrarEvidenciasCierre(
            $orden,
            [$fotoAntes, $fotoDespues],
            'Evidencia visual de conclusión de servicio'
        );

        $this->assertCount(2, $guardadas);
        $this->assertDatabaseCount('evidencias_cierre', 2);
        $this->assertDatabaseHas('evidencias_cierre', [
            'id_orden' => $orden->id_orden,
            'tipo' => 'fotografia',
            'descripcion' => 'Evidencia visual de conclusión de servicio',
        ]);
    }

    public function test_almacenar_evidencia_solicitud_genera_url_correcta(): void
    {
        $foto = UploadedFile::fake()->image('hallazgo.webp', 500, 500);

        $url = $this->evidenciaService->almacenarEvidenciaSolicitud($foto, 99);

        $this->assertStringContainsString('evidencias/solicitudes/99', $url);
    }

    public function test_eliminar_evidencia_cierre_borra_archivo_fisico_y_registro_en_base_de_datos(): void
    {
        $orden = $this->crearOrdenPrueba();
        $foto = UploadedFile::fake()->image('foto_a_borrar.jpg', 300, 300);

        $guardadas = $this->evidenciaService->registrarEvidenciasCierre($orden, [$foto]);
        $evidencia = $guardadas->first();

        $this->assertNotNull($evidencia);
        $this->assertDatabaseHas('evidencias_cierre', ['id_evidencia' => $evidencia->id_evidencia]);

        $eliminado = $this->evidenciaService->eliminarEvidenciaCierre($evidencia);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('evidencias_cierre', ['id_evidencia' => $evidencia->id_evidencia]);
    }

    protected function crearOrdenPrueba(): OrdenTrabajo
    {
        $plantel = Plantel::firstOrCreate(['nombre' => 'Plantel Central'], ['direccion' => 'Calle 1']);
        $edificio = $plantel->edificios()->firstOrCreate(['nombre' => 'Edificio 1']);
        $espacio = $edificio->espacios()->firstOrCreate(
            ['nombre_espacio' => 'Aula 1'],
            ['tipo_espacio' => 'Aula', 'piso' => 'Planta Baja']
        );
        $categoria = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'General']);
        $prioridad = MatrizPrioridad::firstOrCreate(['nivel' => 'Media'], ['tiempo_respuesta_hrs' => 24]);
        $user = User::factory()->create();

        $solicitud = Solicitud::create([
            'id_usuario' => $user->id,
            'id_espacio' => $espacio->id_espacio,
            'id_categoria' => $categoria->id_categoria,
            'id_prioridad' => $prioridad->id_prioridad,
            'descripcion_falla' => 'Falla de prueba para evidencias',
            'fecha_hallazgo' => now(),
            'estado' => 'pendiente',
        ]);

        return $solicitud->ordenTrabajo()->create([
            'id_tecnico' => $user->id,
            'estado' => 'en_proceso',
        ]);
    }
}
