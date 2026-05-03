<?php

namespace Database\Factories;

use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VentaItem>
 */
class VentaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = $this->faker->numberBetween(1, 10);
        $precioUnitario = $this->faker->numberBetween(1000, 20000);
        $subtotal = $cantidad * $precioUnitario;

        return [
            'venta_id' => Venta::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => $cantidad,
            'precio_unitario_ars' => $precioUnitario,
            'subtotal_ars' => $subtotal,
        ];
    }
}
