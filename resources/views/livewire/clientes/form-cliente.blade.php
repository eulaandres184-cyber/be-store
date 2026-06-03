@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>{{ $modoEdicion ? '✏️ Editar cliente' : '👤 Nuevo cliente' }}</span>
    <a href="{{ route('clientes') }}" class="bs-btn-secondary">← Volver</a>
</div>
<div class="form-producto">
    <div class="form-section">
        <div class="form-section-title">👤 Datos del cliente</div>
        <div class="bs-form-group">
            <label class="bs-label">Nombre completo *</label>
            <input class="bs-input" wire:model="nombre" placeholder="Nombre y apellido"/>
            @error('nombre')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Teléfono / WhatsApp</label>
                <input class="bs-input" wire:model="telefono" placeholder="Ej: 358 555 0101"/>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">DNI</label>
                <input class="bs-input" wire:model="dni" placeholder="Ej: 35123456"/>
                @error('dni')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Email</label>
            <input class="bs-input" wire:model="email" type="email" placeholder="cliente@ejemplo.com"/>
            @error('email')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Notas</label>
            <textarea class="bs-input" wire:model="notas" rows="2" placeholder="Observaciones del cliente..."></textarea>
        </div>
        <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem">
            <input type="checkbox" wire:model="activo" id="activo" style="width:16px;height:16px;accent-color:var(--bs-blue)">
            <label for="activo" class="bs-label" style="margin:0;cursor:pointer">Cliente activo</label>
        </div>
    </div>
    <div style="display:flex;gap:.75rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black" style="padding:.65rem 1.5rem">
            <span wire:loading.remove>{{ $modoEdicion ? '💾 Guardar' : '✅ Registrar cliente' }}</span>
            <span wire:loading>Guardando...</span>
        </button>
        <a href="{{ route('clientes') }}" class="bs-btn-secondary">Cancelar</a>
    </div>
</div>
</div>
