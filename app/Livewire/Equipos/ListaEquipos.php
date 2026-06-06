<?php

namespace App\Livewire\Equipos;

use App\Models\EquipoDetalle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;


/**
 * ListaEquipos - Componente Livewire para listar equipos (celulares con IMEI)
 */
class ListaEquipos extends Component
{
    

    public string $busqueda = '';
    public string $marcaFiltro = '';
    public string $estadoFiltro = 'disponible';

    protected $queryString = ['busqueda', 'marcaFiltro', 'estadoFiltro'];

    /**
     * Obtener ID del comercio del usuario autenticado
     */
    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    /**
     * Equipos paginados con filtros aplicados
     */
    #[Computed]
    public function equipos()
    {
        return EquipoDetalle::query()
            ->whereHas('producto', fn($q) => $q->where('comercio_id', $this->comercioId()))
            ->when($this->busqueda, function ($q) {
                $q->where('imei', 'like', "%{$this->busqueda}%")
                  ->orWhere('nombre', 'like', "%{$this->busqueda}%");
            })
            ->when($this->marcaFiltro, fn($q) => $q->where('marca', 'like', "%{$this->marcaFiltro}%"))
            ->when($this->estadoFiltro !== 'todos', fn($q) => $q->where('estado', $this->estadoFiltro))
            ->with('producto')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obtener marcas únicas de equipos disponibles
     */
    #[Computed]
    public function marcas()
    {
        return EquipoDetalle::query()
            ->whereHas('producto', fn($q) => $q->where('comercio_id', $this->comercioId()))
            ->distinct('marca')
            ->pluck('marca')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Resetear paginación cuando cambia la búsqueda
     */
    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    /**
     * Resetear paginación cuando cambia el filtro de marca
     */
    public function updatingMarcaFiltro(): void
    {
        $this->resetPage();
    }

    /**
     * Resetear paginación cuando cambia el filtro de estado
     */
    public function updatingEstadoFiltro(): void
    {
        $this->resetPage();
    }

    /**
     * Renderizar la vista
     */
    public function render()
    {
        $equiposDisponibles = EquipoDetalle::where('estado', 'disponible')
            ->whereHas('producto', fn($q) => $q->where('comercio_id', $this->comercioId()))
            ->count();

        return view('livewire.equipos.lista-equipos', [
            'equiposDisponibles' => $equiposDisponibles,
        ])->layout('layouts.app', ['title' => 'Equipos']);
    }
}
