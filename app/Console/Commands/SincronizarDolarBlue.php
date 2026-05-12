<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Configuracion;
use App\Models\HistorialDolar;
use Illuminate\Support\Facades\Http;

class SincronizarDolarBlue extends Command
{
    protected $signature   = 'bestore:dolar';
    protected $description = 'Sincroniza la cotización del dólar blue desde bluelytics.com.ar';

    public function handle(): int
    {
        $this->info('Sincronizando dólar blue...');

        try {
            $response = Http::timeout(10)->get('https://api.bluelytics.com.ar/v2/latest');

            if (!$response->successful()) {
                $this->error('Error al conectar con la API.');
                return self::FAILURE;
            }

            $data        = $response->json();
            $valorVenta  = $data['blue']['value_sell'] ?? null;
            $valorCompra = $data['blue']['value_buy']  ?? null;

            if (!$valorVenta) {
                $this->error('No se pudo obtener el valor del dólar.');
                return self::FAILURE;
            }

            // Actualizar configuración de todos los comercios
            Configuracion::query()->update([
                'dolar_blue_hoy'       => $valorVenta,
                'dolar_actualizado_en' => now(),
            ]);

            // Guardar en historial
            HistorialDolar::updateOrCreate(
                ['fecha' => today()],
                [
                    'valor_compra' => $valorCompra ?? $valorVenta,
                    'valor_venta'  => $valorVenta,
                    'fuente'       => 'bluelytics',
                ]
            );

            $this->info("✅ Dólar blue actualizado: $" . number_format($valorVenta, 2));
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Excepción: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
