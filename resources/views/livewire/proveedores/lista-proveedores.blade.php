<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Proveedores</h1>
        <p>Gestiona tus contactos de proveedores para compras.</p>
    </header>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:1rem">
        <input type="text" class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre o contacto..." />
        
        <select class="bs-select" wire:model.live="estado">
            <option value="">Todos</option>
            <option value="activos">Activos</option>
            <option value="inactivos">Inactivos</option>
        </select>
    </div>

    @if($this->proveedores->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">🏭</div>
            <p>No se encontraron proveedores</p>
        </div>
    @else
        <table class="bs-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>WhatsApp</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($this->proveedores as $proveedor)
            <tr>
                <td>
                    <strong>{{ $proveedor->nombre }}</strong>
                    @if($proveedor->notas)
                        <div style="font-size:.8rem;color:#94A3B8">{{ substr($proveedor->notas, 0, 40) }}...</div>
                    @endif
                </td>
                <td>{{ $proveedor->contacto ?? '—' }}</td>
                <td>
                    @if($proveedor->whatsapp)
                        <a href="https://wa.me/{{ str_replace(' ', '', $proveedor->whatsapp) }}" target="_blank" class="bs-btn-small" style="font-size:.8rem">{{ $proveedor->whatsapp }}</a>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="bs-badge-{{ $proveedor->activo ? 'green' : 'gray' }}">
                        {{ $proveedor->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:.5rem">
                        <button class="bs-btn-small" onclick="alert('Editar proveedor #{{ $proveedor->id }}')">Editar</button>
                        <button wire:click="toggleActivo({{ $proveedor->id }})" class="bs-btn-small bs-btn-outline">
                            {{ $proveedor->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div style="margin-top:1rem">
            {{ $this->proveedores->links() }}
        </div>
    @endif
</div>
