<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Documento
 * Representa cualquier documento emitido: ticket, factura, recibo o presupuesto.
 * Guarda un snapshot de los ítems para que el historial no cambie
 * aunque el producto se modifique después.
 */
class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'comercio_id','venta_id','cliente_id','user_id',
        'tipo','numero','subtotal','descuento','total',
        'moneda','dolar_blue','items','pagos',
        'observaciones','anulado','emitido_en'
    ];

    protected $casts = [
        'items'       => 'array',
        'pagos'       => 'array',
        'anulado'     => 'boolean',
        'emitido_en'  => 'datetime',
    ];

    public function venta()   { return $this->belongsTo(Venta::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    public function comercio(){ return $this->belongsTo(Comercio::class); }

    /** Retorna el label del tipo de documento */
    public function getLabelTipoAttribute(): string
    {
        return match($this->tipo) {
            'ticket'      => 'Ticket',
            'factura'     => 'Factura C',
            'recibo'      => 'Recibo de Pago',
            'presupuesto' => 'Presupuesto',
            default       => ucfirst($this->tipo),
        };
    }
}
