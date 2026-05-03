<?php

namespace Database\Factories;

use App\Models\Comercio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModeloCelular>
 */
class ModeloCelularFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $marcas = ['Apple', 'Samsung', 'Motorola', 'Xiaomi', 'Huawei', 'Nokia', 'LG'];

        return [
            'comercio_id' => Comercio::factory(),
            'marca' => $this->faker->randomElement($marcas),
            'modelo' => $this->faker->numerify('##A#'),
            'activo' => true,
        ];
    }
}
