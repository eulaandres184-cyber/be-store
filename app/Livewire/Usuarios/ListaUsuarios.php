<?php
namespace App\Livewire\Usuarios;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ListaUsuarios extends Component
{
    public bool   $mostrarForm   = false;
    public ?int   $userId        = null;
    public string $name          = '';
    public string $email         = '';
    public string $nuevaPassword = '';
    public string $confirmarPassword = '';
    public string $rol           = 'vendedor';
    public bool   $modoEdicion   = false;
    public bool   $cambiarPass   = false;

    protected function rules(): array
    {
        $uniqueEmail = 'required|email|unique:users,email' . ($this->userId ? ",{$this->userId}" : '');
        $passRules   = $this->modoEdicion
            ? ($this->cambiarPass ? 'required|min:6|same:confirmarPassword' : 'nullable')
            : 'required|min:6|same:confirmarPassword';

        return [
            'name'            => 'required|string|max:100',
            'email'           => $uniqueEmail,
            'nuevaPassword'   => $passRules,
            'confirmarPassword' => 'nullable',
            'rol'             => 'required|in:admin,vendedor',
        ];
    }

    protected $messages = [
        'name.required'          => 'El nombre es obligatorio.',
        'email.required'         => 'El email es obligatorio.',
        'email.unique'           => 'Este email ya está en uso.',
        'nuevaPassword.required' => 'La contraseña es obligatoria.',
        'nuevaPassword.min'      => 'Mínimo 6 caracteres.',
        'nuevaPassword.same'     => 'Las contraseñas no coinciden.',
    ];

    public function nuevo(): void
    {
        $this->reset(['userId','name','email','nuevaPassword','confirmarPassword','rol','cambiarPass']);
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
        $this->rol         = $user->rol ?? 'vendedor';
        $this->nuevaPassword = '';
        $this->confirmarPassword = '';
        $this->cambiarPass = false;
        $this->modoEdicion = true;
        $this->mostrarForm = true;
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->modoEdicion) {
            $datos = [
                'name'  => $this->name,
                'email' => $this->email,
                'rol'   => $this->rol,
            ];
            if ($this->cambiarPass && $this->nuevaPassword) {
                $datos['password'] = Hash::make($this->nuevaPassword);
            }
            User::findOrFail($this->userId)->update($datos);
            session()->flash('success', 'Usuario actualizado correctamente.');
        } else {
            User::create([
                'name'        => $this->name,
                'email'       => $this->email,
                'password'    => Hash::make($this->nuevaPassword),
                'rol'         => $this->rol,
                'comercio_id' => 1,
            ]);
            session()->flash('success', 'Usuario creado correctamente.');
        }

        $this->mostrarForm = false;
        $this->reset(['userId','name','email','nuevaPassword','confirmarPassword','cambiarPass']);
    }

    public function cancelar(): void
    {
        $this->mostrarForm = false;
        $this->reset(['userId','name','email','nuevaPassword','confirmarPassword']);
    }

    public function render()
    {
        $usuarios = User::where('comercio_id', 1)->orderBy('name')->get();
        return view('livewire.usuarios.lista-usuarios', compact('usuarios'))
            ->layout('layouts.app', ['title' => 'Usuarios']);
    }
}
