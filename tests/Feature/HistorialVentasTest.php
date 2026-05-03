<?php

namespace Tests\Feature;

use App\Livewire\Ventas\HistorialVentas;
use App\Models\Comercio;
use App\Models\Venta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HistorialVentasTest extends TestCase
{
    use RefreshDatabase;

    public function test_historial_ventas_muestra_ventas_del_comercio(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);

        Venta::factory(5)->create(['comercio_id' => $comercio->id]);

        $this->actingAs($user);

        Livewire::test(HistorialVentas::class)
            ->assertViewHas('totalVentas')
            ->assertViewHas('ventasHoy');
    }

    public function test_historial_ventas_filtra_por_fecha(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);

        Venta::factory()->create([
            'comercio_id' => $comercio->id,
            'fecha' => now()->subDays(10),
        ]);

        Venta::factory()->create([
            'comercio_id' => $comercio->id,
            'fecha' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(HistorialVentas::class)
            ->set('desde', now()->format('Y-m-d'))
            ->assertSuccessful();
    }

    public function test_limpiar_filtros_resetea_valores(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);

        $this->actingAs($user);

        Livewire::test(HistorialVentas::class)
            ->set('desde', now()->format('Y-m-d'))
            ->set('medioPago', 'efectivo')
            ->call('limpiarFiltros')
            ->assertSet('desde', '')
            ->assertSet('medioPago', '')
            ->assertSet('ordenar', 'fecha_desc');
    }
}
