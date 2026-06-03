<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;

/**
 * Seeder de productos iniciales de BE Store.
 * Cargados manualmente desde la planilla Google Sheets.
 * Hoja: Fundas y Templados (primera hoja visible).
 *
 * Para actualizar precios usar: php artisan bestore:sync-sheets
 */
class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        $comercioId = 1;

        // ── FUNDAS ──────────────────────────────────────────────────────────
        $catFundas = Categoria::where('comercio_id', $comercioId)
            ->where('nombre', 'Fundas')->first();

        if ($catFundas) {
            $fundas = [
                ['nombre' => 'Puffer Case',                                     'precio' => 6900],
                ['nombre' => 'Transparente Borde Color iPhone MagSafe',         'precio' => 6900],
                ['nombre' => 'Transparente iPhone 11-12-13-14',                 'precio' => 6900],
                ['nombre' => 'Transparentes, Rígida y Diseño',                  'precio' => 7500],
                ['nombre' => 'Siliconcase sin logo',                            'precio' => 8500],
                ['nombre' => 'Fundas Labubu Capibara Stitch Nuevos',            'precio' => 9500],
                ['nombre' => 'Transparentes iPhone 17',                         'precio' => 8500],
                ['nombre' => 'Brillos Nuevas',                                  'precio' => 9500],
                ['nombre' => 'Librito',                                         'precio' => 9500],
                ['nombre' => 'Original iPhone del 6 al 15 Pro Max',            'precio' => 9500],
                ['nombre' => 'MagSafe Transparente',                            'precio' => 9500],
                ['nombre' => 'MagSafe Mate y Brillos Samsung y iPhone',         'precio' => 10500],
                ['nombre' => 'iPhone 16 Metalizadas y Originales',              'precio' => 12000],
                ['nombre' => 'Funda Soporte Conejito',                          'precio' => 12000],
                ['nombre' => 'Funda Metalizada Samsung S24',                    'precio' => 12000],
                ['nombre' => 'Funda Matte S25',                                 'precio' => 12000],
                ['nombre' => 'MagSafe Matte Cubre Cámara',                     'precio' => 14500],
                ['nombre' => 'Tornasolada, Magnetic Armor y Brillos iPhone',    'precio' => 14500],
                ['nombre' => 'Fundas AG Glass',                                 'precio' => 15900],
                ['nombre' => 'Fundas iPhone 17',                                'precio' => 16000],
            ];

            foreach ($fundas as $f) {
                $this->crearProducto($f['nombre'], $f['precio'], $catFundas->id, $comercioId);
            }
            $this->command->info('✓ ' . count($fundas) . ' fundas cargadas');
        }

        // ── TEMPLADOS ────────────────────────────────────────────────────────
        $catTemplados = Categoria::where('comercio_id', $comercioId)
            ->where('nombre', 'Templados')->first();

        if ($catTemplados) {
            $templados = [
                ['nombre' => 'Templado Común',                  'precio' => 2000],
                ['nombre' => 'Templado 9D - 11D',               'precio' => 4500],
                ['nombre' => 'Templado Glass Matte',            'precio' => 5500],
                ['nombre' => 'Templado Antiespía',              'precio' => 7500],
                ['nombre' => 'Glass Cámara iPhone',             'precio' => 5500],
                ['nombre' => 'Glass Cámara iPhone 16',          'precio' => 6900],
                ['nombre' => 'Hydrogel Premium',                'precio' => 15500],
                ['nombre' => 'Hydrogel Matte',                  'precio' => 15500],
                ['nombre' => 'Hydrogel Tablets',                'precio' => 28000],
            ];

            foreach ($templados as $t) {
                $this->crearProducto($t['nombre'], $t['precio'], $catTemplados->id, $comercioId);
            }
            $this->command->info('✓ ' . count($templados) . ' templados cargados');
        }
    }

    /**
     * Crea un producto si no existe ya con el mismo nombre y categoría.
     * Evita duplicados si el seeder se ejecuta más de una vez.
     */
    private function crearProducto(
        string $nombre,
        float  $precio,
        int    $categoriaId,
        int    $comercioId
    ): void {
        $existe = Producto::where('comercio_id', $comercioId)
            ->where('categoria_id', $categoriaId)
            ->where('nombre', $nombre)
            ->exists();

        if (!$existe) {
            Producto::create([
                'comercio_id'     => $comercioId,
                'categoria_id'    => $categoriaId,
                'nombre'          => $nombre,
                'precio_efectivo' => $precio,
                'moneda'          => 'ARS',
                'stock_actual'    => 0,
                'stock_minimo'    => 1,
                'activo'          => true,
                'codigo_interno'  => Producto::generarCodigoInterno(),
            ]);
        }
    }
}
