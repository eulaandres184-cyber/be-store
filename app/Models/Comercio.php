<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comercio extends Model
    protected $table = 'comercios';
{
    protected $fillable = ['nombre', 'cuit', 'plan', 'activo'];

    public function usuarios() { return $this->hasMany(User::class, 'comercio_id'); }
    public function configuracion() { return $this->hasOne(Configuracion::class); }
    public function categorias() { return $this->hasMany(Categoria::class); }
    public function productos() { return $this->hasMany(Producto::class); }
    public function proveedores() { return $this->hasMany(Proveedor::class); }
}