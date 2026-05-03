<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialDolar extends Model
{
    protected $table = 'historial_dolar';
    protected $fillable = ['fecha', 'valor_compra', 'valor_venta', 'fuente'];
    protected $casts = ['fecha' => 'date'];
}