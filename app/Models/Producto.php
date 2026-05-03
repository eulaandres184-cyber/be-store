<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'comercio_id', 'categoria_id', 'nombre', 'descripcion',
        'precio_efectivo', 'moneda', 'stock_actual', 'stock_minimo',
        'tiene_variantes', 'activo'
    ];

    protected $casts = ['tiene_variantes' => 'boolean', 'activo' => 'boolean'];

    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function comercio() { return $this->belongsTo(Comercio::class); }
    public function compatibilidades() { return $this->hasMany(ProductoCompatibilidad::class); }
    public function equipoDetalle() { return $this->hasOne(EquipoDetalle::class); }

    public function precioConRecargo(string $medio, Configuracion $config): float
    {
        $base = (float) $this->precio_efectivo;
        return match($medio) {
            'tarjeta'   => $base * (1 + $config->recargo_tarjeta / 100),
            'cuotas_4'  => $base * (1 + $config->cuotas_4_recargo / 100),
            'cuotas_20' => $base * (1 + $config->cuotas_20_recargo / 100),
            default     => $base,
        };
    }
}