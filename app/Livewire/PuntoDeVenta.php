<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Configuracion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\EquipoPartePago;
use Illuminate\Support\Facades\DB;

class PuntoDeVenta extends Component
{
    public string $busqueda       = '';
    public string $categoriaFiltro = '';
    public array  $carrito        = [];
    public string $medioPago      = 'efectivo';
    public string $notas          = '';
    public bool   $ventaExitosa   = false;
    public int    $ultimaVentaId  = 0;

    // Cliente
    public string $busquedaCliente = '';
    public ?int   $clienteId       = null;
    public string $clienteNombre   = '';

    // Parte de pago
    public bool   $tieneParte      = false;
    public string $parteMarca      = '';
    public string $parteModelo     = '';
    public string $parteImei       = '';
    public string $parteColor      = '';
    public string $parteCondicion  = 'bueno';
    public string $parteBateria    = '';
    public string $parteCotUsd     = '';

    public function getProductosProperty()
    {
        return Producto::where('comercio_id', 1)
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->when($this->busqueda, fn($q) => $q->buscar($this->busqueda))
            ->when($this->categoriaFiltro, fn($q) => $q->where('categoria_id', $this->categoriaFiltro))
            ->with('categoria')
            ->orderBy('nombre')
            ->limit(40)
            ->get();
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function getConfigProperty()
    {
        return Configuracion::where('comercio_id', 1)->first();
    }

    public function getClientesBuscadosProperty()
    {
        if (strlen($this->busquedaCliente) < 2) return collect();
        return Cliente::where('comercio_id', 1)
            ->activos()
            ->buscar($this->busquedaCliente)
            ->limit(5)
            ->get();
    }

    public function seleccionarCliente(int $id, string $nombre): void
    {
        $this->clienteId     = $id;
        $this->clienteNombre = $nombre;
        $this->busquedaCliente = '';
    }

    public function quitarCliente(): void
    {
        $this->clienteId     = null;
        $this->clienteNombre = '';
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->carrito)->sum(fn($i) => $i['precio'] * $i['cantidad']);
    }

    public function getParteCotArsProperty(): float
    {
        $dolar = $this->config?->dolar_blue_hoy ?? 0;
        return (float)$this->parteCotUsd * $dolar;
    }

    public function getRecargoMontoProperty(): float
    {
        $pct = match($this->medioPago) {
            'tarjeta'   => $this->config?->recargo_tarjeta   ?? 15,
            'cuotas_4'  => $this->config?->cuotas_4_recargo  ?? 2,
            'cuotas_20' => $this->config?->cuotas_20_recargo ?? 20,
            default     => 0,
        };
        return $this->subtotal * ($pct / 100);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->recargoMonto - ($this->tieneParte ? $this->parteCotArs : 0);
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
                'precio'   => (float)$producto->precio_efectivo,
                'cantidad' => 1,
                'stock'    => $producto->stock_actual,
            ];
        }
    }

    public function quitarDelCarrito(string $key): void { unset($this->carrito[$key]); }

    public function cambiarCantidad(string $key, int $delta): void
    {
        if (!isset($this->carrito[$key])) return;
        $nueva = $this->carrito[$key]['cantidad'] + $delta;
        if ($nueva <= 0) unset($this->carrito[$key]);
        elseif ($nueva <= $this->carrito[$key]['stock']) $this->carrito[$key]['cantidad'] = $nueva;
    }

    public function vaciarCarrito(): void
    {
        $this->carrito = [];
        $this->medioPago = 'efectivo';
        $this->notas = '';
        $this->tieneParte = false;
        $this->parteCotUsd = '';
    }

    public function registrarVenta(): void
    {
        if (empty($this->carrito)) return;
        $config = $this->config;
        $recargoPct = match($this->medioPago) {
            'tarjeta'   => $config?->recargo_tarjeta   ?? 15,
            'cuotas_4'  => $config?->cuotas_4_recargo  ?? 2,
            'cuotas_20' => $config?->cuotas_20_recargo ?? 20,
            default     => 0,
        };

        DB::transaction(function () use ($recargoPct, $config) {
            $venta = Venta::create([
                'comercio_id'      => 1,
                'user_id'          => auth()->id(),
                'cliente_id'       => $this->clienteId,
                'fecha'            => now(),
                'medio_pago'       => $this->medioPago,
                'subtotal_ars'     => $this->subtotal,
                'recargo_aplicado' => $recargoPct,
                'total_ars'        => $this->total,
                'dolar_blue_usado' => $config?->dolar_blue_hoy,
                'notas'            => $this->notas ?: null,
            ]);

            foreach ($this->carrito as $item) {
                VentaItem::create([
                    'venta_id'            => $venta->id,
                    'producto_id'         => $item['id'],
                    'cantidad'            => $item['cantidad'],
                    'precio_unitario_ars' => $item['precio'],
                    'subtotal_ars'        => $item['precio'] * $item['cantidad'],
                ]);
                Producto::where('id', $item['id'])->decrement('stock_actual', $item['cantidad']);
            }

            if ($this->tieneParte && $this->parteCotUsd > 0) {
                EquipoPartePago::create([
                    'venta_id'        => $venta->id,
                    'cliente_id'      => $this->clienteId,
                    'marca'           => $this->parteMarca,
                    'modelo'          => $this->parteModelo,
                    'imei'            => $this->parteImei    ?: null,
                    'color'           => $this->parteColor   ?: null,
                    'condicion'       => $this->parteCondicion,
                    'bateria_pct'     => $this->parteBateria ?: null,
                    'cotizacion_usd'  => (float)$this->parteCotUsd,
                    'dolar_blue_usado'=> $config?->dolar_blue_hoy ?? 0,
                    'valor_ars'       => $this->parteCotArs,
                    'observaciones'   => null,
                ]);
            }

            $this->ultimaVentaId = $venta->id;
        });

        $this->carrito = [];
        $this->medioPago = 'efectivo';
        $this->notas = '';
        $this->busqueda = '';
        $this->tieneParte = false;
        $this->parteCotUsd = '';
        $this->clienteId = null;
        $this->clienteNombre = '';
        $this->ventaExitosa = true;
    }

    public function nuevaVenta(): void
    {
        $this->ventaExitosa  = false;
        $this->ultimaVentaId = 0;
    }

    public function render()
    {
        return view('livewire.punto-de-venta')
            ->layout('layouts.app', ['title' => 'Punto de Venta']);
    }
}
