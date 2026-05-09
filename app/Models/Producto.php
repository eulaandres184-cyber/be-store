<?php

namespace App\Models;


use App\Models\ProductoCompatibilidad;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
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
        'activo'
    ];

    protected $casts = [
        'tiene_variantes' => 'boolean',
        'activo'          => 'boolean',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }
    public function compatibilidades()
    {
        return $this->hasMany(ProductoCompatibilidad::class);
    }
    public function equipoDetalle()
    {
        return $this->hasOne(EquipoDetalle::class);
    }
    public function ventaItems()
    {
        return $this->hasMany(VentaItem::class);
    }

    // Generar código interno automático
    public static function generarCodigoInterno(): string
    {
        $ultimo = self::whereNotNull('codigo_interno')
            ->where('codigo_interno', 'like', 'BE-%')
            ->orderByDesc('id')
            ->value('codigo_interno');

        if (!$ultimo) return 'BE-0001';

        $numero = (int) substr($ultimo, 3);
        return 'BE-' . str_pad($numero + 1, 4, '0', STR_PAD_LEFT);
    }

    // Precio según medio de pago
    public function precioConRecargo(string $medio, Configuracion $config): float
    {
        $base = (float) $this->precio_efectivo;
        return match ($medio) {
            'tarjeta'   => $base * (1 + $config->recargo_tarjeta / 100),
            'cuotas_4'  => $base * (1 + $config->cuotas_4_recargo / 100),
            'cuotas_20' => $base * (1 + $config->cuotas_20_recargo / 100),
            default     => $base,
        };
    }

    // Scopes útiles
    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }
    public function scopeConStock($q)
    {
        return $q->where('stock_actual', '>', 0);
    }
    public function scopeBajoMinimo($q)
    {
        return $q->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
    public function scopeBuscar($q, $term)
    {
        return $q->where(function ($q) use ($term) {
            $q->where('nombre', 'like', "%$term%")
                ->orWhere('codigo_interno', 'like', "%$term%")
                ->orWhere('codigo_barras', 'like', "%$term%");
        });
    }
}
