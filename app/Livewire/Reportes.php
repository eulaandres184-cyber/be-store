<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reportes extends Component
{
    public string $periodo = '7';

    public function render()
    {
        $comercioId = Auth::user()?->comercio_id ?? 1;
        $dias = (int) $this->periodo;

        // Calular métricas para el período
        $ventasPeriodo = Venta::where('comercio_id', $comercioId)
            ->whereBetween('fecha', [now()->subDays($dias), now()])
            ->sum('total_ars');

        $comprasPeriodo = Compra::where('comercio_id', $comercioId)
            ->whereBetween('fecha', [now()->subDays($dias), now()])
            ->sum('total_ars');

        $ganancia = $ventasPeriodo - $comprasPeriodo;

        $productosMasVendidos = Producto::where('comercio_id', $comercioId)
            ->with([
                'ventasItems' => fn($q) =>
                $q->whereHas(
                    'venta',
                    fn($vq) =>
                    $vq->whereBetween('fecha', [now()->subDays($dias), now()])
                )
            ])
            ->get()
            ->map(fn($p) => [
                'nombre' => $p->nombre,
                'cantidad' => $p->ventasItems->sum('cantidad'),
                'monto' => $p->ventasItems->sum('subtotal_ars'),
            ])
            ->sortByDesc('cantidad')
            ->take(5);

        $ventasPorMedio = Venta::where('comercio_id', $comercioId)
            ->whereBetween('fecha', [now()->subDays($dias), now()])
            ->selectRaw('medio_pago, COUNT(*) as cantidad, SUM(total_ars) as total')
            ->groupBy('medio_pago')
            ->get();

        return view('livewire.reportes', [
            'ventasPeriodo' => $ventasPeriodo,
            'comprasPeriodo' => $comprasPeriodo,
            'ganancia' => $ganancia,
            'productosMasVendidos' => $productosMasVendidos,
            'ventasPorMedio' => $ventasPorMedio,
        ])->layout('layouts.app', ['title' => 'Reportes']);
    }
}
