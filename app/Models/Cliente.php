<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Cliente
 * Representa a los clientes del comercio.
 * Un cliente puede tener muchas ventas asociadas.
 */
class Cliente extends Model
{
    protected $table    = 'clientes';
    protected $fillable = [
        'comercio_id', 'nombre', 'telefono', 'email', 'dni', 'notas', 'activo'
    ];
    protected $casts = ['activo' => 'boolean'];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }

    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }

    public function scopeBuscar($q, $term)
    {
        return $q->where(function($q) use ($term) {
            $q->where('nombre',   'like', "%$term%")
              ->orWhere('telefono','like', "%$term%")
              ->orWhere('dni',     'like', "%$term%")
              ->orWhere('email',   'like', "%$term%");
        });
    }
}
