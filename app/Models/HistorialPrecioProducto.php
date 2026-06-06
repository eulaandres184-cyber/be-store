<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HistorialPrecioProducto - Registro de cambios de precios de productos
 * Permite auditoría completa sin afectar ventas anteriores
 */
class HistorialPrecioProducto extends Model
{
    protected $table = 'historial_precios_productos';

    protected $fillable = [
        'producto_id',
        'comercio_id',
        'precio_anterior',
        'precio_nuevo',
        'moneda_anterior',
        'moneda_nueva',
        'usuario_id',
        'razon_cambio',
        'cambio_en',
    ];

    protected $casts = [
        'precio_anterior' => 'float',
        'precio_nuevo' => 'float',
        'cambio_en' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    public function comercio()
    {
        return $this->belongsTo(Comercio::class, 'comercio_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Scopes para búsquedas comunes
     */
    public function scopeDelComercio($query, int $comercioId)
    {
        return $query->where('comercio_id', $comercioId);
    }

    public function scopeDelProducto($query, int $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('cambio_en', [$desde, $hasta]);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('cambio_en', '>=', now()->subDays($dias));
    }

    /**
     * Método para obtener el cambio de precio
     */
    public function obtenerDiferencia(): float
    {
        return $this->precio_nuevo - $this->precio_anterior;
    }

    /**
     * Método para obtener el porcentaje de cambio
     */
    public function obtenerPorcentajeCambio(): float
    {
        if ($this->precio_anterior == 0) {
            return 100;
        }
        return (($this->precio_nuevo - $this->precio_anterior) / $this->precio_anterior) * 100;
    }

    /**
     * Método para registrar un cambio de precio
     */
    public static function registrarCambio(
        int $productoId,
        int $comercioId,
        float $precioAnterior,
        float $precioNuevo,
        string $moneda,
        ?int $usuarioId = null,
        ?string $razonCambio = null
    ): self {
        return self::create([
            'producto_id' => $productoId,
            'comercio_id' => $comercioId,
            'precio_anterior' => $precioAnterior,
            'precio_nuevo' => $precioNuevo,
            'moneda_anterior' => $moneda,
            'moneda_nueva' => $moneda,
            'usuario_id' => $usuarioId ?? auth()->id(),
            'razon_cambio' => $razonCambio,
            'cambio_en' => now(),
        ]);
    }
}
