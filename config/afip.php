<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ambiente AFIP-ARCA
    |--------------------------------------------------------------------------
    | Valores: 'homologacion' (testing) | 'produccion'
    */
    'ambiente' => env('AFIP_AMBIENTE', 'homologacion'),

    /*
    |--------------------------------------------------------------------------
    | Certificado digital y clave privada
    |--------------------------------------------------------------------------
    | Rutas relativas a la raíz del proyecto.
    | Generarlos en: https://auth.afip.gob.ar/contribuyente_/login.xhtml
    | Ubicarlos en: storage/app/afip/  (fuera del control de versiones)
    */
    'cert_path' => env('AFIP_CERT_PATH', 'storage/app/afip/cert.pem'),
    'key_path'  => env('AFIP_KEY_PATH',  'storage/app/afip/key.pem'),

    /*
    |--------------------------------------------------------------------------
    | CUIT del emisor
    |--------------------------------------------------------------------------
    | CUIT sin guiones. Ej: 20123456789
    */
    'cuit' => env('AFIP_CUIT', ''),
];
