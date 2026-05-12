@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="bs-alert-danger">❌ {{ session('error') }}</div>@endif

<div class="bs-page-title">
    <span>👤 Usuarios del sistema</span>
    @if(!$mostrarForm)
    <button wire:click="nuevo" class="bs-btn-black">+ Nuevo usuario</button>
    @endif
</div>

@if($mostrarForm)
<div class="form-section" style="max-width:500px;margin-bottom:1rem">
    <div class="form-section-title">{{ $modoEdicion ? '✏️ Editar usuario' : '➕ Nuevo usuario' }}</div>
    <div class="bs-form-group">
        <label class="bs-label">Nombre *</label>
        <input class="bs-input" wire:model="name" placeholder="Nombre completo"/>
        @error('name')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
    </div>
    <div class="bs-form-group">
        <label class="bs-label">Email *</label>
        <input class="bs-input" wire:model="email" type="email" placeholder="usuario@bestore.com"/>
        @error('email')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
    </div>
    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Contraseña {{ $modoEdicion ? '(dejar vacío para no cambiar)' : '*' }}</label>
            <input class="bs-input" wire:model="password" type="password" placeholder="Mínimo 6 caracteres"/>
            @error('password')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Rol *</label>
            <select class="bs-select" wire:model="rol">
                <option value="admin">Administrador</option>
                <option value="vendedor">Vendedor</option>
            </select>
        </div>
    </div>
    <div style="display:flex;gap:.5rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black">
            <span wire:loading.remove>{{ $modoEdicion ? '💾 Guardar' : '✅ Crear usuario' }}</span>
            <span wire:loading>Guardando...</span>
        </button>
        <button wire:click="cancelar" class="bs-btn-secondary">Cancelar</button>
    </div>
</div>
@endif

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead>
        <tbody>
        @foreach($usuarios as $u)
        <tr wire:key="u-{{ $u->id }}">
            <td style="font-weight:500">
                {{ $u->name }}
                @if($u->id === auth()->id())
                    <span class="bs-badge-blue" style="margin-left:4px">Vos</span>
                @endif
            </td>
            <td style="font-size:.8rem">{{ $u->email }}</td>
            <td>
                <span class="bs-badge-{{ ($u->rol ?? 'vendedor')==='admin'?'green':'blue' }}">
                    {{ ($u->rol ?? 'vendedor') === 'admin' ? 'Administrador' : 'Vendedor' }}
                </span>
            </td>
            <td>
                <div class="tbl-actions">
                    <button wire:click="editar({{ $u->id }})" class="btn-edit">Editar</button>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
