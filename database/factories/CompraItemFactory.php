<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompraItem>
 */
class CompraItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = $this->faker->numberBetween(1, 50);
        $costoUnitario = $this->faker->numberBetween(500, 15000);
        $subtotal = $cantidad * $costoUnitario;

        return [
            'compra_id' => Compra::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => $cantidad,
            'costo_unitario_ars' => $costoUnitario,
            'subtotal_ars' => $subtotal,
        ];
    }
}
