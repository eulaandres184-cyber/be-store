<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ListaProductos extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $categoriaFiltro = '';
    public string $ordenar = 'nombre';
    public string $estado = 'activos';

    protected $queryString = ['busqueda', 'categoriaFiltro', 'ordenar', 'estado'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function productos()
    {
        return Producto::where('comercio_id', $this->comercioId())
            ->when(
                $this->busqueda,
                fn($q) =>
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                    ->orWhere('descripcion', 'like', "%{$this->busqueda}%")
            )
            ->when(
                $this->categoriaFiltro,
                fn($q) =>
                $q->where('categoria_id', $this->categoriaFiltro)
            )
            ->when(
                $this->estado === 'activos',
                fn($q) =>
                $q->where('activo', true)
            )
            ->when(
                $this->estado === 'inactivos',
                fn($q) =>
                $q->where('activo', false)
            )
            ->with('categoria')
            ->orderBy($this->ordenar)
            ->paginate(20);
    }

    #[Computed]
    public function categorias()
    {
        return Categoria::where('comercio_id', $this->comercioId())
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingCategoriaFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function toggleActivo(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if ($producto && $producto->comercio_id === $this->comercioId()) {
            $producto->update(['activo' => !$producto->activo]);
        }
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        return view('livewire.productos.lista-productos')
            ->layout('layouts.app', ['title' => 'Productos']);
    }
}
