<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Devolucion
 * Registra devoluciones por falla, cambio de producto o devolución de dinero.
 * Al registrar una devolución se puede reponer el stock automáticamente.
 */
class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = [
        'comercio_id', 'venta_id', 'user_id', 'cliente_id',
        'tipo', 'motivo', 'monto_devuelto', 'medio_devolucion',
        'producto_nuevo_id', 'cantidad', 'stock_repuesto', 'observaciones'
    ];

    protected $casts = ['stock_repuesto' => 'boolean'];

    public function venta()          { return $this->belongsTo(Venta::class); }
    public function cliente()        { return $this->belongsTo(Cliente::class); }
    public function usuario()        { return $this->belongsTo(User::class, 'user_id'); }
    public function productoNuevo()  { return $this->belongsTo(Producto::class, 'producto_nuevo_id'); }

    public function getLabelTipoAttribute(): string
    {
        return match($this->tipo) {
            'falla'            => '🔧 Falla del producto',
            'cambio'           => '🔄 Cambio de producto',
            'devolucion_dinero'=> '💵 Devolución de dinero',
            default            => $this->tipo,
        };
    }
}
