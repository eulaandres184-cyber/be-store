<?php

namespace Database\Factories;

use App\Models\Comercio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comercio = Comercio::factory();
        $subtotal = $this->faker->numberBetween(1000, 100000);
        $recargo = $this->faker->numberBetween(0, 20);
        $total = $subtotal * (1 + $recargo / 100);

        return [
            'comercio_id' => $comercio,
            'user_id' => User::factory(['comercio_id' => $comercio]),
            'fecha' => $this->faker->dateTimeBetween('-30 days'),
            'medio_pago' => $this->faker->randomElement(['efectivo', 'transferencia', 'tarjeta', 'cuotas_4', 'cuotas_20']),
            'subtotal_ars' => $subtotal,
            'recargo_aplicado' => $recargo,
            'total_ars' => $total,
            'dolar_blue_usado' => $this->faker->numberBetween(400, 500),
            'notas' => $this->faker->optional()->sentence(),
        ];
    }
}
