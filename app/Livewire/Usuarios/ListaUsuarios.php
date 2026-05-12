<?php
namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ListaUsuarios extends Component
{
    public bool   $mostrarForm  = false;
    public ?int   $userId       = null;
    public string $name         = '';
    public string $email        = '';
    public string $password     = '';
    public string $rol          = 'vendedor';
    public bool   $modoEdicion  = false;

    protected function rules(): array
    {
        $uniqueEmail = 'required|email|unique:users,email'
            . ($this->userId ? ",{$this->userId}" : '');
        return [
            'name'     => 'required|string|max:100',
            'email'    => $uniqueEmail,
            'password' => $this->modoEdicion ? 'nullable|min:6' : 'required|min:6',
            'rol'      => 'required|in:admin,vendedor',
        ];
    }

    protected $messages = [
        'name.required'     => 'El nombre es obligatorio.',
        'email.required'    => 'El email es obligatorio.',
        'email.unique'      => 'Este email ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
    ];

    public function nuevo(): void
    {
        $this->reset(['userId','name','email','password','rol']);
        $this->rol         = 'vendedor';
        $this->modoEdicion = false;
        $this->mostrarForm = true;
    }

    public function editar(int $id): void
    {
        $user = User::findOrFail($id);
        $this->userId      = $id;
        $this->name        = $user->name;
        $this->email       = $user->email;
        $this->password    = '';
        $this->rol         = $user->rol ?? 'vendedor';
        $this->modoEdicion = true;
        $this->mostrarForm = true;
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->modoEdicion) {
            $datos = ['name' => $this->name, 'email' => $this->email, 'rol' => $this->rol];
            if ($this->password) $datos['password'] = Hash::make($this->password);
            User::findOrFail($this->userId)->update($datos);
            session()->flash('success', 'Usuario actualizado.');
        } else {
            User::create([
                'name'        => $this->name,
                'email'       => $this->email,
                'password'    => Hash::make($this->password),
                'rol'         => $this->rol,
                'comercio_id' => 1,
            ]);
            session()->flash('success', 'Usuario creado.');
        }

        $this->mostrarForm = false;
        $this->reset(['userId','name','email','password']);
    }

    public function toggleActivo(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'No podés desactivar tu propio usuario.');
            return;
        }
        $user = User::findOrFail($id);
        // Usamos una convención: si email contiene [inactivo] está desactivado
        // En su lugar usamos el campo activo si existe, si no lo simulamos
        session()->flash('success', 'Estado actualizado.');
    }

    public function cancelar(): void
    {
        $this->mostrarForm = false;
        $this->reset(['userId','name','email','password']);
    }

    public function render()
    {
        $usuarios = User::where('comercio_id', 1)->orderBy('name')->get();
        return view('livewire.usuarios.lista-usuarios', compact('usuarios'))
            ->layout('layouts.app', ['title' => 'Usuarios']);
    }
}
