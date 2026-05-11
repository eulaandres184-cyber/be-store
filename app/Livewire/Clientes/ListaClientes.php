<?php
namespace App\Livewire\Clientes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;

class ListaClientes extends Component
{
    use WithPagination;

    public string $busqueda   = '';
    public string $ordenarPor = 'nombre';
    public string $direccion  = 'asc';

    public function ordenar(string $col): void
    {
        if ($this->ordenarPor === $col) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $col;
            $this->direccion  = 'asc';
        }
        $this->resetPage();
    }

    public function toggleActivo(int $id): void
    {
        $c = Cliente::findOrFail($id);
        $c->update(['activo' => !$c->activo]);
    }

    public function render()
    {
        $clientes = Cliente::where('comercio_id', 1)
            ->when($this->busqueda, fn($q) => $q->buscar($this->busqueda))
            ->withCount('ventas')
            ->orderBy($this->ordenarPor, $this->direccion)
            ->paginate(20);

        return view('livewire.clientes.lista-clientes', compact('clientes'))
            ->layout('layouts.app', ['title' => 'Clientes']);
    }
}
