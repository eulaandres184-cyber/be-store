<?php
namespace App\Livewire\Categorias;

use Livewire\Component;
use App\Models\Categoria;

class ListaCategorias extends Component
{
    public string $busqueda   = '';
    public string $tipoFiltro = '';
    public string $ordenarPor = 'orden';
    public string $direccion  = 'asc';

    public function ordenar(string $col): void
    {
        if ($this->ordenarPor === $col) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $col;
            $this->direccion  = 'asc';
        }
    }

    /**
     * Las categorías NUNCA se eliminan — solo se desactivan.
     * Esto preserva la integridad del historial de ventas y productos.
     */
    public function toggleActivo(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        $cat->update(['activo' => !$cat->activo]);

        $estado = $cat->fresh()->activo ? 'activada' : 'desactivada';
        session()->flash('success', "Categoría '{$cat->nombre}' {$estado}.");
    }

    public function render()
    {
        $categorias = Categoria::where('comercio_id', 1)
            ->when($this->busqueda,   fn($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->when($this->tipoFiltro, fn($q) => $q->where('tipo', $this->tipoFiltro))
            ->withCount('productos')
            ->orderBy($this->ordenarPor, $this->direccion)
            ->get();

        return view('livewire.categorias.lista-categorias', compact('categorias'))
            ->layout('layouts.app', ['title' => 'Categorías']);
    }
}
