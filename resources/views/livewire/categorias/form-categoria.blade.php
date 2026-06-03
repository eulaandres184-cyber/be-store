@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>{{ $modoEdicion ? '✏️ Editar categoría' : '➕ Nueva categoría' }}</span>
    <a href="{{ route('categorias') }}" class="bs-btn-secondary">← Volver</a>
</div>
<div class="form-producto">
    <div class="form-section">
        <div class="form-section-title">📂 Datos de la categoría</div>
        <div class="bs-form-group">
            <label class="bs-label">Nombre *</label>
            <input class="bs-input" wire:model="nombre" placeholder="Ej: Fundas, Cables, iPhones..."/>
            @error('nombre')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Tipo *</label>
                <select class="bs-select" wire:model="tipo">
                    <option value="accesorio">Accesorio (precio en ARS)</option>
                    <option value="equipo">Equipo (precio en USD)</option>
                </select>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Orden de visualización</label>
                <input class="bs-input" wire:model="orden" type="number" min="0"/>
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Número más bajo = aparece primero</div>
            </div>
        </div>
        <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem">
            <input type="checkbox" wire:model="activo" id="activo" style="width:16px;height:16px;accent-color:var(--bs-blue)">
            <label for="activo" class="bs-label" style="margin:0;cursor:pointer">Categoría activa</label>
        </div>
    </div>
    <div style="display:flex;gap:.75rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black" style="padding:.65rem 1.5rem">
            <span wire:loading.remove>{{ $modoEdicion ? '💾 Guardar' : '✅ Crear categoría' }}</span>
            <span wire:loading>Guardando...</span>
        </button>
        <a href="{{ route('categorias') }}" class="bs-btn-secondary">Cancelar</a>
    </div>
</div>
</div>
