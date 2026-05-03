<?php

namespace Database\Factories;

use App\Models\Comercio;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comercio = Comercio::factory();

        return [
            'comercio_id' => $comercio,
            'proveedor_id' => Proveedor::factory(['comercio_id' => $comercio]),
            'user_id' => User::factory(['comercio_id' => $comercio]),
            'fecha' => $this->faker->dateTimeBetween('-30 days'),
            'total_ars' => $this->faker->numberBetween(5000, 200000),
            'notas' => $this->faker->optional()->sentence(),
        ];
    }
}
