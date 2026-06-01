<?php
namespace App\Services;

use Afip;
use App\Models\Comercio;
use App\Models\Documento;
use Illuminate\Support\Facades\Log;

/**
 * AfipService — Integración con ARCA (ex AFIP) para generación de CAE
 *
 * Documentación oficial: https://afipsdk.github.io/afip.php/
 *
 * REQUISITOS PREVIOS:
 *   1. Certificado digital AFIP (.crt y .key) en storage/afip/
 *   2. CUIT habilitado para Facturación Electrónica (WSFE)
 *   3. Punto de venta dado de alta como "Web Services" en AFIP
 *
 * Para testing usar: $produccion = false (sandbox de AFIP)
 * Para producción:   $produccion = true
 */
class AfipService
{
    private $afip;
    private Comercio $comercio;

    public function __construct()
    {
        $this->comercio = Comercio::find(1);

        $this->afip = new Afip([
            // CUIT del emisor (sin guiones)
            'CUIT' => (int) str_replace(['-', '.'], '', $this->comercio->cuit ?? '20347359476'),

            // true = producción real, false = homologación (testing)
            'production' => config('afip.produccion', false),

            // Ruta al certificado digital (.crt)
            'cert' => storage_path('afip/certificado.crt'),

            // Ruta a la clave privada (.key)
            'key'  => storage_path('afip/clave_privada.key'),

            // Directorio para archivos temporales de AFIP
            'res_folder' => storage_path('afip/'),
        ]);
    }

    /**
     * Genera el CAE para una Factura C.
     * Llama al Web Service WSFE de AFIP.
     *
     * @param  Documento $documento  El documento ya creado en la BD
     * @return array ['cae' => '...', 'vencimiento' => '...']
     * @throws \Exception si AFIP rechaza la solicitud
     */
    public function generarCAE(Documento $documento): array
    {
        // Obtener el último número de comprobante autorizado por AFIP
        $ultimoNro = $this->afip->ElectronicBilling->GetLastVoucher(
            (int) $this->comercio->punto_venta,
            11  // Tipo 11 = Factura C
        );

        $nroComprobante = $ultimoNro + 1;

        // Armar el array de ítems para AFIP
        $items = collect($documento->items)->map(fn($item, $i) => [
            'Id'          => $i + 1,
            'Ds'          => substr($item['nombre'], 0, 200), // máx 200 chars
            'Qty'         => (float)($item['cantidad'] ?? 1),
            'Umed'        => 7,           // 7 = Unidades
            'PrecioUnitario' => round((float)($item['precio_unit'] ?? 0), 2),
            'BonificacionPorcentaje' => 0,
            'ImpBonificacion' => 0,
            'Subtotal'    => round((float)($item['subtotal'] ?? $item['precio_ars'] ?? 0), 2),
        ])->toArray();

        // Datos del receptor
        $datosCliente = null;
        if ($documento->observaciones) {
            $datosCliente = json_decode($documento->observaciones, true);
        }

        $data = [
            // Tipo de comprobante: 11 = Factura C
            'CantReg'     => 1,
            'PtoVta'      => (int) $this->comercio->punto_venta,
            'CbteTipo'    => 11,

            // Concepto: 1=Productos, 2=Servicios, 3=Productos y Servicios
            'Concepto'    => 1,

            // Condición IVA del receptor
            // 5 = Consumidor Final, 3 = Exento
            'DocTipo'     => $datosCliente['dni'] ?? null ? 96 : 99, // 96=DNI, 99=Consumidor Final
            'DocNro'      => $datosCliente['dni'] ? preg_replace('/\D/', '', $datosCliente['dni']) : 0,

            // Numeración
            'CbteDesde'   => $nroComprobante,
            'CbteHasta'   => $nroComprobante,
            'CbteFch'     => (int) now()->format('Ymd'),

            // Importes
            'ImpTotal'    => round((float)$documento->total, 2),
            'ImpTotConc'  => 0,    // No gravado
            'ImpNeto'     => round((float)$documento->total, 2),
            'ImpOpEx'     => 0,    // Exento
            'ImpIVA'      => 0,    // Factura C no discrimina IVA
            'ImpTrib'     => 0,    // Otros tributos

            // Moneda
            'MonId'       => 'PES',   // Pesos argentinos
            'MonCotiz'    => 1,

            // Ítems (opcional pero recomendado)
            'ItmDet'      => $items,
        ];

        // Llamar al Web Service de AFIP
        $resultado = $this->afip->ElectronicBilling->CreateVoucher($data);

        if (!isset($resultado['CAE'])) {
            $error = $resultado['Errors'][0]['Msg'] ?? 'Error desconocido de AFIP';
            Log::error('AFIP error al generar CAE', ['resultado' => $resultado, 'documento_id' => $documento->id]);
            throw new \Exception("AFIP rechazó la solicitud: {$error}");
        }

        $cae         = $resultado['CAE'];
        $vencimiento = $resultado['CAEFchVto']; // Formato AAAAMMDD

        // Formatear fecha de vencimiento
        $fechaVto = \Carbon\Carbon::createFromFormat('Ymd', $vencimiento)->format('d/m/Y');

        // Actualizar el documento con los datos del CAE
        $documento->update([
            'numero'       => str_pad($this->comercio->punto_venta, 4, '0', STR_PAD_LEFT)
                            . '-'
                            . str_pad($nroComprobante, 8, '0', STR_PAD_LEFT),
            'observaciones'=> json_encode(array_merge(
                json_decode($documento->observaciones ?? '{}', true) ?? [],
                [
                    'cae'            => $cae,
                    'cae_vencimiento'=> $fechaVto,
                    'nro_comprobante'=> $nroComprobante,
                ]
            )),
        ]);

        // Actualizar el contador del comercio
        $this->comercio->update(['ultimo_nro_factura' => $nroComprobante]);

        Log::info("CAE generado correctamente: {$cae} para doc #{$documento->id}");

        return [
            'cae'            => $cae,
            'vencimiento'    => $fechaVto,
            'nro_comprobante'=> $nroComprobante,
        ];
    }

    /**
     * Verifica si los servicios de AFIP están disponibles.
     * Útil para mostrar un mensaje antes de intentar facturar.
     */
    public function verificarServicio(): bool
    {
        try {
            $estado = $this->afip->ElectronicBilling->GetServerStatus();
            return ($estado['AppServer'] === 'OK' && $estado['DbServer'] === 'OK');
        } catch (\Exception $e) {
            Log::warning('AFIP no disponible: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Consulta un comprobante ya emitido por número.
     * Útil para verificar el estado de una factura.
     */
    public function consultarComprobante(int $nro): array
    {
        return $this->afip->ElectronicBilling->GetVoucherInfo(
            $nro,
            (int) $this->comercio->punto_venta,
            11 // Factura C
        );
    }
}
