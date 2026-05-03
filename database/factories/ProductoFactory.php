<?php

namespace Database\Factories;

use App\Models\Comercio;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precioEfectivo = $this->faker->numberBetween(1000, 50000);

        return [
            'comercio_id' => Comercio::factory(),
            'categoria_id' => Categoria::factory(),
            'nombre' => $this->faker->word() . ' ' . $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'precio_efectivo' => $precioEfectivo,
            'precio_tarjeta' => $precioEfectivo * 1.15,
            'stock_actual' => $this->faker->numberBetween(0, 100),
            'stock_minimo' => $this->faker->numberBetween(5, 20),
            'activo' => true,
        ];
    }
}
