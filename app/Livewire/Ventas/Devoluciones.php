<?php
namespace App\Livewire\Ventas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Devolucion;
use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

/**
 * Devoluciones
 * Gestiona devoluciones por falla, cambio o devolución de dinero.
 * Puede reponer el stock automáticamente y registrar el producto de cambio.
 */
class Devoluciones extends Component
{
    use WithPagination;

    // Formulario
    public bool   $mostrarForm    = false;
    public string $busquedaVenta  = '';
    public ?int   $ventaId        = null;
    public string $ventaInfo      = '';
    public string $tipo           = 'falla';
    public string $motivo         = '';
    public int    $cantidad        = 1;
    public bool   $reponerStock   = true;
    public string $montoDevuelto  = '0';
    public string $medioDevolucion = 'efectivo';
    public string $busquedaProductoNuevo = '';
    public ?int   $productoNuevoId = null;
    public string $productoNuevoNombre = '';
    public string $observaciones  = '';

    // Items de la venta seleccionada
    public array $itemsVenta = [];
    public int   $itemSeleccionado = 0;

    public function getVentasBuscadasProperty()
    {
        if (strlen($this->busquedaVenta) < 3) return collect();
        return Venta::where('comercio_id', 1)
            ->with(['items.producto', 'cliente'])
            ->where(function($q) {
                $q->where('id', 'like', "%{$this->busquedaVenta}%")
                  ->orWhereHas('cliente', fn($q) =>
                      $q->where('nombre', 'like', "%{$this->busquedaVenta}%")
                  );
            })
            ->latest('fecha')
            ->limit(5)
            ->get();
    }

    public function getProductosBuscadosProperty()
    {
        if (strlen($this->busquedaProductoNuevo) < 2) return collect();
        return Producto::where('comercio_id', 1)
            ->where('activo', true)
            ->buscar($this->busquedaProductoNuevo)
            ->limit(6)
            ->get();
    }

    public function seleccionarVenta(int $id): void
    {
        $venta = Venta::with(['items.producto', 'cliente'])->find($id);
        if (!$venta) return;

        $this->ventaId  = $id;
        $this->ventaInfo = "Venta #{$id} — " . $venta->fecha->format('d/m/Y') .
                           " — $" . number_format($venta->total_ars, 0, ',', '.') .
                           ($venta->cliente ? " — {$venta->cliente->nombre}" : '');
        $this->itemsVenta = $venta->items->map(fn($i) => [
            'id'      => $i->id,
            'nombre'  => $i->producto?->nombre ?? 'Producto',
            'cantidad'=> $i->cantidad,
            'precio'  => $i->precio_unitario_ars,
        ])->toArray();

        $this->busquedaVenta = '';
        if (!empty($this->itemsVenta)) {
            $this->itemSeleccionado = 0;
            $this->montoDevuelto = (string)$this->itemsVenta[0]['precio'];
        }
    }

    public function updatedItemSeleccionado(): void
    {
        if (isset($this->itemsVenta[$this->itemSeleccionado])) {
            $this->montoDevuelto = (string)$this->itemsVenta[$this->itemSeleccionado]['precio'];
        }
    }

    public function seleccionarProductoNuevo(int $id, string $nombre): void
    {
        $this->productoNuevoId     = $id;
        $this->productoNuevoNombre = $nombre;
        $this->busquedaProductoNuevo = '';
    }

    public function registrar(): void
    {
        $this->validate([
            'ventaId'  => 'required|exists:ventas,id',
            'tipo'     => 'required|in:falla,cambio,devolucion_dinero',
            'motivo'   => 'required|string|min:5',
            'cantidad' => 'required|integer|min:1',
        ], [
            'ventaId.required'  => 'Seleccioná una venta.',
            'motivo.required'   => 'Describí el motivo.',
            'motivo.min'        => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $venta   = Venta::find($this->ventaId);
        $item    = $this->itemsVenta[$this->itemSeleccionado] ?? null;
        $producto = $item ? Producto::find(
            Venta::find($this->ventaId)->items[$this->itemSeleccionado]->producto_id ?? 0
        ) : null;

        DB::transaction(function () use ($venta, $producto) {
            // Registrar la devolución
            Devolucion::create([
                'comercio_id'      => 1,
                'venta_id'         => $this->ventaId,
                'user_id'          => auth()->id(),
                'cliente_id'       => $venta->cliente_id,
                'tipo'             => $this->tipo,
                'motivo'           => $this->motivo,
                'cantidad'         => $this->cantidad,
                'monto_devuelto'   => $this->tipo === 'devolucion_dinero' ? (float)$this->montoDevuelto : 0,
                'medio_devolucion' => $this->tipo === 'devolucion_dinero' ? $this->medioDevolucion : null,
                'producto_nuevo_id'=> $this->tipo === 'cambio' ? $this->productoNuevoId : null,
                'stock_repuesto'   => $this->reponerStock,
                'observaciones'    => $this->observaciones ?: null,
            ]);

            // Reponer stock del producto devuelto si corresponde
            if ($this->reponerStock && $producto) {
                $producto->increment('stock_actual', $this->cantidad);
            }

            // Si es cambio, descontar stock del producto nuevo
            if ($this->tipo === 'cambio' && $this->productoNuevoId) {
                Producto::where('id', $this->productoNuevoId)
                    ->decrement('stock_actual', $this->cantidad);
            }
        });

        session()->flash('success', 'Devolución registrada correctamente.');
        $this->reset(['ventaId','ventaInfo','tipo','motivo','cantidad',
                      'reponerStock','montoDevuelto','productoNuevoId',
                      'productoNuevoNombre','observaciones','itemsVenta']);
        $this->mostrarForm = false;
    }

    public function render()
    {
        $devoluciones = Devolucion::where('comercio_id', 1)
            ->with(['venta','cliente','productoNuevo','usuario'])
            ->latest()
            ->paginate(15);

        return view('livewire.ventas.devoluciones', compact('devoluciones'))
            ->layout('layouts.app', ['title' => 'Devoluciones']);
    }
}
