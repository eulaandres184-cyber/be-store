<?php
namespace App\Livewire\Documentos;

use Livewire\Component;
use App\Models\Venta;
use App\Models\Documento;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Services\DocumentoService;

/**
 * EmitirDocumento
 *
 * Componente Livewire para emitir cualquier tipo de documento
 * asociado a una venta: ticket, factura, recibo o presupuesto.
 */
class EmitirDocumento extends Component
{
    public int    $ventaId;
    public string $tipo          = 'ticket';
    public bool   $mostrarModal  = false;

    // Datos adicionales para factura
    public string $clienteNombre = '';
    public string $clienteDni    = '';
    public string $clienteDomicilio = '';

    // Datos para recibo con múltiples pagos
    public array  $pagos         = [];
    public string $pagoMetodo    = 'efectivo';
    public string $pagoMonto     = '';
    public string $pagoMoneda    = 'ARS';
    public string $observaciones = '';

    // Documento generado
    public ?int   $documentoId   = null;

    public function mount(int $ventaId): void
    {
        $this->ventaId = $ventaId;

        // Pre-cargar datos del cliente si existe
        $venta = Venta::with('cliente')->find($ventaId);
        if ($venta?->cliente) {
            $this->clienteNombre = $venta->cliente->nombre;
            $this->clienteDni    = $venta->cliente->dni ?? '';
        }
    }

    public function getVentaProperty()
    {
        return Venta::with(['items.producto', 'cliente', 'partePago'])
            ->find($this->ventaId);
    }

    public function getDolarProperty(): float
    {
        return (float) (Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0);
    }

    public function agregarPago(): void
    {
        if (!$this->pagoMonto || (float)$this->pagoMonto <= 0) return;

        $this->pagos[] = [
            'metodo' => $this->pagoMetodo,
            'monto'  => (float) $this->pagoMonto,
            'moneda' => $this->pagoMoneda,
        ];

        $this->pagoMonto  = '';
        $this->pagoMoneda = 'ARS';
    }

    public function quitarPago(int $index): void
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
    }

    public function getTotalPagadoProperty(): float
    {
        return collect($this->pagos)->sum(function ($p) {
            if ($p['moneda'] === 'USD') {
                return (float)$p['monto'] * $this->dolar;
            }
            return (float)$p['monto'];
        });
    }

    public function getSaldoProperty(): float
    {
        return $this->venta->total_ars - $this->totalPagado;
    }

    public function emitir(): void
    {
        $service = new DocumentoService();
        $venta   = $this->venta;

        $doc = match($this->tipo) {
            'ticket'  => $service->crearTicket($venta),
            'factura' => $service->crearFactura($venta, [
                'nombre'     => $this->clienteNombre,
                'dni'        => $this->clienteDni,
                'domicilio'  => $this->clienteDomicilio,
            ]),
            'recibo'  => $service->crearRecibo($venta, $this->pagos, $this->observaciones),
            default   => $service->crearTicket($venta),
        };

        $this->documentoId = $doc->id;
        session()->flash('doc_emitido', $doc->id);
    }

    public function render()
    {
        return view('livewire.documentos.emitir-documento')
            ->layout('layouts.app', ['title' => 'Emitir documento']);
    }
}
