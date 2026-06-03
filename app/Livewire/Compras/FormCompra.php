<?php
namespace App\Livewire\Compras;
use Livewire\Component;
class FormCompra extends Component
{
    public function render()
    {
        return view('livewire.compras.form-compra')
               ->layout('layouts.app', ['title' => 'Nueva Compra']);
    }
}
