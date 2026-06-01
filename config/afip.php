<?php
/**
 * Configuración de AFIP/ARCA para BE Store
 *
 * Para pasar a producción:
 *   1. Cambiar 'produccion' => true
 *   2. Reemplazar certificados en storage/afip/ con los definitivos
 *   3. Dar de alta el punto de venta en AFIP como Web Service
 */
return [
    // false = homologación (testing), true = producción real
    'produccion' => env('AFIP_PRODUCCION', false),

    // CUIT del emisor
    'cuit' => env('AFIP_CUIT', '20347359476'),

    // Punto de venta (4 dígitos)
    'punto_venta' => env('AFIP_PUNTO_VENTA', '1'),

    // Ingresos brutos
    'ingresos_brutos' => env('AFIP_IIBB', '286174060'),

    // Fecha de inicio de actividades
    'inicio_actividades' => env('AFIP_INICIO', '01/06/2021'),
];
