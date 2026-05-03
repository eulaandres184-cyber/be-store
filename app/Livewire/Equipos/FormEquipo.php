<?php
namespace App\Livewire\Equipos;
use Livewire\Component;
class FormEquipo extends Component
{
    public function render()
    {
        return view('livewire.equipos.form-equipo')
               ->layout('layouts.app', ['title' => 'Equipo']);
    }
}
