<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EquipoDetalle>
 */
class EquipoDetalleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'imei' => $this->faker->numerify('##############'),
            'estado' => $this->faker->randomElement(['disponible', 'vendido', 'dañado']),
        ];
    }
}
