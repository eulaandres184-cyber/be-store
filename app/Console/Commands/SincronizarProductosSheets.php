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
 * Estructura de la planilla BE Store:
 *   - Fila 2: encabezados (TIPO | EFECTIVO | TARJETA)
 *   - Fila 3+: datos de productos
 *   - Columna A: nombre del producto
 *   - Columna B: precio en efectivo
 *   - Hoja "FUNDAS Y TEMPLADOS": tiene dos bloques
 *       Bloque 1 (col A-C): Fundas
 *       Bloque 2 (col E-G): Templados
 *
 * GIDs obtenidos de la URL de Google Sheets (convertidos de base-36 a decimal).
 *
 * Uso:
 *   php artisan bestore:sync-sheets           → sincroniza real
 *   php artisan bestore:sync-sheets --dry-run → simula sin guardar
 */
class SincronizarProductosSheets extends Command
{
    protected $signature   = 'bestore:sync-sheets {--dry-run : Simular sin guardar cambios}';
    protected $description = 'Sincroniza productos desde Google Sheets (semanal - lunes 8AM)';

    const SHEET_ID = '1TAW1iHp6SSRsnZfTzWrWCN7nZCZeSklUeDyx6JnP2KA';

    /**
     * Configuración de hojas con GIDs reales (decimales).
     *
     * gid_hex → gid_decimal:
     *   y=34, 1e=50, 1u=66, 2a=82, 2q=98, 36=114,
     *   3m=130, 42=146, 4i=162, 4y=178, 5e=194,
     *   5u=210, 6a=226, 6q=242, 76=258, 7m=274
     *
     * col_nombre/col_precio: índice base 0 (A=0, B=1, E=4, F=5)
     */
    const HOJAS = [
        [
            'nombre'     => 'FUNDAS Y TEMPLADOS',
            'gid'        => '34',
            'categoria'  => 'Fundas',
            'col_nombre' => 0,
            'col_precio' => 1,
            'bloque2'    => [
                'categoria'  => 'Templados',
                'col_nombre' => 4,
                'col_precio' => 5,
            ],
        ],
        ['nombre' => 'ADAPTADORES',          'gid' => '50',  'categoria' => 'Adaptadores',       'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CABLES',               'gid' => '66',  'categoria' => 'Cables',            'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CARGADORES COMPLETOS', 'gid' => '82',  'categoria' => 'Cargadores',        'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'AURICULARES',          'gid' => '98',  'categoria' => 'Auriculares',       'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'AIRPODS ORIG',         'gid' => '114', 'categoria' => 'AirPods',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'CELULARES',            'gid' => '130', 'categoria' => 'Celulares',         'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => "IPHONE's",             'gid' => '146', 'categoria' => 'iPhones',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'USADOS',               'gid' => '162', 'categoria' => 'Usados',            'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'XIAOMI',               'gid' => '178', 'categoria' => 'Xiaomi',            'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'PARLANTES',            'gid' => '194', 'categoria' => 'Parlantes',         'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'RELOJES',              'gid' => '210', 'categoria' => 'Relojes',           'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'SOPORTES',             'gid' => '226', 'categoria' => 'Soportes',          'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'LUCES',                'gid' => '242', 'categoria' => 'Luces',             'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'ACCESORIOS',           'gid' => '258', 'categoria' => 'Accesorios varios', 'col_nombre' => 0, 'col_precio' => 1],
        ['nombre' => 'INFLABLES',            'gid' => '274', 'categoria' => 'Inflables',         'col_nombre' => 0, 'col_precio' => 1],
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
            // Procesar bloque principal
            $this->procesarHoja(
                $hoja['gid'],
                $hoja['nombre'],
                $hoja['categoria'],
                $hoja['col_nombre'],
                $hoja['col_precio']
            );

            // Procesar segundo bloque si existe (Fundas y Templados)
            if (isset($hoja['bloque2'])) {
                $b = $hoja['bloque2'];
                $this->procesarHoja(
                    $hoja['gid'],
                    $hoja['nombre'] . ' (bloque 2)',
                    $b['categoria'],
                    $b['col_nombre'],
                    $b['col_precio']
                );
            }
        }

        $this->newLine();
        $this->info('══════════════════════════════════════════');
        $segundos = now()->diffInSeconds($inicio);
        $this->info("✅ Finalizado en {$segundos}s");
        $this->info("   ➕ Creados:      {$this->creados}");
        $this->info("   ↑  Actualizados: {$this->actualizados}");
        $this->info("   ⏭  Saltados:     {$this->saltados}");

        if ($this->errores > 0) {
            $this->warn("   ❌ Errores:      {$this->errores}");
        }

        return self::SUCCESS;
    }

    /**
     * Descarga el CSV de una hoja y sincroniza los productos.
     *
     * @param string $gid         GID decimal de la hoja
     * @param string $nombreHoja  Nombre descriptivo para el log
     * @param string $categoriaStr Nombre de categoría en la BD
     * @param int    $colNombre   Índice de columna del nombre (0=A)
     * @param int    $colPrecio   Índice de columna del precio (1=B)
     */
    private function procesarHoja(
        string $gid,
        string $nombreHoja,
        string $categoriaStr,
        int    $colNombre,
        int    $colPrecio
    ): void {
        $this->line("📋 {$nombreHoja} → {$categoriaStr}");

        // Buscar categoría en la BD
        $categoria = Categoria::where('comercio_id', 1)
            ->where(function ($q) use ($categoriaStr) {
                $q->where('nombre', $categoriaStr)
                  ->orWhere('nombre', 'like', "%{$categoriaStr}%");
            })
            ->first();

        if (!$categoria) {
            $this->warn("   ⚠ Categoría '{$categoriaStr}' no encontrada. Saltando.");
            $this->errores++;
            return;
        }

        // URL de exportación CSV con el GID correcto
        $url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
            self::SHEET_ID,
            $gid
        );

        try {
            $response = Http::timeout(20)
                ->withHeaders(['Accept' => 'text/csv'])
                ->get($url);

            if (!$response->successful()) {
                $this->error("   ❌ Error HTTP {$response->status()} — GID: {$gid}");
                $this->errores++;
                return;
            }

            $filas = $this->parsearCSV($response->body());

            if (count($filas) < 2) {
                $this->warn("   ⚠ Sin datos suficientes en la hoja.");
                return;
            }

            $count = 0;
            foreach ($filas as $i => $fila) {
                // Fila 0 = encabezados → saltar
                if ($i === 0) continue;

                // Verificar que la fila tiene suficientes columnas
                if (!isset($fila[$colNombre]) || !isset($fila[$colPrecio])) {
                    $this->saltados++;
                    continue;
                }

                $nombre = trim($fila[$colNombre]);
                $precio = $this->limpiarPrecio($fila[$colPrecio]);

                // Validaciones
                if (empty($nombre) || strlen($nombre) < 3) { $this->saltados++; continue; }
                if (in_array(strtoupper($nombre), ['TIPO', '-', 'N/A', 'PRODUCTO'])) { $this->saltados++; continue; }
                if ($precio <= 0) { $this->saltados++; continue; }

                if ($this->dryRun) {
                    $this->line("   [DRY] {$nombre} → $" . number_format($precio, 0, ',', '.'));
                } else {
                    $this->upsertProducto($nombre, $precio, $categoria);
                }
                $count++;
            }

            $this->info("   ✓ {$count} productos en '{$categoriaStr}'");

        } catch (\Exception $e) {
            $this->error("   ❌ " . $e->getMessage());
            $this->errores++;
        }
    }

    /**
     * Parsea CSV usando fgetcsv para manejar correctamente
     * valores con comas dentro de comillas.
     */
    private function parsearCSV(string $csv): array
    {
        $filas  = [];
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
     * Convierte strings de precio en formato argentino a float.
     * Ejemplos: "$6.900,00" → 6900.0 | "15500" → 15500.0
     */
    private function limpiarPrecio(string $valor): float
    {
        $limpio = preg_replace('/[^\d,.]/', '', trim($valor));
        if (empty($limpio)) return 0;

        if (str_contains($limpio, ',')) {
            // Formato argentino: 6.900,00
            $limpio = str_replace('.', '', $limpio);
            $limpio = str_replace(',', '.', $limpio);
        } elseif (substr_count($limpio, '.') === 1) {
            $partes = explode('.', $limpio);
            if (strlen($partes[1]) === 3) {
                // Punto como separador de miles: 6.900
                $limpio = str_replace('.', '', $limpio);
            }
        }

        return (float) $limpio;
    }

    /**
     * Crea o actualiza un producto.
     * Solo actualiza el precio si cambió más de $0.01.
     * No toca stock ni código interno para preservar datos del operador.
     */
    private function upsertProducto(string $nombre, float $precio, Categoria $categoria): void
    {
        $producto = Producto::where('comercio_id', 1)
            ->where('categoria_id', $categoria->id)
            ->where('nombre', $nombre)
            ->first();

        if ($producto) {
            if (abs((float) $producto->precio_efectivo - $precio) > 0.01) {
                $producto->update(['precio_efectivo' => $precio]);
                $this->actualizados++;
                $this->line(sprintf(
                    "   ↑ %s: $%s → $%s",
                    $nombre,
                    number_format((float) $producto->precio_efectivo, 0, ',', '.'),
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
            $this->line("   ➕ {$nombre} → $" . number_format($precio, 0, ',', '.'));
        }
    }
}
