<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = ['comercio_id', 'nombre', 'contacto', 'whatsapp', 'notas', 'activo'];
    public function compras() { return $this->hasMany(Compra::class); }
}