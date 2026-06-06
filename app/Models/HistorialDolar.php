<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HistorialDolar - Registro histórico de valores del dólar
 * Auditoría completa de cambios en cotización
 */
class HistorialDolar extends Model
{
    protected $table = 'historial_dolar';
    protected $fillable = [
        'comercio_id',
        'fecha',
        'valor_compra',
        'valor_venta',
        'fuente',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor_compra' => 'float',
        'valor_venta' => 'float',
    ];

    /**
     * Relaciones
     */
    public function comercio()
    {
        return $this->belongsTo(Comercio::class, 'comercio_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    /**
     * Scopes
     */
    public function scopeDelComercio($query, int $comercioId)
    {
        return $query->where('comercio_id', $comercioId);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecha', '>=', now()->subDays($dias)->toDateString());
    }

    /**
     * Registrar cambio de dólar
     */
    public static function registrarCambio(
        float $valorCompra,
        float $valorVenta,
        string $fuente = 'manual',
        ?int $comercioId = null,
        ?int $usuarioId = null
    ): self {
        return self::create([
            'comercio_id' => $comercioId ?? 1,
            'fecha' => now()->toDateString(),
            'valor_compra' => $valorCompra,
            'valor_venta' => $valorVenta,
            'fuente' => $fuente,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ]);
    }
}
