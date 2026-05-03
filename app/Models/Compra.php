<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = ['comercio_id', 'proveedor_id', 'user_id', 'fecha', 'total_ars', 'notas'];
    protected $casts = ['fecha' => 'date'];

    public function items() { return $this->hasMany(CompraItem::class); }
    public function proveedor() { return $this->belongsTo(Proveedor::class); }
}