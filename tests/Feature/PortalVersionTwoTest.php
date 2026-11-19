<?php

namespace Tests\Feature;

use App\Models\BitacoraSistema;
use App\Models\Material;
use App\Models\OrdenTrabajo;
use App\Models\Solicitud;
use App\Models\User;
use Database\Seeders\SimicDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortalVersionTwoTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            'nuevo reporte' => ['alumno', '/reportes/nuevo', 'Nuevo reporte'],
            'mis reportes' => ['alumno', '/reportes', 'Mis reportes'],
            'coordinacion' => ['coordinador', '/coordinacion', 'Coordinación'],
            'incidencias' => ['coordinador', '/coordinacion/incidencias', 'Incidencias entrantes'],
            'duplicados' => ['coordinador', '/coordinacion/duplicados', 'Posibles duplicados'],
            'asignacion' => ['coordinador', '/coordinacion/cuadrillas', 'Asignación de cuadrillas'],
            'autorizaciones' => ['coordinador', '/coordinacion/autorizaciones', 'Autorizaciones especiales'],
            'ordenes' => ['tecnico', '/cuadrillas', 'Mis órdenes de trabajo'],
            'inventario' => ['almacen', '/almacen', 'Inventario'],
            'solicitudes materiales' => ['almacen', '/almacen/solicitudes', 'Solicitudes de materiales'],
            'entrada' => ['almacen', '/almacen/entradas', 'Entrada de inventario'],
            'bitacora' => ['almacen', '/almacen/bitacora', 'Bitácora de almacén'],
        ];
    }

    #[DataProvider('pages')]
    public function test_renderiza_cada_modulo_con_su_perfil(string $role, string $path, string $title): void
    {
        $this->seed(SimicDemoSeeder::class);
        $this->actingAs($this->user($role))->get($path)->assertOk()->assertSee($title)->assertSee('data-theme-toggle', false);
    }

    #[DataProvider('pages')]
    public function test_protege_los_modulos_sin_sesion(string $role, string $path, string $title): void
    {
        $this->get($path)->assertRedirect('/login');
    }

    public function test_el_acceso_autentica_y_cierra_la_sesion(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $this->get('/')->assertOk()->assertSee('Un campus cuidado.');
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
        $this->post('/login', ['email' => 'coordinacion@univ.edu.mx', 'password' => 'SimicDemo2026!', 'rol' => 'coordinador'])->assertRedirect('/coordinacion');
        $this->assertAuthenticatedAs($this->user('coordinador'));
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_rechaza_un_perfil_no_asignado_a_la_cuenta(): void
    {
        $user = User::factory()->create(['email' => 'intruso@admin.edu.mx', 'email_tipo' => 'alumno']);
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'password', 'rol' => 'coordinador'])->assertRedirect('/login')->assertSessionHas('error');
        $this->assertGuest();
    }

    public static function restrictedPages(): array
    {
        return ['coordinacion' => ['/coordinacion'], 'tecnico' => ['/cuadrillas'], 'almacen' => ['/almacen']];
    }

    #[DataProvider('restrictedPages')]
    public function test_solicitante_recibe_403_en_modulos_ajenos(string $path): void
    {
        $user = User::factory()->create(['email_tipo' => 'alumno']);
        $this->actingAs($user)->get($path)->assertForbidden();
    }

    public function test_oculta_reportes_y_ordenes_de_otras_personas(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $report = Solicitud::where('id_usuario', $this->user('profesor')->id)->firstOrFail();
        $this->actingAs($this->user('alumno'))->get('/reportes/'.$report->id_solicitud)->assertNotFound();
        $other = User::factory()->create(['email_tipo' => 'tecnico']);
        $order = OrdenTrabajo::whereNotNull('id_tecnico')->firstOrFail();
        $this->actingAs($other)->get('/cuadrillas/'.$order->id_orden)->assertNotFound();
        $this->actingAs($other)->post('/cuadrillas/'.$order->id_orden.'/avance', [])->assertNotFound();
    }

    public function test_crea_reporte_y_muestra_su_detalle_con_texto_escapado(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $user = User::factory()->create(['email_tipo' => 'alumno']);
        $existing = Solicitud::firstOrFail();
        $text = '<script>alert(1)</script> Falla de iluminación en el aula.';
        $response = $this->actingAs($user)->post('/reportes', ['id_edificio' => $existing->espacio->id_edificio, 'id_espacio' => $existing->id_espacio, 'id_categoria' => $existing->id_categoria, 'fecha_hallazgo' => now()->format('Y-m-d H:i'), 'descripcion_falla' => $text]);
        $new = Solicitud::where('id_usuario', $user->id)->firstOrFail();
        $response->assertRedirect('/reportes/'.$new->id_solicitud);
        $this->assertDatabaseHas('ordenes_trabajo', ['id_solicitud' => $new->id_solicitud, 'estado' => 'abierta']);
        $this->get('/reportes/'.$new->id_solicitud)->assertOk()->assertSee($text)->assertDontSee($text, false);
    }

    public function test_rechaza_ubicacion_que_no_pertenece_al_edificio(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $report = Solicitud::firstOrFail();
        $otherBuilding = $report->espacio->edificio->plantel->edificios()->create(['nombre' => 'Otro edificio']);
        $this->actingAs($this->user('alumno'))->from('/reportes/nuevo')->post('/reportes', ['id_edificio' => $otherBuilding->id_edificio, 'id_espacio' => $report->id_espacio, 'id_categoria' => $report->id_categoria, 'fecha_hallazgo' => now()->format('Y-m-d H:i'), 'descripcion_falla' => 'Descripción válida para otro reporte.'])->assertSessionHasErrors('descripcion_falla');
        $this->assertDatabaseCount('solicitudes', 3);
    }

    public function test_filtra_reportes_sin_incluir_los_de_otros_usuarios(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $this->actingAs($this->user('alumno'))->get('/reportes?q=parpadea')->assertOk()->assertSee('parpadea')->assertDontSee('Revisión de luminarias');
        $this->get('/reportes?q=clase')->assertOk()->assertDontSee('se apaga durante la clase');
    }

    public function test_agrupa_solo_los_reportes_confirmados(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $base = Solicitud::where('descripcion_falla', 'like', '%parpadea%')->firstOrFail();
        $child = Solicitud::where('descripcion_falla', 'like', '%durante la clase%')->firstOrFail();
        $this->actingAs($this->user('coordinador'))->from('/coordinacion/duplicados')->post('/coordinacion/duplicados', ['padre' => $base->id_solicitud, 'hijas' => [$child->id_solicitud], 'confirmacion' => '1'])->assertRedirect('/coordinacion/duplicados')->assertSessionHas('status');
        $this->assertDatabaseHas('solicitudes', ['id_solicitud' => $child->id_solicitud, 'id_solicitud_padre' => $base->id_solicitud]);
        $this->assertDatabaseHas('ordenes_trabajo', ['id_solicitud' => $child->id_solicitud, 'estado' => 'cancelada_duplicada']);
        $this->actingAs($this->user('profesor'))->get('/reportes/'.$child->id_solicitud)->assertOk()->assertSee('Seguimiento vinculado');
    }

    public function test_rechaza_agrupar_un_reporte_con_sigo_mismo(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $base = Solicitud::firstOrFail();
        $this->actingAs($this->user('coordinador'))->post('/coordinacion/duplicados', ['padre' => $base->id_solicitud, 'hijas' => [$base->id_solicitud], 'confirmacion' => '1'])->assertSessionHasErrors('hijas.0');
        $this->assertNull($base->fresh()->id_solicitud_padre);
    }

    public function test_asigna_una_orden_y_guarda_instrucciones(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::whereNull('id_tecnico')->firstOrFail();
        $this->actingAs($this->user('coordinador'))->post('/coordinacion/asignar', ['id_orden' => $order->id_orden, 'id_tecnico' => $this->user('tecnico')->id, 'turno' => 'Matutino', 'horas' => 3, 'instrucciones' => 'Revisar luminarias del aula.', 'monto' => 500])->assertRedirect('/coordinacion/incidencias');
        $this->assertDatabaseHas('ordenes_trabajo', ['id_orden' => $order->id_orden, 'estado' => 'asignada']);
        $this->actingAs($this->user('tecnico'))->get('/cuadrillas/'.$order->id_orden)->assertOk()->assertSee('Revisar luminarias del aula.');
    }

    public function test_autoriza_inicia_y_cierra_con_evidencia_una_orden(): void
    {
        Storage::fake('public');
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::where('estado', 'requiere_aprobacion')->firstOrFail();
        $this->actingAs($this->user('coordinador'))->post('/coordinacion/autorizaciones/'.$order->id_orden, ['decision' => 'aprobar', 'motivo' => 'Presupuesto y seguridad revisados.'])->assertSessionHas('status');
        $this->actingAs($this->user('tecnico'))->get('/cuadrillas/'.$order->id_orden)->assertOk();
        $this->post('/cuadrillas/'.$order->id_orden.'/avance', ['fase' => 'pruebas', 'horas' => 2, 'notas' => 'Reparación y pruebas de iluminación.'])->assertSessionHas('status');
        $this->assertSame('en_proceso', $order->fresh()->estado);
        $this->get('/cuadrillas/'.$order->id_orden.'/cierre')->assertOk()->assertSee('Evidencia final');
        $this->post('/cuadrillas/'.$order->id_orden.'/cierre', ['resumen' => 'Se sustituyó la luminaria y se realizaron pruebas.', 'evidencia' => $this->photo(), 'confirmacion' => '1'])->assertRedirect('/cuadrillas/'.$order->id_orden);
        $this->assertSame('resuelta', $order->fresh()->estado);
        $this->assertDatabaseHas('reportes_finales', ['id_orden' => $order->id_orden]);
        $this->assertCount(1, $order->evidenciasCierre);
        $this->assertCount(1, Storage::disk('public')->allFiles());
        $this->get('/cuadrillas/'.$order->id_orden)->assertOk()->assertSee('Constancia de cierre');
    }

    public function test_cierre_sin_evidencia_no_resuelve_la_orden(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::whereNotNull('id_tecnico')->firstOrFail();
        $this->actingAs($this->user('tecnico'))->post('/cuadrillas/'.$order->id_orden.'/cierre', ['resumen' => 'Trabajo finalizado correctamente.', 'confirmacion' => '1'])->assertSessionHasErrors('evidencia');
        $this->assertSame('requiere_aprobacion', $order->fresh()->estado);
    }

    public function test_solicita_material_entrega_y_evade_el_doble_descuento(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::whereNotNull('id_tecnico')->firstOrFail();
        $order->update(['requiere_aprobacion' => false, 'estado' => 'asignada']);
        $material = Material::where('codigo_qr_barras', 'SIMIC-DEMO-E01')->firstOrFail();
        $this->actingAs($this->user('tecnico'))->get('/cuadrillas/'.$order->id_orden.'/materiales')->assertOk();
        $this->post('/cuadrillas/'.$order->id_orden.'/materiales', ['id_material' => $material->id_material, 'cantidad' => 2, 'urgencia' => 'normal', 'justificacion' => 'Sustituir los componentes averiados.'])->assertRedirect('/cuadrillas/'.$order->id_orden);
        $req = BitacoraSistema::where('accion', 'SOLICITUD_INSUMOS_TECNICO')->firstOrFail();
        $this->actingAs($this->user('almacen'))->get('/almacen/solicitudes')->assertOk()->assertSee('Preparar entrega');
        $path = '/almacen/solicitudes/'.$req->id_bitacora;
        $this->get($path)->assertOk()->assertSee('Vale de salida');
        $this->from($path)->post($path, ['receptor' => 'Técnico de demostración', 'confirmacion' => '1'])->assertSessionHas('status');
        $this->assertSame('1.00', $material->existencia->fresh()->cantidad_disponible);
        $this->post($path, ['receptor' => 'Técnico de demostración', 'confirmacion' => '1'])->assertSessionHasErrors('confirmacion');
        $this->assertSame('1.00', $material->existencia->fresh()->cantidad_disponible);
        $this->assertDatabaseCount('movimientos_material', 1);
        $this->get($path)->assertOk()->assertSee('Entrega registrada');
    }

    public function test_entrega_sin_stock_no_genera_vale_ni_descuenta_existencias(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::whereNotNull('id_tecnico')->firstOrFail();
        $order->update(['requiere_aprobacion' => false, 'estado' => 'asignada']);
        $material = Material::where('codigo_qr_barras', 'SIMIC-DEMO-E01')->firstOrFail();
        $req = BitacoraSistema::create(['id_usuario' => $this->user('tecnico')->id, 'accion' => 'SOLICITUD_INSUMOS_TECNICO', 'descripcion' => json_encode(['id_orden' => $order->id_orden, 'items' => [['id_material' => $material->id_material, 'cantidad' => 50]]]), 'fecha' => now()]);
        $this->actingAs($this->user('almacen'))->post('/almacen/solicitudes/'.$req->id_bitacora, ['receptor' => 'Receptor', 'confirmacion' => '1'])->assertSessionHasErrors('confirmacion');
        $this->assertSame('3.00', $material->existencia->fresh()->cantidad_disponible);
        $this->assertDatabaseCount('movimientos_material', 0);
        $this->assertDatabaseMissing('bitacora_sistema', ['accion' => 'ENTREGA_REQUISICION']);
    }

    public function test_entrada_actualiza_stock_y_exporta_el_periodo_filtrado(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $material = Material::where('codigo_qr_barras', 'SIMIC-DEMO-E01')->firstOrFail();
        $this->actingAs($this->user('almacen'))->post('/almacen/entradas', ['id_material' => $material->id_material, 'cantidad' => 12, 'concepto' => 'Compra institucional', 'referencia' => 'FAC-123'])->assertRedirect('/almacen');
        $this->assertSame('15.00', $material->existencia->fresh()->cantidad_disponible);
        $this->get('/almacen?stock=bajo')->assertOk()->assertSee('Balastro electrónico');
        $this->get('/almacen/bitacora?tipo=entrada')->assertOk()->assertSee('Balastro electrónico');
        $csv = $this->get('/almacen/bitacora/exportar?tipo=entrada');
        $csv->assertDownload('bitacora-almacen.csv');
        $this->assertStringContainsString('Balastro electrónico', $csv->streamedContent());
        $this->get('/almacen/bitacora/exportar?tipo=salida')->assertDownload()->assertStreamedContent("\xEF\xBB\xBFFolio,Fecha,Material,Movimiento,Cantidad,Responsable\n");
        $this->get('/almacen/bitacora/exportar?formato=pdf')->assertOk()->assertSee('Imprimir / guardar como PDF');
    }

    public function test_rechaza_entrada_negativa(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $material = Material::firstOrFail();
        $this->actingAs($this->user('almacen'))->post('/almacen/entradas', ['id_material' => $material->id_material, 'cantidad' => -1, 'concepto' => 'Compra institucional', 'referencia' => 'FAC-123'])->assertSessionHasErrors('cantidad');
        $this->assertDatabaseCount('movimientos_material', 0);
    }

    private function user(string $type): User
    {
        return User::where('email_tipo', $type)->firstOrFail();
    }

    public function test_completa_tareas_propias_y_rechaza_tareas_de_otra_orden(): void
    {
        $this->seed(SimicDemoSeeder::class);
        $order = OrdenTrabajo::whereNotNull('id_tecnico')->firstOrFail();
        $order->update(['estado' => 'en_proceso', 'requiere_aprobacion' => false]);
        $task = $order->tareas()->create(['descripcion_tarea' => 'Revisar conexión eléctrica', 'completada' => false]);
        $this->actingAs($this->user('tecnico'))->get('/cuadrillas/'.$order->id_orden)->assertOk()->assertSee('Completar tarea');
        $this->post('/cuadrillas/'.$order->id_orden.'/tareas/'.$task->id_tarea, ['completada' => 1])->assertSessionHas('status');
        $this->assertTrue($task->fresh()->completada);
        $other = OrdenTrabajo::whereNull('id_tecnico')->firstOrFail();
        $otherTask = $other->tareas()->create(['descripcion_tarea' => 'Tarea ajena', 'completada' => false]);
        $this->post('/cuadrillas/'.$order->id_orden.'/tareas/'.$otherTask->id_tarea, ['completada' => 1])->assertNotFound();
        $this->assertFalse($otherTask->fresh()->completada);
    }

    private function photo(): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jN1kAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent('despues.png', $bytes);
    }
}
