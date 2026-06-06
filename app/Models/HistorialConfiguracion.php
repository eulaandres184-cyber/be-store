<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HistorialConfiguracion - Registro de cambios en configuración del sistema
 * Auditoría de cambios en recargos, porcentajes, valores del dólar, etc
 */
class HistorialConfiguracion extends Model
{
    protected $table = 'historial_configuracion';

    protected $fillable = [
        'comercio_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'usuario_id',
        'descripcion',
        'cambio_en',
    ];

    protected $casts = [
        'cambio_en' => 'datetime',
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

    public function scopeDelCampo($query, string $campo)
    {
        return $query->where('campo', $campo);
    }

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('cambio_en', [$desde, $hasta]);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('cambio_en', '>=', now()->subDays($dias));
    }

    /**
     * Método para registrar un cambio de configuración
     */
    public static function registrarCambio(
        int $comercioId,
        string $campo,
        $valorAnterior,
        $valorNuevo,
        ?int $usuarioId = null,
        ?string $descripcion = null
    ): self {
        return self::create([
            'comercio_id' => $comercioId,
            'campo' => $campo,
            'valor_anterior' => (string) $valorAnterior,
            'valor_nuevo' => (string) $valorNuevo,
            'usuario_id' => $usuarioId ?? auth()->id(),
            'descripcion' => $descripcion,
            'cambio_en' => now(),
        ]);
    }

    /**
     * Campos que se pueden auditar
     */
    public static function getCamposAuditables(): array
    {
        return [
            'recargo_tarjeta' => 'Recargo Tarjeta de Crédito (%)',
            'cuotas_4_recargo' => 'Recargo Cuotas 4 (%)',
            'cuotas_20_recargo' => 'Recargo Cuotas 20 (%)',
            'dolar_blue_hoy' => 'Valor Dólar Blue',
            'dolar_oficial' => 'Valor Dólar Oficial',
        ];
    }

    /**
     * Obtener nombre legible del campo
     */
    public function getNombreCampo(): string
    {
        return self::getCamposAuditables()[$this->campo] ?? $this->campo;
    }
}
