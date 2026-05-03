<?php

namespace Tests\Feature;

use App\Livewire\Productos\ListaProductos;
use App\Models\Comercio;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListaProductosTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_productos_muestra_productos_del_comercio(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);
        $categoria = Categoria::factory()->create(['comercio_id' => $comercio->id]);

        $productos = Producto::factory(5)->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'activo' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ListaProductos::class)
            ->assertViewHas('productos')
            ->call('render');
    }

    public function test_lista_productos_filtra_por_busqueda(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);
        $categoria = Categoria::factory()->create(['comercio_id' => $comercio->id]);

        Producto::factory()->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'iPhone 14',
            'activo' => true,
        ]);

        Producto::factory()->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Samsung Galaxy',
            'activo' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ListaProductos::class)
            ->set('busqueda', 'iPhone')
            ->assertCount(1, fn() => $this->productos)
            ->call('render');
    }

    public function test_lista_productos_solo_muestra_productos_activos(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);
        $categoria = Categoria::factory()->create(['comercio_id' => $comercio->id]);

        Producto::factory(3)->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'activo' => true,
        ]);

        Producto::factory(2)->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'activo' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ListaProductos::class)
            ->set('estado', 'activos')
            ->assertCount(3, fn() => $this->productos)
            ->call('render');
    }

    public function test_toggle_activo_producto(): void
    {
        $comercio = Comercio::factory()->create();
        $user = User::factory()->create(['comercio_id' => $comercio->id]);
        $categoria = Categoria::factory()->create(['comercio_id' => $comercio->id]);

        $producto = Producto::factory()->create([
            'comercio_id' => $comercio->id,
            'categoria_id' => $categoria->id,
            'activo' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ListaProductos::class)
            ->call('toggleActivo', $producto->id)
            ->assertSuccessful();

        $this->assertFalse($producto->fresh()->activo);
    }
}
