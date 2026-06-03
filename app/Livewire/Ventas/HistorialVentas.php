<?php
namespace App\Livewire\Ventas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Venta;

class HistorialVentas extends Component
{
    use WithPagination;

    public string $desde      = '';
    public string $hasta      = '';
    public string $medioPago  = '';
    public string $busqueda   = '';
    public string $ordenarPor = 'fecha';
    public string $direccion  = 'desc';
    public ?int   $ventaDetalle = null;

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->format('Y-m-d');
    }

    public function ordenar(string $col): void
    {
        if ($this->ordenarPor === $col) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $col;
            $this->direccion  = 'desc';
        }
        $this->resetPage();
    }

    public function verDetalle(int $id): void
    {
        $this->ventaDetalle = $id;
    }

    public function cerrarDetalle(): void
    {
        $this->ventaDetalle = null;
    }

    public function getVentaDetalleDataProperty()
    {
        if (!$this->ventaDetalle) return null;
        return Venta::with(['items.producto', 'cliente', 'usuario', 'partePago'])
            ->find($this->ventaDetalle);
    }

    public function getTotalesProperty()
    {
        $q = Venta::where('comercio_id', 1)
            ->when($this->desde,     fn($q) => $q->whereDate('fecha', '>=', $this->desde))
            ->when($this->hasta,     fn($q) => $q->whereDate('fecha', '<=', $this->hasta))
            ->when($this->medioPago, fn($q) => $q->where('medio_pago', $this->medioPago));

        return [
            'total'      => $q->sum('total_ars'),
            'cantidad'   => $q->count(),
            'promedio'   => $q->count() > 0 ? $q->sum('total_ars') / $q->count() : 0,
        ];
    }

    public function render()
    {
        $ventas = Venta::where('comercio_id', 1)
            ->with(['items', 'cliente', 'usuario'])
            ->when($this->desde,     fn($q) => $q->whereDate('fecha', '>=', $this->desde))
            ->when($this->hasta,     fn($q) => $q->whereDate('fecha', '<=', $this->hasta))
            ->when($this->medioPago, fn($q) => $q->where('medio_pago', $this->medioPago))
            ->when($this->busqueda,  fn($q) =>
                $q->whereHas('cliente', fn($q) =>
                    $q->where('nombre', 'like', "%{$this->busqueda}%")
                )
            )
            ->orderBy($this->ordenarPor, $this->direccion)
            ->paginate(20);

        return view('livewire.ventas.historial-ventas', compact('ventas'))
            ->layout('layouts.app', ['title' => 'Historial de ventas']);
    }
}
