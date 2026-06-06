<?php

namespace App\Observers;

use App\Models\Configuracion;
use App\Models\HistorialConfiguracion;

/**
 * ConfiguracionObserver - Observa cambios en la configuración del sistema
 * Registra automáticamente cambios en recargos, porcentajes, valores del dólar, etc
 */
class ConfiguracionObserver
{
    /**
     * Campos que se deben auditar
     */
    private const CAMPOS_AUDITABLES = [
        'recargo_tarjeta',
        'cuotas_4_recargo',
        'cuotas_20_recargo',
        'dolar_blue_hoy',
        'dolar_oficial',
    ];

    /**
     * Handle the Configuracion "updated" event.
     */
    public function updated(Configuracion $configuracion): void
    {
        foreach (self::CAMPOS_AUDITABLES as $campo) {
            if ($configuracion->isDirty($campo)) {
                $valorAnterior = $configuracion->getOriginal($campo);
                $valorNuevo = $configuracion->{$campo};

                // No registrar si no cambió realmente
                if ($valorAnterior != $valorNuevo) {
                    HistorialConfiguracion::registrarCambio(
                        comercioId: $configuracion->comercio_id,
                        campo: $campo,
                        valorAnterior: $valorAnterior,
                        valorNuevo: $valorNuevo,
                        usuarioId: auth()->id(),
                        descripcion: $this->obtenerDescripcionCambio($campo, $valorAnterior, $valorNuevo)
                    );
                }
            }
        }
    }

    /**
     * Obtener descripción legible del cambio
     */
    private function obtenerDescripcionCambio(string $campo, $anterior, $nuevo): string
    {
        $nombres = HistorialConfiguracion::getCamposAuditables();
        $nombre = $nombres[$campo] ?? $campo;

        return "$nombre: cambió de $anterior a $nuevo";
    }
}
