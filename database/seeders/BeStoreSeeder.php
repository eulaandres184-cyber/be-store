<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BeStoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Comercio
        $comercioId = DB::table('comercios')->insertGetId([
            'nombre'     => 'BE Store',
            'cuit'       => '',
            'plan'       => 'basico',
            'activo'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Usuario admin
        DB::table('users')->insert([
            'name'       => 'Administrador',
            'email'      => 'admin@bestore.com',
            'password'   => Hash::make('BeStore2024'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Configuración inicial
        DB::table('configuracion')->insert([
            'comercio_id'        => $comercioId,
            'recargo_tarjeta'    => 15.00,
            'cuotas_4_recargo'   => 2.00,
            'cuotas_20_recargo'  => 20.00,
            'dolar_blue_hoy'     => 0,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 4. Categorías reales de BE Store
        $categorias = [
            // Accesorios
            ['nombre' => 'Fundas',             'tipo' => 'accesorio', 'orden' => 1],
            ['nombre' => 'Templados',           'tipo' => 'accesorio', 'orden' => 2],
            ['nombre' => 'Cables',              'tipo' => 'accesorio', 'orden' => 3],
            ['nombre' => 'Cargadores',          'tipo' => 'accesorio', 'orden' => 4],
            ['nombre' => 'Auriculares',         'tipo' => 'accesorio', 'orden' => 5],
            ['nombre' => 'AirPods',             'tipo' => 'accesorio', 'orden' => 6],
            ['nombre' => 'Parlantes',           'tipo' => 'accesorio', 'orden' => 7],
            ['nombre' => 'Relojes',             'tipo' => 'accesorio', 'orden' => 8],
            ['nombre' => 'Soportes',            'tipo' => 'accesorio', 'orden' => 9],
            ['nombre' => 'Luces',               'tipo' => 'accesorio', 'orden' => 10],
            ['nombre' => 'Inflables',           'tipo' => 'accesorio', 'orden' => 11],
            ['nombre' => 'Adaptadores',         'tipo' => 'accesorio', 'orden' => 12],
            ['nombre' => 'Accesorios varios',   'tipo' => 'accesorio', 'orden' => 13],
            // Equipos
            ['nombre' => 'Celulares',           'tipo' => 'equipo',    'orden' => 14],
            ['nombre' => 'iPhones',             'tipo' => 'equipo',    'orden' => 15],
            ['nombre' => 'Usados',              'tipo' => 'equipo',    'orden' => 16],
            ['nombre' => 'Xiaomi',              'tipo' => 'equipo',    'orden' => 17],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias')->insert([
                'comercio_id' => $comercioId,
                'nombre'      => $cat['nombre'],
                'tipo'        => $cat['tipo'],
                'orden'       => $cat['orden'],
                'activo'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 5. Modelos de celular más comunes
        $marcas = [
            'iPhone'   => ['11', '12', '12 Pro', '13', '13 Pro', '13 Pro Max',
                           '14', '14 Pro', '14 Pro Max', '15', '15 Pro', '15 Pro Max',
                           '16', '16 Pro', '16 Pro Max', '16e', '17'],
            'Samsung'  => ['A15', 'A25', 'A35', 'A55', 'S23', 'S24', 'S24 Ultra',
                           'S25', 'S25 Ultra'],
            'Motorola' => ['G54', 'G84', 'G85', 'Edge 50', 'Edge 50 Pro'],
            'Xiaomi'   => ['Redmi 13', 'Redmi Note 13', 'Redmi Note 13 Pro', '14C'],
        ];

        foreach ($marcas as $marca => $modelos) {
            foreach ($modelos as $modelo) {
                DB::table('modelos_celular')->insert([
                    'comercio_id' => $comercioId,
                    'marca'       => $marca,
                    'modelo'      => $modelo,
                    'activo'      => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}