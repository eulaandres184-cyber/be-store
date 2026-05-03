<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoDetalle extends Model
{
    protected $table = 'equipos_detalle';
    protected $fillable = [
        'producto_id', 'imei', 'marca', 'modelo',
        'capacidad_gb', 'color', 'condicion', 'precio_usd',
        'bateria_pct', 'estado'
    ];

    public function producto() { return $this->belongsTo(Producto::class); }
}