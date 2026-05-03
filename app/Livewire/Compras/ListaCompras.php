<?php

namespace App\Livewire\Compras;

use App\Models\Compra;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ListaCompras extends Component
{
    use WithPagination;

    public string $desde = '';
    public string $hasta = '';
    public string $proveedorFiltro = '';
    public string $ordenar = 'fecha_desc';

    protected $queryString = ['desde', 'hasta', 'proveedorFiltro', 'ordenar'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function compras()
    {
        return Compra::where('comercio_id', $this->comercioId())
            ->when(
                $this->desde,
                fn($q) =>
                $q->whereDate('fecha', '>=', $this->desde)
            )
            ->when(
                $this->hasta,
                fn($q) =>
                $q->whereDate('fecha', '<=', $this->hasta)
            )
            ->when(
                $this->proveedorFiltro,
                fn($q) =>
                $q->where('proveedor_id', $this->proveedorFiltro)
            )
            ->with(['items' => fn($q) => $q->with('producto'), 'proveedor'])
            ->when($this->ordenar === 'fecha_asc', fn($q) => $q->oldest('fecha'))
            ->when($this->ordenar === 'fecha_desc', fn($q) => $q->latest('fecha'))
            ->when($this->ordenar === 'monto_desc', fn($q) => $q->orderByDesc('total_ars'))
            ->paginate(15);
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::where('comercio_id', $this->comercioId())
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function limpiarFiltros(): void
    {
        $this->desde = '';
        $this->hasta = '';
        $this->proveedorFiltro = '';
        $this->ordenar = 'fecha_desc';
        $this->resetPage();
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        $totalCompras = Compra::where('comercio_id', $this->comercioId())->sum('total_ars');
        $comprasHoy = Compra::where('comercio_id', $this->comercioId())
            ->whereDate('fecha', today())
            ->sum('total_ars');

        return view('livewire.compras.lista-compras', [
            'totalCompras' => $totalCompras,
            'comprasHoy' => $comprasHoy,
        ])->layout('layouts.app', ['title' => 'Compras']);
    }
}
