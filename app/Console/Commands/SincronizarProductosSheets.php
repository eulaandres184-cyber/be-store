<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Producto;
use App\Models\Categoria;

/**
 * Comando: bestore:sync-sheets
 *
 * Sincroniza productos y precios desde Google Sheets una vez por semana.
 *
 * La planilla de BE Store tiene la siguiente estructura por hoja:
 *   - Columna A: Nombre del producto (TIPO)
 *   - Columna B: Precio en efectivo (EFECTIVO)
 *   - Columna C: Precio con tarjeta (TARJETA) — se ignora, se calcula
 *   - Fila 2: Encabezados
 *   - Fila 3 en adelante: Datos
 *
 * Algunas hojas tienen DOS bloques de datos (col A-C y col E-G)
 * como Fundas y Templados que están en la misma hoja.
 *
 * Uso:
 *   php artisan bestore:sync-sheets           → sincroniza real
 *   php artisan bestore:sync-sheets --dry-run → simula sin guardar
 */
class SincronizarProductosSheets extends Command
{
    protected $signature   = 'bestore:sync-sheets {--dry-run : Simular sin guardar cambios}';
    protected $description = 'Sincroniza productos desde Google Sheets (semanal - lunes 8AM)';

    /** ID del Google Spreadsheet de BE Store */
    const SHEET_ID = '1TAW1iHp6SSRsnZfTzWrWCN7nZCZeSklUeDyx6JnP2KA';

    /**
     * Configuración de cada hoja:
     * 'gid'        → ID de la pestaña (de la URL de Google Sheets)
     * 'categoria'  → nombre de la categoría en la BD
     * 'col_nombre' → índice de columna del nombre (0=A, 1=B, 4=E...)
     * 'col_precio' → índice de columna del precio efectivo
     * 'bloque2'    → si tiene un segundo bloque de datos en la misma hoja
     */
    const HOJAS = [
        [
            'nombre'     => 'Fundas y Templados',
            'gid'        => '1169561459',
            'categoria'  => 'Fundas',
            'col_nombre' => 0,  // columna A
            'col_precio' => 1,  // columna B
            'bloque2'    => [
                'categoria'  => 'Templados',
                'col_nombre' => 4,  // columna E
                'col_precio' => 5,  // columna F
            ],
        ],
        ['nombre' => 'ADAPTADORES',          'gid' => '1350975065', 'categoria' => 'Adaptadores',      'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CABLES',               'gid' => '1764110107', 'categoria' => 'Cables',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CARGADORES COMPLETOS', 'gid' => '434900350',  'categoria' => 'Cargadores',       'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'AURICULARES',          'gid' => '1403895346', 'categoria' => 'Auriculares',      'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'AIRPODS ORIG',         'gid' => '1012691532', 'categoria' => 'AirPods',          'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CELULARES',            'gid' => '1881782097', 'categoria' => 'Celulares',        'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => "IPHONE's",             'gid' => '1421289057', 'categoria' => 'iPhones',          'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'USADOS',               'gid' => '1058732665', 'categoria' => 'Usados',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'XIAOMI',               'gid' => '1664588590', 'categoria' => 'Xiaomi',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'PARLANTES',            'gid' => '1916611371', 'categoria' => 'Parlantes',        'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'RELOJES',              'gid' => '1524369639', 'categoria' => 'Relojes',          'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'SOPORTES',             'gid' => '1961428801', 'categoria' => 'Soportes',        'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'LUCES',                'gid' => '1284282165', 'categoria' => 'Luces',            'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'ACCESORIOS',           'gid' => '2070323337', 'categoria' => 'Accesorios varios','col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'INFLABLES',            'gid' => '1895611375', 'categoria' => 'Inflables',        'col_nombre' => 0, 'col_precio' => 1],
    ];

    private int  $creados      = 0;
    private int  $actualizados = 0;
    private int  $saltados     = 0;
    private int  $errores      = 0;
    private bool $dryRun       = false;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $inicio = now();

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   BE Store — Sync Google Sheets          ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info("Iniciado: {$inicio->format('d/m/Y H:i:s')}");

        if ($this->dryRun) {
            $this->warn('⚠ MODO DRY-RUN: no se guardarán cambios en la BD.');
        }

        $this->newLine();

        foreach (self::HOJAS as $hoja) {
            $this->procesarHoja($hoja);

            // Si tiene segundo bloque, procesarlo también
            if (isset($hoja['bloque2'])) {
                $this->procesarHoja(array_merge($hoja, $hoja['bloque2'], ['bloque2' => null]));
            }
        }

        $this->newLine();
        $this->info('══════════════════════════════════════════');
        $this->info("✅ Sincronización finalizada en " . now()->diffInSeconds($inicio) . "s");
        $this->info("   ➕ Creados:      {$this->creados}");
        $this->info("   ↑  Actualizados: {$this->actualizados}");
        $this->info("   ⏭  Saltados:     {$this->saltados}");

        if ($this->errores > 0) {
            $this->warn("   ❌ Errores:      {$this->errores}");
        }

        return self::SUCCESS;
    }

    /**
     * Descarga y procesa una hoja del spreadsheet.
     * Filtra filas vacías, sin precio o con precio cero.
     */
    private function procesarHoja(array $config): void
    {
        $nombreHoja   = $config['nombre'];
        $categoriaStr = $config['categoria'];
        $colNombre    = $config['col_nombre'];
        $colPrecio    = $config['col_precio'];
        $gid          = $config['gid'];

        $this->line("📋 {$nombreHoja} → {$categoriaStr}");

        // Buscar categoría en la BD (búsqueda flexible)
        $categoria = Categoria::where('comercio_id', 1)
            ->where(function($q) use ($categoriaStr) {
                $q->where('nombre', $categoriaStr)
                  ->orWhere('nombre', 'like', "%{$categoriaStr}%");
            })
            ->first();

        if (!$categoria) {
            $this->warn("   ⚠ Categoría '{$categoriaStr}' no encontrada. Saltando.");
            $this->errores++;
            return;
        }

        // Construir URL de exportación CSV
        $url = "https://docs.google.com/spreadsheets/d/" . self::SHEET_ID
             . "/export?format=csv&gid={$gid}";

        try {
            $response = Http::timeout(20)
                ->withHeaders(['Accept' => 'text/csv'])
                ->get($url);

            if (!$response->successful()) {
                $this->error("   ❌ Error HTTP {$response->status()}");
                $this->errores++;
                return;
            }

            $filas = $this->parsearCSV($response->body());

            if (empty($filas)) {
                $this->warn("   ⚠ Hoja vacía o sin datos legibles.");
                return;
            }

            // Saltar fila de encabezados (fila 2 en la planilla = índice 0 en el CSV)
            $count = 0;
            foreach ($filas as $i => $fila) {
                // Saltar fila de encabezados
                if ($i === 0) continue;

                $nombre = trim($fila[$colNombre] ?? '');
                $precio = $this->limpiarPrecio($fila[$colPrecio] ?? '');

                // Validaciones: saltar filas inválidas
                if (empty($nombre) || strlen($nombre) < 3)  { $this->saltados++; continue; }
                if ($nombre === 'TIPO' || $nombre === '-')   { $this->saltados++; continue; }
                if ($precio <= 0)                            { $this->saltados++; continue; }

                if ($this->dryRun) {
                    $this->line("   [DRY] {$nombre} → $" . number_format($precio, 0, ',', '.'));
                } else {
                    $this->upsertProducto($nombre, $precio, $categoria);
                }
                $count++;
            }

            $this->info("   ✓ {$count} productos procesados en '{$categoriaStr}'");

        } catch (\Exception $e) {
            $this->error("   ❌ " . $e->getMessage());
            $this->errores++;
        }
    }

    /**
     * Convierte el CSV crudo en array de arrays.
     * Maneja correctamente valores con comas dentro de comillas.
     */
    private function parsearCSV(string $csv): array
    {
        $filas = [];
        // str_getcsv con "\n" puede fallar en algunos sistemas
        // Usamos fgetcsv sobre un stream en memoria
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        while (($row = fgetcsv($stream)) !== false) {
            $filas[] = $row;
        }
        fclose($stream);

        return $filas;
    }

    /**
     * Limpia strings de precio como "$6.900,00" o "6900" → 6900.0
     * Maneja formatos argentinos (punto como separador de miles, coma como decimal)
     */
    private function limpiarPrecio(string $valor): float
    {
        if (empty(trim($valor))) return 0;

        // Quitar símbolo de peso, espacios y caracteres no numéricos excepto .,
        $limpio = preg_replace('/[^\d,.]/', '', $valor);

        if (empty($limpio)) return 0;

        // Formato argentino: $6.900,00 → quitar puntos, reemplazar coma por punto
        if (str_contains($limpio, ',')) {
            // Tiene coma → es separador decimal en formato argentino
            $limpio = str_replace('.', '', $limpio);   // quitar separadores de miles
            $limpio = str_replace(',', '.', $limpio);  // coma decimal → punto
        } elseif (substr_count($limpio, '.') === 1) {
            // Un solo punto → puede ser decimal o separador de miles
            $partes = explode('.', $limpio);
            if (strlen($partes[1]) === 3) {
                // Es separador de miles: 6.900
                $limpio = str_replace('.', '', $limpio);
            }
            // Si no, es decimal normal: 69.00
        }

        return (float) $limpio;
    }

    /**
     * Crea o actualiza un producto en la BD.
     * - Si existe por nombre+categoría → actualiza precio si cambió
     * - Si no existe → lo crea con código interno auto-generado
     *
     * No modifica stock ni otros campos para no pisar datos del operador.
     */
    private function upsertProducto(string $nombre, float $precio, Categoria $categoria): void
    {
        $producto = Producto::where('comercio_id', 1)
            ->where('categoria_id', $categoria->id)
            ->where('nombre', $nombre)
            ->first();

        if ($producto) {
            $precioActual = (float) $producto->precio_efectivo;

            if (abs($precioActual - $precio) > 0.01) {
                // Solo actualiza si el precio realmente cambió
                $producto->update(['precio_efectivo' => $precio]);
                $this->actualizados++;
                $this->line(sprintf(
                    "   ↑ %s: $%s → $%s",
                    $nombre,
                    number_format($precioActual, 0, ',', '.'),
                    number_format($precio, 0, ',', '.')
                ));
            } else {
                $this->saltados++;
            }
        } else {
            Producto::create([
                'comercio_id'     => 1,
                'categoria_id'    => $categoria->id,
                'nombre'          => $nombre,
                'precio_efectivo' => $precio,
                'moneda'          => 'ARS',
                'stock_actual'    => 0,
                'stock_minimo'    => 1,
                'activo'          => true,
                'codigo_interno'  => Producto::generarCodigoInterno(),
            ]);
            $this->creados++;
            $this->line("   ➕ " . $nombre . " → $" . number_format($precio, 0, ',', '.'));
        }
    }
}
