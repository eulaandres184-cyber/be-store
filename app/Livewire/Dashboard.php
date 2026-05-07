<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\EquipoDetalle;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    protected $layout = 'layouts.app';
    protected $layoutData = ['title' => 'Dashboard'];

    public function render()
    {
        $comercioId = Auth::user()?->comercio_id ?? 1;
        $config = Configuracion::where('comercio_id', $comercioId)->first();

        $ventasHoy = Venta::where('comercio_id', $comercioId)
            ->whereDate('fecha', today())->sum('total_ars');

        $cantVentasHoy = Venta::where('comercio_id', $comercioId)
            ->whereDate('fecha', today())->count();

        $ultimasVentas = Venta::where('comercio_id', $comercioId)
            ->whereDate('fecha', today())
            ->with('items')
            ->latest('fecha')
            ->take(5)
            ->get();

        $totalProductos = Producto::where('comercio_id', $comercioId)
            ->where('activo', true)->count();

        $stockBajo = Producto::where('comercio_id', $comercioId)
            ->where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        $equiposDisponibles = EquipoDetalle::where('estado', 'disponible')
            ->whereHas('producto', fn($q) => $q->where('comercio_id', $comercioId))
            ->count();

        return view('livewire.dashboard', [
            'dolarHoy'           => $config?->dolar_blue_hoy ?? 0,
            'dolarActualizado'   => $config?->dolar_actualizado_en
                ? 'Actualizado ' . $config->dolar_actualizado_en->format('d/m/Y H:i') . ' hs'
                : 'Sin actualizar',
            'ventasHoy'          => $ventasHoy,
            'cantVentasHoy'      => $cantVentasHoy,
            'totalProductos'     => $totalProductos,
            'stockBajo'          => $stockBajo,
            'equiposDisponibles' => $equiposDisponibles,
            'ultimasVentas'      => $ultimasVentas,
        ]);
    }
}
