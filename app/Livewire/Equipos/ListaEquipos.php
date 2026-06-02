<?php

namespace App\Livewire\Equipos;

use App\Models\EquipoDetalle;
use App\Models\ModeloCelular;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ListaEquipos extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $modeloFiltro = '';
    public string $estadoFiltro = 'disponible';

    protected $queryString = ['busqueda', 'modeloFiltro', 'estadoFiltro'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function equipos()
    {
        return EquipoDetalle::when(
            $this->busqueda,
            fn($q) =>
            $q->where('imei', 'like', "%{$this->busqueda}%")
        )
            ->when(
                $this->modeloFiltro,
                fn($q) =>
                $q->whereHas(
                    'producto',
                    fn($subQ) =>
                    $subQ->where('modelo_celular_id', $this->modeloFiltro)
                )
            )
            ->when(
                $this->estadoFiltro,
                fn($q) =>
                $q->where('estado', $this->estadoFiltro)
            )
            ->whereHas(
                'producto',
                fn($q) =>
                $q->where('comercio_id', $this->comercioId())
            )
            ->with(['producto' => fn($q) => $q->with('modeloCelular')])
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function modelos()
    {
        return ModeloCelular::where('comercio_id', $this->comercioId())
            ->where('activo', true)
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingModeloFiltro(): void
    {
        $this->resetPage();
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

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
