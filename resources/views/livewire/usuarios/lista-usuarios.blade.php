<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Usuarios</h1>
        <p>Gestiona el acceso de usuarios a la plataforma.</p>
    </header>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:1rem">
        <input type="text" class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre o email..." />
        
        <select class="bs-select" wire:model.live="rolFiltro">
            <option value="">Todos los roles</option>
            <option value="admin">Admin</option>
            <option value="vendedor">Vendedor</option>
            <option value="almacen">Almacén</option>
        </select>
    </div>

    @if($this->usuarios->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">👤</div>
            <p>No se encontraron usuarios</p>
        </div>
    @else
        <table class="bs-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Registrado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($this->usuarios as $usuario)
            <tr>
                <td><strong>{{ $usuario->name }}</strong></td>
                <td>{{ $usuario->email }}</td>
                <td>
                    @php
                        $rolColor = match($usuario->rol) {
                            'admin' => 'red',
                            'vendedor' => 'blue',
                            'almacen' => 'amber',
                            default => 'gray'
                        };
                    @endphp
                    <span class="bs-badge-{{ $rolColor }}">
                        {{ ucfirst($usuario->rol) }}
                    </span>
                </td>
                <td>{{ $usuario->created_at->format('d/m/Y') }}</td>
                <td>
                    <button class="bs-btn-small" onclick="alert('Editar usuario #{{ $usuario->id }}')">Editar</button>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div style="margin-top:1rem">
            {{ $this->usuarios->links() }}
        </div>
    @endif
</div>
