<?php
namespace App\Livewire\Proveedores;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proveedor;

class ListaProveedores extends Component
{
    use WithPagination;

    public string $busqueda    = '';
    public string $ordenarPor  = 'nombre';
    public string $direccion   = 'asc';
    public bool   $mostrarForm = false;
    public ?int   $proveedorId = null;
    public string $nombre      = '';
    public string $contacto    = '';
    public string $whatsapp    = '';
    public string $notas       = '';
    public bool   $activo      = true;
    public bool   $modoEdicion = false;

    protected function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:100',
            'contacto' => 'nullable|string|max:150',
            'whatsapp' => 'nullable|string|max:20',
            'notas'    => 'nullable|string',
        ];
    }
    protected $messages = ['nombre.required' => 'El nombre es obligatorio.'];

    public function ordenar(string $col): void
    {
        if ($this->ordenarPor === $col) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenarPor = $col;
            $this->direccion  = 'asc';
        }
    }

    public function nuevo(): void
    {
        $this->reset(['proveedorId','nombre','contacto','whatsapp','notas']);
        $this->activo      = true;
        $this->modoEdicion = false;
        $this->mostrarForm = true;
    }

    public function editar(int $id): void
    {
        $p = Proveedor::findOrFail($id);
        $this->proveedorId = $id;
        $this->nombre      = $p->nombre;
        $this->contacto    = $p->contacto  ?? '';
        $this->whatsapp    = $p->whatsapp  ?? '';
        $this->notas       = $p->notas     ?? '';
        $this->activo      = $p->activo;
        $this->modoEdicion = true;
        $this->mostrarForm = true;
    }

    public function guardar(): void
    {
        $this->validate();
        $datos = [
            'comercio_id' => 1,
            'nombre'      => trim($this->nombre),
            'contacto'    => trim($this->contacto) ?: null,
            'whatsapp'    => trim($this->whatsapp)  ?: null,
            'notas'       => trim($this->notas)     ?: null,
            'activo'      => $this->activo,
        ];
        if ($this->modoEdicion) {
            Proveedor::findOrFail($this->proveedorId)->update($datos);
            session()->flash('success', 'Proveedor actualizado.');
        } else {
            Proveedor::create($datos);
            session()->flash('success', 'Proveedor creado.');
        }
        $this->mostrarForm = false;
        $this->reset(['proveedorId','nombre','contacto','whatsapp','notas']);
    }

    public function cancelar(): void
    {
        $this->mostrarForm = false;
        $this->reset(['proveedorId','nombre','contacto','whatsapp','notas']);
    }

    public function toggleActivo(int $id): void
    {
        $p = Proveedor::findOrFail($id);
        $p->update(['activo' => !$p->activo]);
    }

    public function render()
    {
        $proveedores = Proveedor::where('comercio_id', 1)
            ->when($this->busqueda, fn($q) => $q->where('nombre', 'like', "%{$this->busqueda}%"))
            ->withCount('compras')
            ->orderBy($this->ordenarPor, $this->direccion)
            ->paginate(20);

        return view('livewire.proveedores.lista-proveedores', compact('proveedores'))
            ->layout('layouts.app', ['title' => 'Proveedores']);
    }
}
