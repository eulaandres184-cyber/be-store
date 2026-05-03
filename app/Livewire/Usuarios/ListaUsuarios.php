<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ListaUsuarios extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $rolFiltro = '';

    protected $queryString = ['busqueda', 'rolFiltro'];
    protected $paginationTheme = 'bootstrap';

    #[Computed]
    public function usuarios()
    {
        return User::where('comercio_id', $this->comercioId())
            ->when(
                $this->busqueda,
                fn($q) =>
                $q->where('name', 'like', "%{$this->busqueda}%")
                    ->orWhere('email', 'like', "%{$this->busqueda}%")
            )
            ->when(
                $this->rolFiltro,
                fn($q) =>
                $q->where('rol', $this->rolFiltro)
            )
            ->orderBy('name')
            ->paginate(15);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        return view('livewire.usuarios.lista-usuarios')
            ->layout('layouts.app', ['title' => 'Usuarios']);
    }
}
