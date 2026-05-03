<?php

namespace App\Livewire\Proveedores;

use App\Models\Proveedor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ListaProveedores extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $estado = 'activos';

    protected $queryString = ['busqueda', 'estado'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function proveedores()
    {
        return Proveedor::where('comercio_id', $this->comercioId())
            ->when(
                $this->busqueda,
                fn($q) =>
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                    ->orWhere('contacto', 'like', "%{$this->busqueda}%")
                    ->orWhere('whatsapp', 'like', "%{$this->busqueda}%")
            )
            ->when(
                $this->estado === 'activos',
                fn($q) =>
                $q->where('activo', true)
            )
            ->when(
                $this->estado === 'inactivos',
                fn($q) =>
                $q->where('activo', false)
            )
            ->orderBy('nombre')
            ->paginate(15);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function toggleActivo(int $proveedorId): void
    {
        $proveedor = Proveedor::find($proveedorId);
        if ($proveedor && $proveedor->comercio_id === $this->comercioId()) {
            $proveedor->update(['activo' => !$proveedor->activo]);
        }
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        return view('livewire.proveedores.lista-proveedores')
            ->layout('layouts.app', ['title' => 'Proveedores']);
    }
}
