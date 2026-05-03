<?php

namespace Database\Factories;

use App\Models\Comercio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proveedor>
 */
class ProveedorFactory extends Factory
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
            'nombre' => $this->faker->company(),
            'contacto' => $this->faker->name(),
            'whatsapp' => $this->faker->numerify('54 ## ########'),
            'notas' => $this->faker->optional()->sentence(),
            'activo' => true,
        ];
    }
}
