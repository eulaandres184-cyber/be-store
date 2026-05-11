<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'comercio_id','user_id','cliente_id','fecha','medio_pago',
        'subtotal_ars','recargo_aplicado','total_ars',
        'dolar_blue_usado','notas'
    ];
    protected $casts = ['fecha' => 'datetime'];

    public function items()        { return $this->hasMany(VentaItem::class); }
    public function usuario()      { return $this->belongsTo(User::class, 'user_id'); }
    public function cliente()      { return $this->belongsTo(Cliente::class); }
    public function partePago()    { return $this->hasOne(EquipoPartePago::class); }

    public function getLabelMedioPagoAttribute(): string
    {
        return match($this->medio_pago) {
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia / MP',
            'tarjeta'       => 'Tarjeta (+15%)',
            'cuotas_4'      => 'BLP 4 cuotas (+2%)',
            'cuotas_20'     => 'BLP 20 cuotas (+20%)',
            default         => $this->medio_pago,
        };
    }
}
