<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraItem extends Model
{
    protected $fillable = [
        'compra_id', 'producto_id', 'cantidad',
        'costo_unitario', 'moneda', 'dolar_al_momento'
    ];

    public function producto() { return $this->belongsTo(Producto::class); }
}