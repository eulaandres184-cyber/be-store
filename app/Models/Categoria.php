<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Categoria
 * Se define $table explícitamente porque Laravel pluralizaría
 * "Categoria" como "categorias" correctamente en este caso,
 * pero lo dejamos explícito para mayor claridad.
 */
class Categoria extends Model
{
    protected $table    = 'categorias';
    protected $fillable = ['comercio_id', 'nombre', 'tipo', 'orden', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }

    public function scopeActivas($q)
    {
        return $q->where('activo', true);
    }
}
