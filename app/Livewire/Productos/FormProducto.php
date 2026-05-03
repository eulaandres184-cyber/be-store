<?php
namespace App\Livewire\Productos;
use Livewire\Component;
class FormProducto extends Component
{
    public function render()
    {
        return view('livewire.productos.form-producto')
               ->layout('layouts.app', ['title' => 'Producto']);
    }
}
