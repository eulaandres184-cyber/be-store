<?php
namespace App\Livewire\Clientes;

use Livewire\Component;
use App\Models\Cliente;

class FormCliente extends Component
{
    public ?int   $clienteId  = null;
    public string $nombre     = '';
    public string $telefono   = '';
    public string $email      = '';
    public string $dni        = '';
    public string $notas      = '';
    public bool   $activo     = true;
    public bool   $modoEdicion = false;

    protected function rules(): array
    {
        $uniqueDni = 'nullable|string|max:20|unique:clientes,dni'
            . ($this->clienteId ? ",{$this->clienteId}" : '');
        return [
            'nombre'   => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:150',
            'dni'      => $uniqueDni,
            'notas'    => 'nullable|string',
        ];
    }
    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'email.email'     => 'El email no es válido.',
        'dni.unique'      => 'Este DNI ya está registrado.',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->modoEdicion = true;
            $this->clienteId   = $id;
            $c = Cliente::findOrFail($id);
            $this->nombre   = $c->nombre;
            $this->telefono = $c->telefono ?? '';
            $this->email    = $c->email    ?? '';
            $this->dni      = $c->dni      ?? '';
            $this->notas    = $c->notas    ?? '';
            $this->activo   = $c->activo;
        }
    }

    public function guardar(): void
    {
        $this->validate();
        $datos = [
            'comercio_id' => 1,
            'nombre'      => trim($this->nombre),
            'telefono'    => trim($this->telefono) ?: null,
            'email'       => trim($this->email)    ?: null,
            'dni'         => trim($this->dni)      ?: null,
            'notas'       => trim($this->notas)    ?: null,
            'activo'      => $this->activo,
        ];
        if ($this->modoEdicion) {
            Cliente::findOrFail($this->clienteId)->update($datos);
            session()->flash('success', 'Cliente actualizado.');
        } else {
            Cliente::create($datos);
            session()->flash('success', 'Cliente registrado.');
        }
        $this->redirect(route('clientes'));
    }

    public function render()
    {
        return view('livewire.clientes.form-cliente')
            ->layout('layouts.app', ['title' => $this->modoEdicion ? 'Editar cliente' : 'Nuevo cliente']);
    }
}
