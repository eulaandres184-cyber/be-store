<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Proveedor
 * Tabla: proveedores (se define explícitamente para evitar
 * la pluralización automática en inglés "proveedors")
 */
class Proveedor extends Model
{
    // Laravel pluraliza en inglés por defecto → "proveedors"
    // Definimos el nombre correcto de la tabla manualmente
    protected $table = 'proveedores';

    protected $fillable = [
        'comercio_id',
        'nombre',
        'contacto',
        'whatsapp',
        'notas',
        'activo',
    ];

    protected $casts = ['activo' => 'boolean'];

    /** Un proveedor pertenece a un comercio */
    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }

    /** Un proveedor tiene muchas compras */
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    /** Scope: solo proveedores activos */
    public function scopeActivos($q)
    {
        return $q->where('activo', true);
    }
}
