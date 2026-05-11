<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EquipoPartePago extends Model
{
    protected $table = 'equipos_parte_pago';
    protected $fillable = [
        'venta_id','cliente_id','marca','modelo','imei','color',
        'capacidad_gb','condicion','bateria_pct',
        'cotizacion_usd','dolar_blue_usado','valor_ars','observaciones'
    ];

    public function venta()   { return $this->belongsTo(Venta::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
}
