<?php
namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Models\EquipoDetalle;
use App\Models\Configuracion;

class ListaEquipos extends Component
{
    public string $busqueda     = '';
    public string $estadoFiltro = 'disponible';
    public string $marcaFiltro  = '';
    public string $ordenarPor   = 'created_at';
    public string $direccion    = 'desc';

    public function ordenar(string $columna): void
    {
        if ($this->ordenarPor === $columna) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $columna;
            $this->direccion  = 'asc';
        }
    }

    public function getMarcasProperty()
    {
        return EquipoDetalle::select('marca')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');
    }

    public function getDolarProperty()
    {
        return Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0;
    }

    public function render()
    {
        $equipos = EquipoDetalle::with('producto')
            ->whereHas('producto', fn($q) => $q->where('comercio_id', 1))
            ->when($this->busqueda, fn($q) => $q->where(function($q) {
                $q->where('marca',  'like', "%{$this->busqueda}%")
                  ->orWhere('modelo', 'like', "%{$this->busqueda}%")
                  ->orWhere('imei',   'like', "%{$this->busqueda}%")
                  ->orWhere('color',  'like', "%{$this->busqueda}%");
            }))
            ->when($this->estadoFiltro, fn($q) => $q->where('estado', $this->estadoFiltro))
            ->when($this->marcaFiltro,  fn($q) => $q->where('marca',  $this->marcaFiltro))
            ->orderBy($this->ordenarPor, $this->direccion)
            ->get();

        return view('livewire.equipos.lista-equipos', compact('equipos'))
            ->layout('layouts.app', ['title' => 'Equipos']);
    }
}
