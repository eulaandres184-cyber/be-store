<?php
namespace App\Services;

use App\Models\Comercio;
use App\Models\Documento;
use App\Models\Venta;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;

/**
 * DocumentoService
 *
 * Centraliza la lógica de creación de documentos (ticket, factura, recibo, presupuesto).
 * Todos los documentos guardan un snapshot de los ítems para preservar
 * el historial aunque los productos cambien en el futuro.
 */
class DocumentoService
{
    /**
     * Crea un ticket no válido como factura a partir de una venta.
     */
    public function crearTicket(Venta $venta): Documento
    {
        $comercio = Comercio::find($venta->comercio_id);
        $venta->load(['items.producto', 'cliente']);

        return DB::transaction(function () use ($venta, $comercio) {
            return Documento::create([
                'comercio_id'  => $comercio->id,
                'venta_id'     => $venta->id,
                'cliente_id'   => $venta->cliente_id,
                'user_id'      => $venta->user_id,
                'tipo'         => 'ticket',
                'numero'       => $comercio->siguienteNumero('ticket'),
                'subtotal'     => $venta->subtotal_ars,
                'descuento'    => 0,
                'total'        => $venta->total_ars,
                'moneda'       => 'ARS',
                'dolar_blue'   => $venta->dolar_blue_usado,
                'items'        => $this->snapshotItems($venta),
                'pagos'        => [['metodo' => $venta->medio_pago, 'monto' => $venta->total_ars]],
                'emitido_en'   => now(),
            ]);
        });
    }

    /**
     * Crea una Factura C (Monotributista) a partir de una venta.
     */
    public function crearFactura(Venta $venta, ?array $datosCliente = null): Documento
    {
        $comercio = Comercio::find($venta->comercio_id);
        $venta->load(['items.producto', 'cliente']);

        return DB::transaction(function () use ($venta, $comercio, $datosCliente) {
            return Documento::create([
                'comercio_id'  => $comercio->id,
                'venta_id'     => $venta->id,
                'cliente_id'   => $venta->cliente_id,
                'user_id'      => $venta->user_id,
                'tipo'         => 'factura',
                'numero'       => $comercio->siguienteNumero('factura'),
                'subtotal'     => $venta->subtotal_ars,
                'descuento'    => 0,
                'total'        => $venta->total_ars,
                'moneda'       => 'ARS',
                'dolar_blue'   => $venta->dolar_blue_usado,
                'items'        => $this->snapshotItems($venta),
                'pagos'        => [['metodo' => $venta->medio_pago, 'monto' => $venta->total_ars]],
                'observaciones'=> $datosCliente ? json_encode($datosCliente) : null,
                'emitido_en'   => now(),
            ]);
        });
    }

    /**
     * Crea un recibo de pago para ventas con múltiples métodos de pago.
     * Usado principalmente para equipos con pago parcial.
     *
     * @param array $pagos [['metodo'=>'efectivo','monto'=>50000,'moneda'=>'ARS'], ...]
     */
    public function crearRecibo(Venta $venta, array $pagos, ?string $observaciones = null): Documento
    {
        $comercio = Comercio::find($venta->comercio_id);
        $venta->load(['items.producto', 'cliente']);
        $config = Configuracion::where('comercio_id', $comercio->id)->first();

        $totalPagado = collect($pagos)->sum(function ($p) use ($config) {
            // Convertir USD a ARS si corresponde
            if (($p['moneda'] ?? 'ARS') === 'USD') {
                return (float)$p['monto'] * ($config->dolar_blue_hoy ?? 1);
            }
            return (float)$p['monto'];
        });

        return DB::transaction(function () use ($venta, $comercio, $pagos, $totalPagado, $observaciones, $config) {
            return Documento::create([
                'comercio_id'  => $comercio->id,
                'venta_id'     => $venta->id,
                'cliente_id'   => $venta->cliente_id,
                'user_id'      => auth()->id(),
                'tipo'         => 'recibo',
                'numero'       => $comercio->siguienteNumero('recibo'),
                'subtotal'     => $venta->total_ars,
                'descuento'    => max(0, $venta->total_ars - $totalPagado),
                'total'        => $totalPagado,
                'moneda'       => 'ARS',
                'dolar_blue'   => $config?->dolar_blue_hoy,
                'items'        => $this->snapshotItems($venta),
                'pagos'        => $pagos,
                'observaciones'=> $observaciones,
                'emitido_en'   => now(),
            ]);
        });
    }

    /**
     * Crea un presupuesto (sin venta asociada).
     * Para equipos muestra precio en USD y el equivalente en ARS al dólar del día.
     *
     * @param array $items [['nombre'=>'...','precio_usd'=>850,'precio_ars'=>1215000], ...]
     */
    public function crearPresupuesto(array $items, ?int $clienteId = null, ?string $obs = null): Documento
    {
        $comercio = Comercio::find(1);
        $config   = Configuracion::where('comercio_id', 1)->first();
        $total    = collect($items)->sum('precio_ars');

        return DB::transaction(function () use ($comercio, $items, $clienteId, $obs, $config, $total) {
            return Documento::create([
                'comercio_id'  => $comercio->id,
                'venta_id'     => null,
                'cliente_id'   => $clienteId,
                'user_id'      => auth()->id(),
                'tipo'         => 'presupuesto',
                'numero'       => $comercio->siguienteNumero('presupuesto'),
                'subtotal'     => $total,
                'descuento'    => 0,
                'total'        => $total,
                'moneda'       => 'ARS',
                'dolar_blue'   => $config?->dolar_blue_hoy,
                'items'        => $items,
                'pagos'        => null,
                'observaciones'=> $obs,
                'emitido_en'   => now(),
            ]);
        });
    }

    /**
     * Genera un snapshot de los ítems de la venta.
     * Guarda el estado exacto al momento de emitir el documento.
     */
    private function snapshotItems(Venta $venta): array
    {
        return $venta->items->map(fn($item) => [
            'nombre'         => $item->producto?->nombre ?? 'Producto eliminado',
            'cantidad'       => $item->cantidad,
            'precio_unit'    => $item->precio_unitario_ars,
            'subtotal'       => $item->subtotal_ars,
            'precio_usd'     => $item->precio_usd_snapshot,
        ])->toArray();
    }
}
