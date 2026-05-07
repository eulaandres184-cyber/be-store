<?php

namespace App\Livewire\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;

class FormCompra extends Component
{
    public string $proveedor_id = '';
    public string $fecha        = '';
    public string $notas        = '';
    public array  $items        = [];

    // Buscador de productos para agregar
    public string $busquedaProducto = '';
    public ?int   $productoSelId    = null;
    public string $cantidadItem     = '1';
    public string $costoItem        = '';
    public string $monedaItem       = 'ARS';

    public function mount(): void
    {
        $this->fecha = now()->format('Y-m-d');
    }

    public function getProveedoresProperty()
    {
        return Proveedor::where('comercio_id', 1)->where('activo', true)->orderBy('nombre')->get();
    }

    public function getProductosBuscadosProperty()
    {
        if (strlen($this->busquedaProducto) < 2) return collect();
        return Producto::where('comercio_id', 1)
            ->where('activo', true)
            ->buscar($this->busquedaProducto)
            ->limit(8)
            ->get();
    }

    public function seleccionarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if (!$producto) return;

        $this->productoSelId  = $id;
        $this->busquedaProducto = $producto->nombre;
        $this->costoItem      = (string) $producto->precio_efectivo;
        $this->monedaItem     = $producto->moneda;
    }

    public function agregarItem(): void
    {
        if (!$this->productoSelId || !$this->cantidadItem || !$this->costoItem) return;

        $producto = Producto::find($this->productoSelId);
        if (!$producto) return;

        $key = 'item_' . $this->productoSelId;
        if (isset($this->items[$key])) {
            $this->items[$key]['cantidad'] += (int) $this->cantidadItem;
        } else {
            $this->items[$key] = [
                'producto_id' => $this->productoSelId,
                'nombre'      => $producto->nombre,
                'cantidad'    => (int) $this->cantidadItem,
                'costo'       => (float) $this->costoItem,
                'moneda'      => $this->monedaItem,
            ];
        }

        $this->busquedaProducto = '';
        $this->productoSelId    = null;
        $this->cantidadItem     = '1';
        $this->costoItem        = '';
    }

    public function quitarItem(string $key): void
    {
        unset($this->items[$key]);
    }

    public function getTotalProperty(): float
    {
        return collect($this->items)->sum(fn($i) => $i['costo'] * $i['cantidad']);
    }

    public function guardar(): void
    {
        $this->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha'        => 'required|date',
            'items'        => 'required|min:1',
        ], [
            'proveedor_id.required' => 'Seleccioná un proveedor.',
            'items.required'        => 'Agregá al menos un producto.',
            'items.min'             => 'Agregá al menos un producto.',
        ]);

        DB::transaction(function () {
            $compra = Compra::create([
                'comercio_id'  => 1,
                'proveedor_id' => (int) $this->proveedor_id,
                'user_id'      => auth()->id(),
                'fecha'        => $this->fecha,
                'total_ars'    => $this->total,
                'notas'        => $this->notas ?: null,
            ]);

            foreach ($this->items as $item) {
                CompraItem::create([
                    'compra_id'     => $compra->id,
                    'producto_id'   => $item['producto_id'],
                    'cantidad'      => $item['cantidad'],
                    'costo_unitario'=> $item['costo'],
                    'moneda'        => $item['moneda'],
                ]);

                // Actualizar stock
                Producto::where('id', $item['producto_id'])
                    ->increment('stock_actual', $item['cantidad']);
            }
        });

        session()->flash('success', 'Compra registrada y stock actualizado.');
        $this->redirect(route('compras'));
    }

    public function render()
    {
        return view('livewire.compras.form-compra')
            ->layout('layouts.app', ['title' => 'Nueva Compra']);
    }
}
