<?php
namespace App\Livewire\Productos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;

class ListaProductos extends Component
{
    // Filtros — todos activos simultáneamente
    public string $busqueda        = '';
    public string $categoriaFiltro = '';
    public string $estadoFiltro    = 'activos';
    public string $ordenarPor      = 'nombre';
    public string $direccion       = 'asc';

    public function ordenar(string $columna): void
    {
        if ($this->ordenarPor === $columna) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $columna;
            $this->direccion  = 'asc';
        }
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda        = '';
        $this->categoriaFiltro = '';
        $this->estadoFiltro    = 'activos';
        $this->ordenarPor      = 'nombre';
        $this->direccion       = 'asc';
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function eliminar(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => false]);
        session()->flash('success', "Producto '{$producto->nombre}' desactivado.");
    }

    public function activar(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['activo' => true]);
        session()->flash('success', "Producto '{$producto->nombre}' activado.");
    }

    public function getTotalFiltradoProperty(): int
    {
        return $this->getProductosQuery()->count();
    }

    private function getProductosQuery()
    {
        return Producto::where('comercio_id', 1)
            // Filtro de texto: busca en nombre, código interno y código de barras
            ->when($this->busqueda, fn($q) => $q->where(function($q) {
                $q->where('nombre',         'like', "%{$this->busqueda}%")
                  ->orWhere('codigo_interno','like', "%{$this->busqueda}%")
                  ->orWhere('codigo_barras', 'like', "%{$this->busqueda}%");
            }))
            // Filtro de categoría
            ->when($this->categoriaFiltro, fn($q) =>
                $q->where('categoria_id', $this->categoriaFiltro)
            )
            // Filtro de estado — todos los filtros se aplican juntos
            ->when($this->estadoFiltro === 'activos',    fn($q) => $q->where('activo', true))
            ->when($this->estadoFiltro === 'inactivos',  fn($q) => $q->where('activo', false))
            ->when($this->estadoFiltro === 'bajo_stock', fn($q) =>
                $q->where('activo', true)->whereColumn('stock_actual', '<=', 'stock_minimo')
            )
            ->when($this->estadoFiltro === 'sin_stock',  fn($q) =>
                $q->where('activo', true)->where('stock_actual', 0)
            )
            ->with('categoria');
    }

    public function render()
    {
        $productos = $this->getProductosQuery()
            ->orderBy($this->ordenarPor, $this->direccion)
            ->get();

        return view('livewire.productos.lista-productos', compact('productos'))
            ->layout('layouts.app', ['title' => 'Productos']);
    }
}
