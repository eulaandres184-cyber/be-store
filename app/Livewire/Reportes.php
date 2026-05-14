<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;

/**
 * Componente Livewire para el módulo de Reportes.
 * Muestra estadísticas de ventas, compras, medios de pago,
 * categorías y productos más vendidos en un período seleccionado.
 */
class Reportes extends Component
{
    /** @var string Período seleccionado: '7dias', '30dias' o 'mes' */
    public string $periodo = '7dias';

    /**
     * Calcula las fechas de inicio y fin según el período seleccionado.
     * @return array [Carbon $desde, Carbon $hasta]
     */
    public function getFechasProperty(): array
    {
        return match($this->periodo) {
            '7dias'  => [now()->subDays(7),          now()],
            '30dias' => [now()->subDays(30),         now()],
            'mes'    => [now()->startOfMonth(),       now()->endOfMonth()],
            default  => [now()->subDays(7),           now()],
        };
    }

    /** Total vendido en el período */
    public function getVentasTotalProperty(): float
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_ars') ?? 0;
    }

    /** Total de compras en el período */
    public function getComprasTotalProperty(): float
    {
        [$desde, $hasta] = $this->fechas;
        return Compra::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->sum('total_ars') ?? 0;
    }

    /** Cantidad de ventas en el período */
    public function getCantVentasProperty(): int
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->count();
    }

    /** Ticket promedio del período */
    public function getTicketPromedioProperty(): float
    {
        return $this->cantVentas > 0
            ? $this->ventasTotal / $this->cantVentas
            : 0;
    }

    /** Ventas agrupadas por medio de pago */
    public function getVentasPorMedioPagoProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->select(
                'medio_pago',
                DB::raw('SUM(total_ars) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('medio_pago')
            ->orderByDesc('total')
            ->get();
    }

    /** Ventas agrupadas por categoría de producto */
    public function getVentasPorCategoriaProperty()
    {
        [$desde, $hasta] = $this->fechas;

        // Usamos join directo para evitar problemas de relaciones
        return DB::table('ventas_items')
            ->join('ventas',    'ventas_items.venta_id',    '=', 'ventas.id')
            ->join('productos',  'ventas_items.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id',  '=', 'categorias.id')
            ->where('ventas.comercio_id', 1)
            ->whereBetween('ventas.fecha', [$desde, $hasta])
            ->select(
                'categorias.nombre as categoria',
                DB::raw('SUM(ventas_items.subtotal_ars) as total'),
                DB::raw('SUM(ventas_items.cantidad) as unidades')
            )
            ->groupBy('categorias.nombre')
            ->orderByDesc('total')
            ->get();
    }

    /** Top 10 productos más vendidos por unidades */
    public function getProductosMasVendidosProperty()
    {
        [$desde, $hasta] = $this->fechas;

        return DB::table('ventas_items')
            ->join('ventas',   'ventas_items.venta_id',    '=', 'ventas.id')
            ->join('productos', 'ventas_items.producto_id', '=', 'productos.id')
            ->where('ventas.comercio_id', 1)
            ->whereBetween('ventas.fecha', [$desde, $hasta])
            ->select(
                'productos.nombre',
                DB::raw('SUM(ventas_items.cantidad) as total_vendido'),
                DB::raw('SUM(ventas_items.subtotal_ars) as total_ars')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();
    }

    /** Ventas diarias para el gráfico de barras */
    public function getVentasPorDiaProperty()
    {
        [$desde, $hasta] = $this->fechas;
        return Venta::where('comercio_id', 1)
            ->whereBetween('fecha', [$desde, $hasta])
            ->select(
                DB::raw('DATE(fecha) as dia'),
                DB::raw('SUM(total_ars) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();
    }

    public function render()
    {
        return view('livewire.reportes')
            ->layout('layouts.app', ['title' => 'Reportes']);
    }
}
