<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MensajeriaOperativaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, string}> */
    public static function direccionesOperativas(): array
    {
        return [
            'coordinación a técnico' => ['coordinador', 'tecnico'],
            'coordinación a almacén' => ['coordinador', 'almacen'],
            'técnico a coordinación' => ['tecnico', 'coordinador'],
            'técnico a almacén' => ['tecnico', 'almacen'],
            'almacén a coordinación' => ['almacen', 'coordinador'],
            'almacén a técnico' => ['almacen', 'tecnico'],
        ];
    }

    #[DataProvider('direccionesOperativas')]
    public function test_entrega_y_marca_como_leido_al_abrir_la_conversacion(string $origen, string $destino): void
    {
        $remitente = User::factory()->create(['email_tipo' => $origen]);
        $destinatario = User::factory()->create(['email_tipo' => $destino]);
        $mensaje = 'El material está disponible.';

        $this->actingAs($remitente)
            ->post(route('mensajes.store', $destinatario), ['mensaje' => $mensaje])
            ->assertRedirect(route('mensajes.index', ['contacto' => $destinatario->id]));

        $this->get(route('mensajes.index', ['contacto' => $destinatario->id]))
            ->assertOk()->assertSee('message-outgoing')->assertSee($mensaje);

        $this->actingAs($destinatario)->getJson(route('mensajes.pendientes'))->assertExactJson(['total' => 1]);
        $this->get(route('mensajes.index'))->assertOk()
            ->assertSee('data-contact-unread="'.$remitente->id.'"', false)
            ->assertDontSee($mensaje);
        $this->get(route('mensajes.index', ['contacto' => $remitente->id]))
            ->assertOk()->assertSee($mensaje)->assertDontSee('data-contact-unread', false);
        $this->getJson(route('mensajes.pendientes'))->assertExactJson(['total' => 0]);
    }

    public function test_el_total_suma_conversaciones_y_solo_se_limpia_la_conversacion_abierta(): void
    {
        $coordinador = User::factory()->create(['email_tipo' => 'coordinador']);
        $almacen = User::factory()->create(['email_tipo' => 'almacen']);
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);

        $this->actingAs($coordinador)->post(route('mensajes.store', $tecnico), ['mensaje' => 'Aviso de coordinación'])->assertRedirect();
        $this->actingAs($almacen)->post(route('mensajes.store', $tecnico), ['mensaje' => 'Primer aviso de almacén'])->assertRedirect();
        $this->post(route('mensajes.store', $tecnico), ['mensaje' => 'Segundo aviso de almacén'])->assertRedirect();

        $this->actingAs($tecnico)->getJson(route('mensajes.pendientes'))->assertExactJson(['total' => 3]);
        $this->get(route('mensajes.index', ['contacto' => $coordinador->id]))->assertOk()
            ->assertSee('Aviso de coordinación')->assertDontSee('Primer aviso de almacén')
            ->assertSee('data-contact-unread="'.$almacen->id.'"', false);
        $this->getJson(route('mensajes.pendientes'))->assertExactJson(['total' => 2]);
    }

    public function test_los_solicitantes_no_pueden_acceder_a_mensajes_operativos(): void
    {
        $alumno = User::factory()->create(['email_tipo' => 'alumno']);
        $tecnico = User::factory()->create(['email_tipo' => 'tecnico']);

        $this->actingAs($alumno)->get(route('mensajes.index'))->assertForbidden();
        $this->getJson(route('mensajes.pendientes'))->assertForbidden();
        $this->actingAs($tecnico)->post(route('mensajes.store', $alumno), ['mensaje' => 'Fuera del canal'])->assertForbidden();
    }
}
