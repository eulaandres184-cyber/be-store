<?php

namespace Database\Factories;

use App\Models\Comercio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comercio_id' => Comercio::factory(),
            'nombre' => $this->faker->word(),
            'tipo' => $this->faker->randomElement(['accesorio', 'celular', 'servicio']),
            'orden' => $this->faker->numberBetween(1, 10),
            'activo' => true,
        ];
    }
}
