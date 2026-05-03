<?php

namespace Tests\Feature;

use App\Livewire\Configuracion as ConfiguracionComponent;
use App\Models\Comercio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuracion_component_can_save_data(): void
    {
        $comercio = Comercio::create([
            'nombre' => 'Comercio Test',
            'cuit' => '12345678901',
            'plan' => 'basico',
            'activo' => true,
        ]);

        $user = User::factory()->create([
            'comercio_id' => $comercio->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ConfiguracionComponent::class)
            ->set('recargo_tarjeta', 12.50)
            ->set('cuotas_4_recargo', 3.00)
            ->set('cuotas_20_recargo', 18.00)
            ->set('dolar_blue_hoy', 489.25)
            ->call('saveConfiguracion')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('configuracion', [
            'comercio_id' => $comercio->id,
            'recargo_tarjeta' => 12.50,
            'cuotas_4_recargo' => 3.00,
            'cuotas_20_recargo' => 18.00,
            'dolar_blue_hoy' => 489.25,
        ]);
    }
}
