@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div class="bs-alert-danger">❌ {{ session('error') }}</div>@endif

<div class="bs-page-title">
    <span>📂 Categorías</span>
    <a href="{{ route('categorias.nueva') }}" class="bs-btn-black">+ Nueva categoría</a>
</div>

<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar categoría..."/>
    <select class="bs-select" wire:model.live="tipoFiltro" style="width:auto">
        <option value="">Todos los tipos</option>
        <option value="accesorio">Accesorios</option>
        <option value="equipo">Equipos</option>
    </select>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('orden')">Orden @if($ordenarPor==='orden'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th class="th-sort" wire:click="ordenar('nombre')">Nombre @if($ordenarPor==='nombre'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Tipo</th>
                <th>Productos</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($categorias as $cat)
        <tr wire:key="cat-{{ $cat->id }}">
            <td style="font-weight:600;color:var(--bs-muted)">{{ $cat->orden }}</td>
            <td style="font-weight:600">{{ $cat->nombre }}</td>
            <td><span class="bs-badge-{{ $cat->tipo==='accesorio'?'blue':'green' }}">{{ ucfirst($cat->tipo) }}</span></td>
            <td>{{ $cat->productos_count }}</td>
            <td><span class="bs-badge-{{ $cat->activo?'green':'red' }}">{{ $cat->activo?'Activa':'Inactiva' }}</span></td>
            <td>
                <div class="tbl-actions">
                    <a href="{{ route('categorias.editar', $cat->id) }}" class="btn-edit">Editar</a>
                    <button wire:click="toggleActivo({{ $cat->id }})" class="btn-edit">
                        {{ $cat->activo ? 'Desactivar' : 'Activar' }}
                    </button>
                    @if($cat->productos_count === 0)
                    <button wire:click="eliminar({{ $cat->id }})" wire:confirm="¿Eliminar esta categoría?" class="btn-del">Eliminar</button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--bs-muted)">No se encontraron categorías</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</div>
