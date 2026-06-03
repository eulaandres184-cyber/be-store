<?php
namespace App\Livewire\Documentos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Documento;

/**
 * ListaDocumentos
 * Historial de todos los documentos emitidos con filtros por tipo y fecha.
 */
class ListaDocumentos extends Component
{
    use WithPagination;

    public string $tipoFiltro = '';
    public string $desde      = '';
    public string $hasta      = '';
    public string $busqueda   = '';

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->format('Y-m-d');
        $this->hasta = now()->format('Y-m-d');
    }

    public function render()
    {
        $documentos = Documento::where('comercio_id', 1)
            ->with(['cliente', 'usuario', 'venta'])
            ->when($this->tipoFiltro, fn($q) => $q->where('tipo', $this->tipoFiltro))
            ->when($this->desde,     fn($q) => $q->whereDate('emitido_en', '>=', $this->desde))
            ->when($this->hasta,     fn($q) => $q->whereDate('emitido_en', '<=', $this->hasta))
            ->when($this->busqueda,  fn($q) => $q->where('numero', 'like', "%{$this->busqueda}%"))
            ->orderByDesc('emitido_en')
            ->paginate(20);

        return view('livewire.documentos.lista-documentos', compact('documentos'))
            ->layout('layouts.app', ['title' => 'Documentos emitidos']);
    }
}
