<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder principal de BE Store.
 * Ejecuta los seeders en el orden correcto respetando dependencias.
 *
 * Uso: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Datos base del sistema (comercio, usuario, config, categorías)
        $this->call(BeStoreSeeder::class);

        // 2. Productos iniciales desde la planilla de precios
        $this->call(ProductosSeeder::class);
    }
}
