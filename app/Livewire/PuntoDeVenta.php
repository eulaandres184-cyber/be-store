<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Configuracion;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PuntoDeVenta extends Component
{
    // Búsqueda
    public string $busqueda = '';
    public string $categoriaFiltro = '';

    // Carrito
    public array $carrito = [];

    // Pago
    public string $medioPago = 'efectivo';
    public string $notas = '';

    // UI
    public bool $ventaExitosa = false;
    public int $ultimaVentaId = 0;

    protected $listeners = ['agregarProducto'];

    public function getProductosProperty()
    {
        if (strlen($this->busqueda) < 1 && !$this->categoriaFiltro) {
            return collect();
        }

        return Producto::where('comercio_id', $this->comercioId())
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->when(
                $this->busqueda,
                fn($q) =>
                $q->where('nombre', 'like', "%{$this->busqueda}%")
            )
            ->when(
                $this->categoriaFiltro,
                fn($q) =>
                $q->where('categoria_id', $this->categoriaFiltro)
            )
            ->with('categoria')
            ->orderBy('nombre')
            ->limit(12)
            ->get();
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', $this->comercioId())
            ->where('tipo', 'accesorio')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function getConfigProperty()
    {
        return Configuracion::where('comercio_id', $this->comercioId())->first();
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->carrito)->sum(fn($item) => $item['precio'] * $item['cantidad']);
    }

    public function getRecargoMontoProperty(): float
    {
        $pct = match ($this->medioPago) {
            'tarjeta'   => $this->config?->recargo_tarjeta ?? 15,
            'cuotas_4'  => $this->config?->cuotas_4_recargo ?? 2,
            'cuotas_20' => $this->config?->cuotas_20_recargo ?? 20,
            default     => 0,
        };
        return $this->subtotal * ($pct / 100);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->recargoMonto;
    }

    public function getLabelMedioPagoProperty(): string
    {
        return match ($this->medioPago) {
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia / MP',
            'tarjeta'       => 'Tarjeta +' . ($this->config?->recargo_tarjeta ?? 15) . '%',
            'cuotas_4'      => 'BLP 4 cuotas +' . ($this->config?->cuotas_4_recargo ?? 2) . '%',
            'cuotas_20'     => 'BLP 20 cuotas +' . ($this->config?->cuotas_20_recargo ?? 20) . '%',
            default         => $this->medioPago,
        };
    }

    public function agregarAlCarrito(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if (!$producto) return;

        $key = 'p_' . $productoId;

        if (isset($this->carrito[$key])) {
            if ($this->carrito[$key]['cantidad'] >= $producto->stock_actual) return;
            $this->carrito[$key]['cantidad']++;
        } else {
            $this->carrito[$key] = [
                'id'       => $producto->id,
                'nombre'   => $producto->nombre,
                'precio'   => (float) $producto->precio_efectivo,
                'cantidad' => 1,
                'stock'    => $producto->stock_actual,
            ];
        }
    }

    public function quitarDelCarrito(string $key): void
    {
        unset($this->carrito[$key]);
    }

    public function cambiarCantidad(string $key, int $delta): void
    {
        if (!isset($this->carrito[$key])) return;
        $nueva = $this->carrito[$key]['cantidad'] + $delta;
        if ($nueva <= 0) {
            unset($this->carrito[$key]);
        } elseif ($nueva <= $this->carrito[$key]['stock']) {
            $this->carrito[$key]['cantidad'] = $nueva;
        }
    }

    public function vaciarCarrito(): void
    {
        $this->carrito = [];
        $this->medioPago = 'efectivo';
        $this->notas = '';
    }

    public function registrarVenta(): void
    {
        if (empty($this->carrito)) return;

        $config = $this->config;
        $recargoPct = match ($this->medioPago) {
            'tarjeta'   => $config?->recargo_tarjeta ?? 15,
            'cuotas_4'  => $config?->cuotas_4_recargo ?? 2,
            'cuotas_20' => $config?->cuotas_20_recargo ?? 20,
            default     => 0,
        };

        DB::transaction(function () use ($recargoPct, $config) {
            $venta = Venta::create([
                'comercio_id'      => $this->comercioId(),
                'user_id'          => Auth::id(),
                'fecha'            => now(),
                'medio_pago'       => $this->medioPago,
                'subtotal_ars'     => $this->subtotal,
                'recargo_aplicado' => $recargoPct,
                'total_ars'        => $this->total,
                'dolar_blue_usado' => $config?->dolar_blue_hoy,
                'notas'            => $this->notas,
            ]);

            foreach ($this->carrito as $item) {
                VentaItem::create([
                    'venta_id'           => $venta->id,
                    'producto_id'        => $item['id'],
                    'cantidad'           => $item['cantidad'],
                    'precio_unitario_ars' => $item['precio'],
                    'subtotal_ars'       => $item['precio'] * $item['cantidad'],
                ]);

                // Descontar stock
                Producto::where('id', $item['id'])
                    ->decrement('stock_actual', $item['cantidad']);
            }

            $this->ultimaVentaId = $venta->id;
        });

        $this->carrito = [];
        $this->medioPago = 'efectivo';
        $this->notas = '';
        $this->busqueda = '';
        $this->ventaExitosa = true;
    }

    public function nuevaVenta(): void
    {
        $this->ventaExitosa = false;
        $this->ultimaVentaId = 0;
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        return view('livewire.punto-de-venta')
            ->layout('layouts.app', ['title' => 'Punto de Venta']);
    }
}
