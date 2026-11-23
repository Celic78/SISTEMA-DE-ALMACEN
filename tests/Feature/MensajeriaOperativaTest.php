<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensajeriaOperativaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_ya_no_ofrece_mensajeria_operativa(): void
    {
        $this->seed();
        $coordinador = User::where('email_tipo', 'coordinador')->firstOrFail();
        $tecnico = User::where('email_tipo', 'tecnico')->firstOrFail();

        $this->actingAs($coordinador)->get(route('coordinacion.registros'))
            ->assertOk()->assertDontSee('/mensajes', false);
        $this->get('/mensajes')->assertNotFound();
        $this->getJson('/mensajes/pendientes')->assertNotFound();
        $this->post('/mensajes/'.$tecnico->getKey(), ['mensaje' => 'Aviso'])->assertNotFound();
    }
}
