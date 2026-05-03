<?php

namespace Database\Factories;

use App\Models\Comercio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuracion>
 */
class ConfiguracionFactory extends Factory
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
            'recargo_tarjeta' => 15.00,
            'cuotas_4_recargo' => 2.00,
            'cuotas_20_recargo' => 20.00,
            'dolar_blue_hoy' => $this->faker->numberBetween(400, 500),
            'dolar_actualizado_en' => now(),
        ];
    }
}
