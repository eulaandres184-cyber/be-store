<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Producto - Gestiona productos del comercio
 */
class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'comercio_id',
        'categoria_id',
        'codigo_interno',
        'codigo_barras',
        'nombre',
        'descripcion',
        'precio_efectivo',
        'moneda',
        'stock_actual',
        'stock_minimo',
        'tiene_variantes',
        'activo',
    ];

    protected $casts = [
        'tiene_variantes' => 'boolean',
        'activo'          => 'boolean',
        'precio_efectivo' => 'float',
        'stock_actual'    => 'integer',
        'stock_minimo'    => 'integer',
    ];

    /**
     * Relaciones
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', 'id');
    }

    public function comercio()
    {
        return $this->belongsTo(Comercio::class, 'comercio_id', 'id');
    }

    public function ventaItems()
    {
        return $this->hasMany(VentaItem::class, 'producto_id', 'id');
    }

    public function equiposDetalle()
    {
        return $this->hasMany(EquipoDetalle::class, 'producto_id', 'id');
    }

    /**
     * Relación con historial de cambios de precio
     */
    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecioProducto::class, 'producto_id', 'id')
            ->orderByDesc('cambio_en');
    }

    /**
     * Generar código interno automático
     */
    public static function generarCodigoInterno(): string
    {
        $ultimo = self::whereNotNull('codigo_interno')
            ->where('codigo_interno', 'like', 'BE-%')
            ->orderByDesc('id')
            ->value('codigo_interno');

        if (!$ultimo) {
            return 'BE-0001';
        }

        $numero = (int) substr($ultimo, 3);
        return 'BE-' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular precio según medio de pago
     */
    public function precioConRecargo(string $medio, ?Configuracion $config = null): float
    {
        $base = (float) $this->precio_efectivo;

        if (!$config) {
            return $base;
        }

        return match ($medio) {
            'tarjeta'   => $base * (1 + ($config->recargo_tarjeta ?? 15) / 100),
            'cuotas_4'  => $base * (1 + ($config->cuotas_4_recargo ?? 2) / 100),
            'cuotas_20' => $base * (1 + ($config->cuotas_20_recargo ?? 20) / 100),
            default     => $base,
        };
    }

    /**
     * Scopes para consultas comunes
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    public function scopeConStock($query)
    {
        return $query->where('stock_actual', '>', 0);
    }

    public function scopeBajoMinimo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    public function scopeBuscar($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
                ->orWhere('codigo_interno', 'like', "%{$term}%")
                ->orWhere('codigo_barras', 'like', "%{$term}%");
        });
    }

    public function scopeDelComercio($query, int $comercioId)
    {
        return $query->where('comercio_id', $comercioId);
    }
}
