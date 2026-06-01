<?php
namespace App\Livewire\Documentos;

use Livewire\Component;
use App\Models\Venta;
use App\Models\Documento;
use App\Models\Configuracion;
use App\Services\DocumentoService;
use App\Services\AfipService;

class EmitirDocumento extends Component
{
    public int    $ventaId;
    public string $tipo          = 'ticket';
    public bool   $mostrarModal  = false;
    public string $clienteNombre = '';
    public string $clienteDni    = '';
    public string $clienteDomicilio = '';
    public array  $pagos         = [];
    public string $pagoMetodo    = 'efectivo';
    public string $pagoMonto     = '';
    public string $pagoMoneda    = 'ARS';
    public string $observaciones = '';
    public ?int   $documentoId   = null;
    public ?string $cae          = null;
    public ?string $caeVencimiento = null;
    public ?string $errorAfip    = null;
    public bool   $afipDisponible = true;

    public function mount(int $ventaId): void
    {
        $this->ventaId = $ventaId;
        $venta = Venta::with('cliente')->find($ventaId);
        if ($venta?->cliente) {
            $this->clienteNombre = $venta->cliente->nombre;
            $this->clienteDni    = $venta->cliente->dni ?? '';
        }
    }

    public function getVentaProperty()
    {
        return Venta::with(['items.producto', 'cliente', 'partePago'])->find($this->ventaId);
    }

    public function getDolarProperty(): float
    {
        return (float)(Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0);
    }

    public function agregarPago(): void
    {
        if (!$this->pagoMonto || (float)$this->pagoMonto <= 0) return;
        $this->pagos[] = ['metodo' => $this->pagoMetodo, 'monto' => (float)$this->pagoMonto, 'moneda' => $this->pagoMoneda];
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
        return collect($this->pagos)->sum(function($p) {
            return ($p['moneda'] === 'USD') ? (float)$p['monto'] * $this->dolar : (float)$p['monto'];
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
        $this->errorAfip = null;

        $doc = match($this->tipo) {
            'factura' => $service->crearFactura($venta, [
                'nombre'    => $this->clienteNombre,
                'dni'       => $this->clienteDni,
                'domicilio' => $this->clienteDomicilio,
            ]),
            'recibo'  => $service->crearRecibo($venta, $this->pagos, $this->observaciones),
            default   => $service->crearTicket($venta),
        };

        // Si es factura, solicitar CAE a AFIP
        if ($this->tipo === 'factura') {
            try {
                $afip   = new AfipService();
                $result = $afip->generarCAE($doc);
                $this->cae           = $result['cae'];
                $this->caeVencimiento = $result['vencimiento'];
            } catch (\Exception $e) {
                // Si AFIP falla, el documento queda guardado sin CAE
                // El usuario puede reintentar o emitir como ticket
                $this->errorAfip = $e->getMessage();
                \Log::error('Error AFIP: ' . $e->getMessage());
            }
        }

        $this->documentoId = $doc->id;
    }

    public function render()
    {
        return view('livewire.documentos.emitir-documento')
            ->layout('layouts.app', ['title' => 'Emitir documento']);
    }
}
