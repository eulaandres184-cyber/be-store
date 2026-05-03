<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    protected $fillable = [
        'venta_id', 'producto_id', 'cantidad',
        'precio_unitario_ars', 'precio_usd_snapshot', 'subtotal_ars'
    ];

    public function producto() { return $this->belongsTo(Producto::class); }
    public function venta() { return $this->belongsTo(Venta::class); }
}