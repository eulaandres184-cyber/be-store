<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuracion';
    protected $fillable = [
        'comercio_id',
        'recargo_tarjeta',
        'cuotas_4_recargo',
        'cuotas_20_recargo',
        'dolar_blue_hoy',
        'dolar_actualizado_en'
    ];

    protected $casts = [
        'dolar_actualizado_en' => 'datetime',
    ];

    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }
}
