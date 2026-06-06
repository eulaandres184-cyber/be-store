<?php

namespace App\Livewire\Auditoria;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HistorialPrecioProducto;
use App\Models\HistorialConfiguracion;
use Illuminate\Support\Facades\Auth;

/**
 * HistorialCambios - Componente Livewire para visualizar el historial de cambios
 */
class HistorialCambios extends Component
{
    use WithPagination;

    public string $tipoHistorial = 'precios'; // precios, configuracion, ambos
    public string $busqueda = '';
    public string $filtroUsuario = '';
    public string $filtroFecha = '';
    public int $diasAtras = 30;

    protected $queryString = ['tipoHistorial', 'busqueda', 'diasAtras'];

    /**
     * Obtener el historial según el tipo seleccionado
     */
    public function getHistorialProperty()
    {
        $comercioId = Auth::user()?->comercio_id ?? 1;

        if ($this->tipoHistorial === 'precios') {
            return $this->obtenerHistorialPrecios($comercioId);
        } elseif ($this->tipoHistorial === 'configuracion') {
            return $this->obtenerHistorialConfiguracion($comercioId);
        }

        // Si es 'ambos', combinar ambos (opcional)
        return collect();
    }

    /**
     * Obtener historial de cambios de precios
     */
    private function obtenerHistorialPrecios($comercioId)
    {
        return HistorialPrecioProducto::query()
            ->where('comercio_id', $comercioId)
            ->when($this->busqueda, function ($q) {
                $q->whereHas('producto', function ($query) {
                    $query->where('nombre', 'like', "%{$this->busqueda}%")
                        ->orWhere('codigo_interno', 'like', "%{$this->busqueda}%");
                });
            })
            ->when($this->filtroUsuario, function ($q) {
                $q->where('usuario_id', $this->filtroUsuario);
            })
            ->where('cambio_en', '>=', now()->subDays($this->diasAtras))
            ->with('producto', 'usuario')
            ->orderByDesc('cambio_en')
            ->paginate(20);
    }

    /**
     * Obtener historial de cambios de configuración
     */
    private function obtenerHistorialConfiguracion($comercioId)
    {
        return HistorialConfiguracion::query()
            ->where('comercio_id', $comercioId)
            ->when($this->busqueda, function ($q) {
                $q->where('campo', 'like', "%{$this->busqueda}%")
                    ->orWhere('descripcion', 'like', "%{$this->busqueda}%");
            })
            ->when($this->filtroUsuario, function ($q) {
                $q->where('usuario_id', $this->filtroUsuario);
            })
            ->where('cambio_en', '>=', now()->subDays($this->diasAtras))
            ->with('usuario')
            ->orderByDesc('cambio_en')
            ->paginate(20);
    }

    /**
     * Resetear paginación cuando cambia búsqueda
     */
    public function updatingBusqueda()
    {
        $this->resetPage();
    }

    public function updatingTipoHistorial()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.auditoria.historial-cambios', [
            'historial' => $this->historial,
        ])->layout('layouts.app', ['title' => 'Historial de Cambios']);
    }
}
