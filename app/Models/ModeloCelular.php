<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeloCelular extends Model
{
    protected $table = 'modelos_celular';
    protected $fillable = ['comercio_id', 'marca', 'modelo', 'activo'];
}