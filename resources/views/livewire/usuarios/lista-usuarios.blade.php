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
<div class="form-section" style="max-width:520px;margin-bottom:1rem">
    <div class="form-section-title">{{ $modoEdicion ? '✏️ Editar usuario' : '➕ Nuevo usuario' }}</div>

    <div class="bs-form-group">
        <label class="bs-label">Nombre completo *</label>
        <input class="bs-input" wire:model="name" placeholder="Nombre del empleado"/>
        @error('name')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
    </div>

    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Email *</label>
            <input class="bs-input" wire:model="email" type="email" placeholder="usuario@bestore.com"/>
            @error('email')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Rol *</label>
            <select class="bs-select" wire:model="rol">
                <option value="vendedor">Vendedor</option>
                <option value="admin">Administrador</option>
            </select>
        </div>
    </div>

    {{-- Contraseña --}}
    @if($modoEdicion)
    <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
        <input type="checkbox" wire:model.live="cambiarPass" id="cambiarPass"
               style="width:16px;height:16px;accent-color:var(--bs-blue)">
        <label for="cambiarPass" class="bs-label" style="margin:0;cursor:pointer">
            Cambiar contraseña
        </label>
    </div>
    @php
        // Nota: Las contraseñas se almacenan encriptadas (bcrypt).
        // No es posible mostrar la contraseña actual por seguridad.
        // Solo se puede establecer una nueva contraseña.
    @endphp
    @if($cambiarPass)
    <div style="background:var(--bs-warning-bg);border:1px solid #F5CBA7;border-radius:8px;padding:.6rem .85rem;font-size:.78rem;color:var(--bs-warning-text);margin-bottom:.75rem">
        ⚠ Las contraseñas se almacenan de forma encriptada por seguridad. No es posible ver la contraseña actual — podés establecer una nueva.
    </div>
    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Nueva contraseña</label>
            <input class="bs-input" wire:model="nuevaPassword" type="password" placeholder="Mínimo 6 caracteres"/>
            @error('nuevaPassword')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Confirmar contraseña</label>
            <input class="bs-input" wire:model="confirmarPassword" type="password" placeholder="Repetir contraseña"/>
        </div>
    </div>
    @endif
    @else
    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Contraseña *</label>
            <input class="bs-input" wire:model="nuevaPassword" type="password" placeholder="Mínimo 6 caracteres"/>
            @error('nuevaPassword')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Confirmar contraseña</label>
            <input class="bs-input" wire:model="confirmarPassword" type="password" placeholder="Repetir contraseña"/>
        </div>
    </div>
    @endif

    <div style="display:flex;gap:.5rem;margin-top:.5rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black">
            <span wire:loading.remove>{{ $modoEdicion ? '💾 Guardar cambios' : '✅ Crear usuario' }}</span>
            <span wire:loading>Guardando...</span>
        </button>
        <button wire:click="cancelar" class="bs-btn-secondary">Cancelar</button>
    </div>
</div>
@endif

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
        </thead>
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
                    <button wire:click="editar({{ $u->id }})" class="btn-edit">✏️ Editar</button>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
</div>
