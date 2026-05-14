<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
    protected $table = 'categorias';
    protected $table = 'categorias';
{
    protected $fillable = ['comercio_id', 'nombre', 'tipo', 'orden', 'activo'];

    public function productos() { return $this->hasMany(Producto::class); }
    public function comercio() { return $this->belongsTo(Comercio::class); }
}
