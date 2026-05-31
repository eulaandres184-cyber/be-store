<?php
namespace App\Services;

use App\Models\Comercio;
use App\Models\Documento;
use Exception;
use RuntimeException;
use SoapClient;
use SoapFault;

/**
 * AfipService
 *
 * Integración con los Web Services de AFIP-ARCA para obtener el
 * CAE (Código de Autorización Electrónico) al emitir Facturas Electrónicas.
 *
 * Flujo:
 *  1. WSAA — Autenticación: firma un TRA con el certificado digital y obtiene Token+Sign.
 *  2. WSFE — Facturación: llama a FECAESolicitar con los datos de la factura.
 *
 * Ambientes disponibles via AFIP_AMBIENTE en .env:
 *   - homologacion (testing)
 *   - produccion
 *
 * Certificados requeridos (rutas configurables via .env):
 *   AFIP_CERT_PATH — ruta al certificado .pem emitido por AFIP
 *   AFIP_KEY_PATH  — ruta a la clave privada .key
 *   AFIP_CUIT      — CUIT del emisor sin guiones (ej: 20123456789)
 */
class AfipService
{
    // ─── URLs por ambiente ────────────────────────────────────────────────────

    private const WSAA_URLS = [
        'homologacion' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl',
        'produccion'   => 'https://wsaa.afip.gov.ar/ws/services/LoginCms?wsdl',
    ];

    private const WSFE_URLS = [
        'homologacion' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
        'produccion'   => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx?WSDL',
    ];

    // Servicio de WSAA (nombre del servicio de facturación)
    private const WSFE_SERVICE = 'wsfe';

    // Tipo de factura C (Monotributista) = 11
    // Tipo de factura A (Resp. Inscripto a Resp. Inscripto) = 1
    // Tipo de factura B (Resp. Inscripto a Consumidor Final) = 6
    private const CBTE_TIPO_FACTURA_C = 11;

    private string $ambiente;
    private string $certPath;
    private string $keyPath;
    private string $cuit;
    private string $cacheDir;

    public function __construct()
    {
        $this->ambiente  = config('afip.ambiente', 'homologacion');
        $this->certPath  = base_path(config('afip.cert_path', 'storage/app/afip/cert.pem'));
        $this->keyPath   = base_path(config('afip.key_path',  'storage/app/afip/key.pem'));
        $this->cuit      = config('afip.cuit', '');
        $this->cacheDir  = storage_path('app/afip');

        // Crear directorio de caché si no existe
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    // ─── Método principal ─────────────────────────────────────────────────────

    /**
     * Solicita el CAE a AFIP-ARCA para un documento de tipo factura.
     * Devuelve un array con ['cae', 'cae_vto', 'respuesta_raw'].
     *
     * @throws RuntimeException si hay error de comunicación o el CUIT/cert no están configurados.
     */
    public function solicitarCAE(Documento $doc, Comercio $comercio): array
    {
        $this->validarConfiguracion();

        // Obtener Token + Sign via WSAA
        ['token' => $token, 'sign' => $sign] = $this->obtenerTokenSign();

        $cuit       = $this->cuit ?: preg_replace('/\D/', '', $comercio->cuit ?? '');
        $puntoVenta = intval($comercio->punto_venta ?? 1);
        $nroComprobante = intval(explode('-', $doc->numero)[1] ?? 1);

        // Construir ítems del comprobante
        $importeTotal = (float) $doc->total;
        $importeNeto  = $importeTotal; // Factura C: todo es neto sin IVA discriminado

        $cbteAsoc = []; // No hay comprobantes asociados para facturas C nuevas

        $cbteFch = $doc->emitido_en->format('Ymd');

        // Armar request para FECAESolicitar
        $request = [
            'Auth' => [
                'Token' => $token,
                'Sign'  => $sign,
                'Cuit'  => $cuit,
            ],
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg'  => 1,
                    'PtoVta'   => $puntoVenta,
                    'CbteTipo' => self::CBTE_TIPO_FACTURA_C,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto'    => 1, // 1=Productos, 2=Servicios, 3=Productos y Servicios
                        'DocTipo'     => $this->tipoDoc($doc),
                        'DocNro'      => $this->nroDoc($doc),
                        'CbteDesde'   => $nroComprobante,
                        'CbteHasta'   => $nroComprobante,
                        'CbteFch'     => $cbteFch,
                        'ImpTotal'    => $importeTotal,
                        'ImpTotConc'  => 0,
                        'ImpNeto'     => $importeNeto,
                        'ImpOpEx'     => 0,
                        'ImpIVA'      => 0,
                        'ImpTrib'     => 0,
                        'MonId'       => 'PES', // Pesos argentinos
                        'MonCotiz'    => 1,
                    ],
                ],
            ],
        ];

        // Llamar a WSFE
        try {
            $client   = new SoapClient(self::WSFE_URLS[$this->ambiente], [
                'exceptions' => true,
                'trace'      => true,
            ]);
            $response = $client->FECAESolicitar($request);
        } catch (SoapFault $e) {
            throw new RuntimeException('Error SOAP WSFE: ' . $e->getMessage(), 0, $e);
        }

        $resultado = $response->FECAESolicitarResult ?? null;

        // Verificar errores de negocio
        $this->verificarErrores($resultado);

        $detalle = $resultado->FeDetResp->FECAEDetResponse ?? null;

        if (!$detalle || ($detalle->Resultado ?? '') !== 'A') {
            $obs = collect((array)($detalle->Observaciones->Obs ?? []))
                ->map(fn($o) => "[{$o->Code}] {$o->Msg}")
                ->implode(' | ');
            throw new RuntimeException('AFIP rechazó la factura: ' . ($obs ?: 'sin detalle'));
        }

        return [
            'cae'          => $detalle->CAE,
            'cae_vto'      => \Carbon\Carbon::createFromFormat('Ymd', $detalle->CAEFchVto)->toDateString(),
            'respuesta_raw' => json_encode($response, JSON_UNESCAPED_UNICODE),
        ];
    }

    // ─── WSAA: Token + Sign ───────────────────────────────────────────────────

    /**
     * Obtiene el Token+Sign del WSAA.
     * Usa caché de 12 horas para no re-autenticar en cada factura.
     */
    private function obtenerTokenSign(): array
    {
        $cacheFile = $this->cacheDir . '/ta_' . $this->ambiente . '.json';

        // Usar caché si aún es válida (vence 10 min antes para evitar borde)
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (isset($cached['expira_en']) && time() < ($cached['expira_en'] - 600)) {
                return ['token' => $cached['token'], 'sign' => $cached['sign']];
            }
        }

        // Generar nuevo TRA y firmarlo
        $tra  = $this->generarTRA();
        $cms  = $this->firmarTRA($tra);

        // Llamar a WSAA
        try {
            $client   = new SoapClient(self::WSAA_URLS[$this->ambiente], ['exceptions' => true]);
            $response = $client->loginCms(['in0' => $cms]);
        } catch (SoapFault $e) {
            throw new RuntimeException('Error WSAA: ' . $e->getMessage(), 0, $e);
        }

        $xml = simplexml_load_string($response->loginCmsReturn);
        if (!$xml) {
            throw new RuntimeException('WSAA devolvió una respuesta inválida.');
        }

        $token    = (string) $xml->credentials->token;
        $sign     = (string) $xml->credentials->sign;
        $expiraEn = strtotime((string) $xml->header->expirationTime);

        // Guardar caché
        file_put_contents($cacheFile, json_encode([
            'token'     => $token,
            'sign'      => $sign,
            'expira_en' => $expiraEn,
        ]));

        return ['token' => $token, 'sign' => $sign];
    }

    /**
     * Genera el XML del Ticket de Requerimiento de Acceso (TRA).
     */
    private function generarTRA(): string
    {
        $ahora      = new \DateTime('now', new \DateTimeZone('UTC'));
        $desde      = clone $ahora;
        $hasta      = clone $ahora;
        $desde->modify('-10 minutes');
        $hasta->modify('+12 hours');

        $uniqueId   = time();

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<loginTicketRequest version="1.0">'
            .   '<header>'
            .     '<uniqueId>' . $uniqueId . '</uniqueId>'
            .     '<generationTime>' . $desde->format('c') . '</generationTime>'
            .     '<expirationTime>' . $hasta->format('c') . '</expirationTime>'
            .   '</header>'
            .   '<service>' . self::WSFE_SERVICE . '</service>'
            . '</loginTicketRequest>';
    }

    /**
     * Firma el TRA con el certificado digital y la clave privada.
     * Devuelve el CMS en Base64 (formato esperado por WSAA).
     */
    private function firmarTRA(string $tra): string
    {
        if (!file_exists($this->certPath)) {
            throw new RuntimeException("Certificado AFIP no encontrado en: {$this->certPath}");
        }
        if (!file_exists($this->keyPath)) {
            throw new RuntimeException("Clave privada AFIP no encontrada en: {$this->keyPath}");
        }

        $cert    = file_get_contents($this->certPath);
        $key     = file_get_contents($this->keyPath);
        $signed  = '';

        if (!openssl_pkcs7_sign(
            tempnam(sys_get_temp_dir(), 'tra_') . '.xml',
            tempnam(sys_get_temp_dir(), 'cms_') . '.pem',
            $cert,
            [$key, ''],
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            ''
        )) {
            // Alternativa usando openssl_sign directamente para compatibilidad
            return $this->firmarTRAAlternativo($tra, $cert, $key);
        }

        return $signed;
    }

    /**
     * Firma el TRA usando openssl_pkcs7_sign con archivos temporales.
     */
    private function firmarTRAAlternativo(string $tra, string $cert, string $key): string
    {
        $tmpTra = tempnam(sys_get_temp_dir(), 'afip_tra_');
        $tmpOut = tempnam(sys_get_temp_dir(), 'afip_cms_');

        try {
            file_put_contents($tmpTra, $tra);

            $certResource = openssl_x509_read($cert);
            $keyResource  = openssl_pkey_get_private($key);

            if (!$certResource || !$keyResource) {
                throw new RuntimeException('No se pudo leer el certificado o la clave privada AFIP.');
            }

            $ok = openssl_pkcs7_sign(
                $tmpTra,
                $tmpOut,
                $certResource,
                $keyResource,
                [],
                PKCS7_BINARY | PKCS7_DETACHED
            );

            if (!$ok) {
                throw new RuntimeException('Error al firmar el TRA: ' . openssl_error_string());
            }

            // Extraer el contenido CMS (la parte después de los encabezados MIME)
            $content = file_get_contents($tmpOut);
            $parts   = explode("\n\n", $content);
            // El CMS está en la última sección
            $cms = end($parts);
            // Limpiar saltos de línea para Base64 limpio
            return base64_encode(base64_decode(str_replace(["\n", "\r", ' '], '', $cms)));

        } finally {
            @unlink($tmpTra);
            @unlink($tmpOut);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function validarConfiguracion(): void
    {
        if (empty($this->cuit)) {
            throw new RuntimeException('AFIP_CUIT no está configurado en .env');
        }
    }

    /**
     * Tipo de documento del receptor.
     * 99 = Consumidor Final (sin documento), 96 = DNI, 80 = CUIT
     */
    private function tipoDoc(Documento $doc): int
    {
        if (!$doc->cliente) {
            return 99; // Consumidor final
        }
        $cliente = $doc->cliente;
        if (!empty($cliente->cuit)) return 80;
        if (!empty($cliente->dni))  return 96;
        return 99;
    }

    private function nroDoc(Documento $doc): string
    {
        if (!$doc->cliente) return '0';
        $cliente = $doc->cliente;
        if (!empty($cliente->cuit)) return preg_replace('/\D/', '', $cliente->cuit);
        if (!empty($cliente->dni))  return preg_replace('/\D/', '', $cliente->dni);
        return '0';
    }

    private function verificarErrores(mixed $resultado): void
    {
        $errors = $resultado->Errors->Err ?? null;
        if ($errors) {
            $lista = is_array($errors) ? $errors : [$errors];
            $msg   = collect($lista)->map(fn($e) => "[{$e->Code}] {$e->Msg}")->implode(' | ');
            throw new RuntimeException('AFIP devolvió error: ' . $msg);
        }
    }
}
