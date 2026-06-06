<?php

namespace App\Observers;

use App\Models\Producto;
use App\Models\HistorialPrecioProducto;

/**
 * ProductoObserver - Observa cambios en el modelo Producto
 * Registra automáticamente los cambios de precio en la auditoría
 */
class ProductoObserver
{
    /**
     * Handle the Producto "updated" event.
     */
    public function updated(Producto $producto): void
    {
        // Verificar si el precio cambió
        if ($producto->isDirty('precio_efectivo')) {
            $precioAnterior = $producto->getOriginal('precio_efectivo');
            $precioNuevo = $producto->precio_efectivo;

            // No registrar cambios si ambos precios son iguales
            if ($precioAnterior != $precioNuevo) {
                HistorialPrecioProducto::registrarCambio(
                    productoId: $producto->id,
                    comercioId: $producto->comercio_id,
                    precioAnterior: (float) $precioAnterior,
                    precioNuevo: (float) $precioNuevo,
                    moneda: $producto->moneda,
                    usuarioId: auth()->id(),
                    razonCambio: 'Actualización directa de precio'
                );
            }
        }

        // Verificar si la moneda cambió (también cambia el precio de referencia)
        if ($producto->isDirty('moneda')) {
            HistorialConfiguracion::registrarCambio(
                comercioId: $producto->comercio_id,
                campo: "producto_{$producto->id}_moneda",
                valorAnterior: $producto->getOriginal('moneda'),
                valorNuevo: $producto->moneda,
                usuarioId: auth()->id(),
                descripcion: "Cambio de moneda para producto: {$producto->nombre}"
            );
        }
    }

    /**
     * Handle the Producto "created" event.
     */
    public function created(Producto $producto): void
    {
        // Registrar la creación del producto
        HistorialPrecioProducto::create([
            'producto_id' => $producto->id,
            'comercio_id' => $producto->comercio_id,
            'precio_anterior' => 0,
            'precio_nuevo' => $producto->precio_efectivo,
            'moneda_anterior' => $producto->moneda,
            'moneda_nueva' => $producto->moneda,
            'usuario_id' => auth()->id(),
            'razon_cambio' => 'Creación de producto',
            'cambio_en' => now(),
        ]);
    }
}
