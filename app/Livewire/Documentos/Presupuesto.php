<?php
namespace App\Livewire\Documentos;

use Livewire\Component;
use App\Models\EquipoDetalle;
use App\Models\Configuracion;
use App\Services\DocumentoService;

/**
 * Presupuesto
 * Genera presupuestos para equipos con cotización del dólar blue del día.
 * No requiere una venta previa — es un documento independiente.
 */
class Presupuesto extends Component
{
    public array  $items       = [];
    public string $busqueda    = '';
    public string $observaciones = '';
    public ?int   $documentoId = null;

    public function getDolarProperty(): float
    {
        return (float)(Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0);
    }

    public function getEquiposBuscadosProperty()
    {
        if (strlen($this->busqueda) < 2) return collect();
        return EquipoDetalle::where('estado', 'disponible')
            ->where(function($q) {
                $q->where('marca',  'like', "%{$this->busqueda}%")
                  ->orWhere('modelo', 'like', "%{$this->busqueda}%");
            })
            ->limit(6)
            ->get();
    }

    public function agregarEquipo(int $id): void
    {
        $equipo = EquipoDetalle::find($id);
        if (!$equipo) return;

        $this->items[] = [
            'nombre'     => "{$equipo->marca} {$equipo->modelo}" .
                            ($equipo->capacidad_gb ? " {$equipo->capacidad_gb}GB" : '') .
                            ($equipo->color ? " {$equipo->color}" : ''),
            'precio_usd' => (float)$equipo->precio_usd,
            'precio_ars' => (float)$equipo->precio_usd * $this->dolar,
        ];

        $this->busqueda = '';
    }

    public function quitarItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
    }

    public function getTotalUsdProperty(): float
    {
        return collect($this->items)->sum('precio_usd');
    }

    public function getTotalArsProperty(): float
    {
        return collect($this->items)->sum('precio_ars');
    }

    public function generar(): void
    {
        if (empty($this->items)) return;
        $service = new DocumentoService();
        $doc = $service->crearPresupuesto($this->items, null, $this->observaciones);
        $this->documentoId = $doc->id;
    }

    public function render()
    {
        return view('livewire.documentos.presupuesto')
            ->layout('layouts.app', ['title' => 'Nuevo presupuesto']);
    }
}
