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

    public function eliminar(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        if ($cat->productos()->count() > 0) {
            session()->flash('error', "No podés eliminar '{$cat->nombre}' porque tiene productos asociados.");
            return;
        }
        $cat->delete();
        session()->flash('success', "Categoría '{$cat->nombre}' eliminada.");
    }

    public function toggleActivo(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        $cat->update(['activo' => !$cat->activo]);
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
