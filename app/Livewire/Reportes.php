<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class Reportes extends Component
{
    public string $periodo = '7dias';

    public function getFechasProperty(): array
    {
        return match($this->periodo) {
            '7dias'  => [now()->subDays(7),  now()],
            '30dias' => [now()->subDays(30), now()],
            'mes'    => [now()->startOfMonth(), now()->endOfMonth()],
            default  => [now()->subDays(7),  now()],
        };
    }

    public function getVentasTotalProperty(): float
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_ars');
    }

    public function getComprasTotalProperty(): float
    {
        [$desde, $hasta] = $this->fechas;
        return Compra::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_ars');
    }

    public function getCantVentasProperty(): int
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->count();
    }

    public function getTicketPromedioProperty(): float
    {
        return $this->cantVentas > 0
            ? $this->ventasTotal / $this->cantVentas
            : 0;
    }

    public function getVentasPorDiaProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->select(DB::raw('DATE(fecha) as dia'), DB::raw('SUM(total_ars) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();
    }

    public function getVentasPorMedioPagoProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->select('medio_pago', DB::raw('SUM(total_ars) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('medio_pago')
            ->orderByDesc('total')
            ->get();
    }

    public function getProductosMasVendidosProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return VentaItem::whereHas('venta', fn($q) =>
                $q->where('comercio_id', 1)->whereBetween('fecha', [$desde, $hasta])
            )
            ->select('producto_id', DB::raw('SUM(cantidad) as total_vendido'), DB::raw('SUM(subtotal_ars) as total_ars'))
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->with('producto:id,nombre')
            ->limit(10)
            ->get();
    }

    public function getVentasPorCategoriaProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return VentaItem::whereHas('venta', fn($q) =>
                $q->where('comercio_id', 1)->whereBetween('fecha', [$desde, $hasta])
            )
            ->join('productos', 'ventas_items.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select('categorias.nombre as categoria', DB::raw('SUM(ventas_items.subtotal_ars) as total'))
            ->groupBy('categorias.nombre')
            ->orderByDesc('total')
            ->get();
    }

    public function render()
    {
        return view('livewire.reportes')
            ->layout('layouts.app', ['title' => 'Reportes']);
    }
}

