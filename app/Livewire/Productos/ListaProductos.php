<?php

namespace App\Livewire\Productos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\Categoria;

class ListaProductos extends Component
{
    use WithPagination;

    public string $busqueda        = '';
    public string $categoriaFiltro = '';
    public string $estadoFiltro    = 'activos';
    public string $ordenarPor      = 'nombre';
    public string $direccion       = 'asc';

    protected $queryString = ['busqueda', 'categoriaFiltro', 'estadoFiltro'];

    public function updatingBusqueda()
    {
        $this->resetPage();
    }
    public function updatingCategoriaFiltro()
    {
        $this->resetPage();
    }

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

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)->where('activo', true)->orderBy('orden')->get();
    }

    private function authorizeAdmin(): void
    {
        if (auth()->user()?->rol !== 'admin') {
            abort(403);
        }
    }

    public function eliminar(int $id): void
    {
        $this->authorizeAdmin();
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]);
        session()->flash('success', "Producto '{$producto->nombre}' desactivado.");
    }

    public function activar(int $id): void
    {
        $this->authorizeAdmin();
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => true]);
        session()->flash('success', "Producto '{$producto->nombre}' activado.");
    }

    public function render()
    {
        $productos = Producto::where('comercio_id', 1)
            ->when($this->busqueda,        fn($q) => $q->buscar($this->busqueda))
            ->when($this->categoriaFiltro, fn($q) => $q->where('categoria_id', $this->categoriaFiltro))
            ->when($this->estadoFiltro === 'activos',    fn($q) => $q->where('activo', true))
            ->when($this->estadoFiltro === 'inactivos',  fn($q) => $q->where('activo', false))
            ->when($this->estadoFiltro === 'bajo_stock', fn($q) => $q->bajoMinimo()->where('activo', true))
            ->with('categoria')
            ->orderBy($this->ordenarPor, $this->direccion)
            ->paginate(20);

        return view('livewire.productos.lista-productos', compact('productos'))
            ->layout('layouts.app', ['title' => 'Productos']);
    }
}
