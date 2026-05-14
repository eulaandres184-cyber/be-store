<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Producto;
use App\Models\Categoria;

/**
 * Comando: SincronizarProductosSheets
 *
 * Sincroniza productos y precios desde Google Sheets una vez por semana.
 * Lee cada hoja del spreadsheet y actualiza/crea productos en la BD.
 *
 * Uso manual: php artisan bestore:sync-sheets
 * Automático: todos los lunes a las 8:00 AM (configurado en console.php)
 *
 * La planilla debe estar publicada como pública (solo lectura).
 * Sheet ID: 1TAW1iHp6SSRsnZfTzWrWCN7nZCZeSklUeDyx6JnP2KA
 */
class SincronizarProductosSheets extends Command
{
    protected $signature   = 'bestore:sync-sheets {--dry-run : Simular sin guardar}';
    protected $description = 'Sincroniza productos desde Google Sheets (semanal)';

    /** ID del spreadsheet de Google Sheets */
    const SHEET_ID = '1TAW1iHp6SSRsnZfTzWrWCN7nZCZeSklUeDyx6JnP2KA';

    /**
     * Mapeo de nombre de hoja → nombre de categoría en la BD.
     * La clave es el nombre exacto de la pestaña en Google Sheets.
     */
    const HOJAS = [
        'Fundas y Templados' => 'Fundas',
        'Templados'          => 'Templados',
        'Adaptadores'        => 'Adaptadores',
        'Cables'             => 'Cables',
        'Cargadores'         => 'Cargadores',
        'Auriculares'        => 'Auriculares',
        'AirPods'            => 'AirPods',
        'Celulares'          => 'Celulares',
        'iPhones'            => 'iPhones',
        'Usados'             => 'Usados',
        'Xiaomi'             => 'Xiaomi',
        'Parlantes'          => 'Parlantes',
        'Relojes'            => 'Relojes',
        'Soportes'           => 'Soportes',
        'Luces'              => 'Luces',
        'Accesorios'         => 'Accesorios varios',
        'Inflables'          => 'Inflables',
    ];

    /** IDs de cada hoja (gid). Se obtienen de la URL al hacer clic en cada pestaña */
    const GIDS = [
        'Fundas y Templados' => '1169561459',
        'Adaptadores'        => '0',
        'Cables'             => '1',
        'Cargadores'         => '2',
        'Auriculares'        => '3',
        'AirPods'            => '4',
        'Celulares'          => '5',
        'iPhones'            => '6',
        'Usados'             => '7',
        'Xiaomi'             => '8',
        'Parlantes'          => '9',
        'Relojes'            => '10',
        'Soportes'           => '11',
        'Luces'              => '12',
        'Accesorios'         => '13',
        'Inflables'          => '14',
    ];

    private int $creados     = 0;
    private int $actualizados = 0;
    private int $errores     = 0;
    private bool $dryRun     = false;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $this->info('=== Sincronización Google Sheets → BE Store ===');

        if ($this->dryRun) {
            $this->warn('MODO DRY-RUN: no se guardarán cambios.');
        }

        foreach (self::HOJAS as $nombreHoja => $nombreCategoria) {
            $this->procesarHoja($nombreHoja, $nombreCategoria);
        }

        $this->newLine();
        $this->info("✅ Sincronización completa:");
        $this->info("   Creados:      {$this->creados}");
        $this->info("   Actualizados: {$this->actualizados}");
        $this->error("   Errores:      {$this->errores}");

        return self::SUCCESS;
    }

    /**
     * Procesa una hoja del spreadsheet y sincroniza sus productos.
     *
     * @param string $nombreHoja      Nombre de la pestaña en Google Sheets
     * @param string $nombreCategoria Nombre de la categoría en la BD
     */
    private function procesarHoja(string $nombreHoja, string $nombreCategoria): void
    {
        $this->line("📋 Procesando: {$nombreHoja} → {$nombreCategoria}");

        // Buscar categoría en la BD
        $categoria = Categoria::where('comercio_id', 1)
            ->where('nombre', 'like', "%{$nombreCategoria}%")
            ->first();

        if (!$categoria) {
            $this->warn("   ⚠ Categoría '{$nombreCategoria}' no encontrada en BD. Saltando.");
            $this->errores++;
            return;
        }

        // Obtener GID de la hoja
        $gid = self::GIDS[$nombreHoja] ?? null;
        if (!$gid) {
            $this->warn("   ⚠ GID no configurado para '{$nombreHoja}'. Saltando.");
            return;
        }

        // Descargar CSV de la hoja
        $url = sprintf(
            'https://docs.google.com/spreadsheets/d/%s/export?format=csv&gid=%s',
            self::SHEET_ID,
            $gid
        );

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                $this->error("   ❌ Error HTTP {$response->status()} al descargar '{$nombreHoja}'");
                $this->errores++;
                return;
            }

            $filas = $this->parsearCSV($response->body());
            $this->sincronizarFilas($filas, $categoria);

        } catch (\Exception $e) {
            $this->error("   ❌ Excepción: " . $e->getMessage());
            $this->errores++;
        }
    }

    /**
     * Convierte el body CSV en un array de filas asociativas.
     * La primera fila se usa como encabezados.
     *
     * @param string $csv Contenido CSV crudo
     * @return array Array de arrays asociativos
     */
    private function parsearCSV(string $csv): array
    {
        $lineas = str_getcsv($csv, "\n");
        if (count($lineas) < 2) return [];

        // Primera fila = encabezados
        $headers = str_getcsv(array_shift($lineas));
        $headers = array_map('trim', $headers);

        $filas = [];
        foreach ($lineas as $linea) {
            if (empty(trim($linea))) continue;
            $valores = str_getcsv($linea);
            // Asegurar que tenga la misma cantidad de columnas que los headers
            while (count($valores) < count($headers)) {
                $valores[] = '';
            }
            $filas[] = array_combine($headers, $valores);
        }

        return $filas;
    }

    /**
     * Sincroniza las filas del CSV con la base de datos.
     * Detecta automáticamente las columnas de nombre y precio.
     *
     * @param array     $filas     Array de filas del CSV
     * @param Categoria $categoria Categoría a asignar
     */
    private function sincronizarFilas(array $filas, Categoria $categoria): void
    {
        if (empty($filas)) {
            $this->warn('   ⚠ Hoja vacía o sin datos.');
            return;
        }

        // Detectar columnas automáticamente (puede variar entre hojas)
        $primeraFila = $filas[0];
        $columnas    = array_keys($primeraFila);

        $colNombre = $this->detectarColumna($columnas, ['producto', 'nombre', 'descripcion', 'item', 'articulo']);
        $colPrecio = $this->detectarColumna($columnas, ['precio', 'efectivo', 'cash', 'price', 'valor']);

        if (!$colNombre || !$colPrecio) {
            $this->warn("   ⚠ No se detectaron columnas de nombre/precio. Columnas: " . implode(', ', $columnas));
            return;
        }

        $this->line("   Columnas: nombre='{$colNombre}', precio='{$colPrecio}'");

        $sincronizados = 0;
        foreach ($filas as $fila) {
            $nombre = trim($fila[$colNombre] ?? '');
            $precio = $this->limpiarPrecio($fila[$colPrecio] ?? '');

            // Saltar filas sin nombre o precio inválido
            if (empty($nombre) || $nombre === '-' || strlen($nombre) < 3) continue;
            if ($precio <= 0) continue;

            if (!$this->dryRun) {
                $this->upsertProducto($nombre, $precio, $categoria);
            } else {
                $this->line("   [DRY] {$nombre} → $" . number_format($precio, 0, ',', '.'));
            }

            $sincronizados++;
        }

        $this->info("   ✓ {$sincronizados} productos procesados");
    }

    /**
     * Detecta qué columna del CSV corresponde a un campo buscado.
     * Compara en minúsculas y busca coincidencias parciales.
     *
     * @param array $columnas  Nombres de columnas disponibles
     * @param array $candidatos Posibles nombres a buscar
     */
    private function detectarColumna(array $columnas, array $candidatos): ?string
    {
        foreach ($columnas as $col) {
            $colLower = strtolower(trim($col));
            foreach ($candidatos as $candidato) {
                if (str_contains($colLower, $candidato)) {
                    return $col;
                }
            }
        }
        return null;
    }

    /**
     * Limpia y convierte un string de precio a float.
     * Maneja formatos como "$1.500", "1500,00", "1.500,00".
     */
    private function limpiarPrecio(string $valor): float
    {
        // Eliminar símbolos de moneda, espacios y caracteres no numéricos
        $limpio = preg_replace('/[^\d,.]/', '', $valor);

        // Si tiene coma como separador decimal (1.500,00)
        if (str_contains($limpio, ',')) {
            $limpio = str_replace('.', '', $limpio);
            $limpio = str_replace(',', '.', $limpio);
        } else {
            // Si tiene punto como separador de miles (1.500)
            if (substr_count($limpio, '.') === 1 && strlen(explode('.', $limpio)[1]) === 3) {
                $limpio = str_replace('.', '', $limpio);
            }
        }

        return (float) $limpio;
    }

    /**
     * Crea o actualiza un producto en la base de datos.
     * Si ya existe por nombre y categoría, solo actualiza el precio.
     * Si no existe, lo crea con código interno generado automáticamente.
     */
    private function upsertProducto(string $nombre, float $precio, Categoria $categoria): void
    {
        $producto = Producto::where('comercio_id', 1)
            ->where('categoria_id', $categoria->id)
            ->where('nombre', $nombre)
            ->first();

        if ($producto) {
            // Solo actualiza si el precio cambió
            if (abs((float)$producto->precio_efectivo - $precio) > 0.01) {
                $producto->update(['precio_efectivo' => $precio]);
                $this->actualizados++;
                $this->line("   ↑ Actualizado: {$nombre} → $" . number_format($precio, 0, ',', '.'));
            }
        } else {
            Producto::create([
                'comercio_id'    => 1,
                'categoria_id'   => $categoria->id,
                'nombre'         => $nombre,
                'precio_efectivo'=> $precio,
                'moneda'         => 'ARS',
                'stock_actual'   => 0,
                'stock_minimo'   => 1,
                'activo'         => true,
                'codigo_interno' => Producto::generarCodigoInterno(),
            ]);
            $this->creados++;
            $this->line("   + Creado: {$nombre} → $" . number_format($precio, 0, ',', '.'));
        }
    }
}
