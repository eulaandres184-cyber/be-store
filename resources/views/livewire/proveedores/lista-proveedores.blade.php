@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

<div class="bs-page-title">
    <span>🚚 Proveedores</span>
    @if(!$mostrarForm)
    <button wire:click="nuevo" class="bs-btn-black">+ Nuevo proveedor</button>
    @endif
</div>

@if($mostrarForm)
<div class="form-section" style="max-width:600px;margin-bottom:1rem">
    <div class="form-section-title">{{ $modoEdicion?'✏️ Editar proveedor':'➕ Nuevo proveedor' }}</div>
    <div class="bs-form-group">
        <label class="bs-label">Nombre *</label>
        <input class="bs-input" wire:model="nombre" placeholder="Nombre del proveedor o empresa"/>
        @error('nombre')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
    </div>
    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Contacto</label>
            <input class="bs-input" wire:model="contacto" placeholder="Nombre del contacto"/>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">WhatsApp</label>
            <input class="bs-input" wire:model="whatsapp" placeholder="Ej: 351 555 0101"/>
        </div>
    </div>
    <div class="bs-form-group">
        <label class="bs-label">Notas</label>
        <textarea class="bs-input" wire:model="notas" rows="2" placeholder="Condiciones, días de entrega, etc."></textarea>
    </div>
    <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem">
        <input type="checkbox" wire:model="activo" id="activo" style="width:16px;height:16px;accent-color:var(--bs-blue)">
        <label for="activo" class="bs-label" style="margin:0;cursor:pointer">Proveedor activo</label>
    </div>
    <div style="display:flex;gap:.5rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black">
            <span wire:loading.remove>{{ $modoEdicion?'💾 Guardar':'✅ Crear proveedor' }}</span>
            <span wire:loading>Guardando...</span>
        </button>
        <button wire:click="cancelar" class="bs-btn-secondary">Cancelar</button>
    </div>
</div>
@endif

<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar proveedor..."/>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('nombre')">Nombre @if($ordenarPor==='nombre'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Contacto</th>
                <th>WhatsApp</th>
                <th>Compras</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($proveedores as $p)
        <tr wire:key="prov-{{ $p->id }}">
            <td style="font-weight:600">{{ $p->nombre }}</td>
            <td>{{ $p->contacto ?? '—' }}</td>
            <td>
                @if($p->whatsapp)
                <a href="https://wa.me/54{{ preg_replace('/\D/','',$p->whatsapp) }}" target="_blank" style="color:var(--bs-success-text)">
                    📱 {{ $p->whatsapp }}
                </a>
                @else —
                @endif
            </td>
            <td><span class="bs-badge-blue">{{ $p->compras_count }}</span></td>
            <td><span class="bs-badge-{{ $p->activo?'green':'red' }}">{{ $p->activo?'Activo':'Inactivo' }}</span></td>
            <td>
                <div class="tbl-actions">
                    <button wire:click="editar({{ $p->id }})" class="btn-edit">Editar</button>
                    <button wire:click="toggleActivo({{ $p->id }})" class="btn-edit">
                        {{ $p->activo?'Desactivar':'Activar' }}
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay proveedores</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $proveedores->links() }}</div>
</div>
