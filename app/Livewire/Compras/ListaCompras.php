<?php

namespace App\Livewire\Compras;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Compra;

class ListaCompras extends Component
{
    use WithPagination;

    public string $busqueda  = '';
    public string $ordenarPor = 'fecha';
    public string $direccion  = 'desc';

    public function ordenar(string $columna): void
    {
        if ($this->ordenarPor === $columna) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $columna;
            $this->direccion  = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $compras = Compra::where('comercio_id', 1)
            ->with(['proveedor', 'items'])
            ->when($this->busqueda, fn($q) =>
                $q->whereHas('proveedor', fn($q) =>
                    $q->where('nombre', 'like', "%{$this->busqueda}%")
                )
            )
            ->orderBy($this->ordenarPor, $this->direccion)
            ->paginate(20);

        return view('livewire.compras.lista-compras', compact('compras'))
            ->layout('layouts.app', ['title' => 'Compras']);
    }
}
