<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class HistorialVentas extends Component
{
    use WithPagination;

    public string $desde = '';
    public string $hasta = '';
    public string $medioPago = '';
    public string $ordenar = 'fecha_desc';

    protected $queryString = ['desde', 'hasta', 'medioPago', 'ordenar'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function ventas()
    {
        return Venta::where('comercio_id', $this->comercioId())
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
                $this->medioPago,
                fn($q) =>
                $q->where('medio_pago', $this->medioPago)
            )
            ->with(['items' => fn($q) => $q->with('producto')])
            ->when($this->ordenar === 'fecha_asc', fn($q) => $q->oldest('fecha'))
            ->when($this->ordenar === 'fecha_desc', fn($q) => $q->latest('fecha'))
            ->when($this->ordenar === 'monto_desc', fn($q) => $q->orderByDesc('total_ars'))
            ->paginate(15);
    }

    public function limpiarFiltros(): void
    {
        $this->desde = '';
        $this->hasta = '';
        $this->medioPago = '';
        $this->ordenar = 'fecha_desc';
        $this->resetPage();
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        $totalVentas = Venta::where('comercio_id', $this->comercioId())->sum('total_ars');
        $ventasHoy = Venta::where('comercio_id', $this->comercioId())
            ->whereDate('fecha', today())
            ->sum('total_ars');

        return view('livewire.ventas.historial-ventas', [
            'totalVentas' => $totalVentas,
            'ventasHoy' => $ventasHoy,
        ])->layout('layouts.app', ['title' => 'Ventas']);
    }
}
