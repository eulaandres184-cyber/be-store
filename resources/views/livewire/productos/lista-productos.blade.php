<x-layouts.app title="Productos">
<x-slot name="estilos">@vite(['resources/css/productos.css'])</x-slot>
<x-slot name="scripts">@vite(['resources/js/productos.js'])</x-slot>

@if(session('success'))
    <div class="bs-alert-success">✅ {{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="bs-alert-warning">⚠️ {{ session('warning') }}</div>
@endif

<div class="bs-page-title">
    <span>📦 Productos</span>
    <a href="{{ route('productos.nuevo') }}" class="bs-btn-black">+ Nuevo producto</a>
</div>

{{-- Filtros --}}
<div class="productos-filtros">
    <input
        class="bs-input"
        wire:model.live.debounce.300ms="busqueda"
        placeholder="Buscar por nombre, código interno o código de barras..."
    />
    <select class="bs-select" wire:model.live="categoriaFiltro">
        <option value="">Todas las categorías</option>
        @foreach($this->categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>
    <select class="bs-select" wire:model.live="ordenar" style="width:auto;min-width:130px">
        <option value="nombre">Nombre A-Z</option>
        <option value="precio_efectivo">Precio</option>
        <option value="stock_actual">Stock</option>
        <option value="created_at">Más nuevos</option>
    </select>
</div>

{{-- Tabs de estado --}}
<div class="estado-tabs">
    <div class="estado-tab {{ $estadoFiltro === 'todos' ? 'activo' : '' }}" wire:click="$set('estadoFiltro','todos')">Todos</div>
    <div class="estado-tab {{ $estadoFiltro === 'activos' ? 'activo' : '' }}" wire:click="$set('estadoFiltro','activos')">Activos</div>
    <div class="estado-tab {{ $estadoFiltro === 'bajo_stock' ? 'activo' : '' }}" wire:click="$set('estadoFiltro','bajo_stock')">⚠️ Bajo stock</div>
    <div class="estado-tab {{ $estadoFiltro === 'inactivos' ? 'activo' : '' }}" wire:click="$set('estadoFiltro','inactivos')">Inactivos</div>
</div>

{{-- Tabla --}}
<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio efectivo</th>
                <th>Stock</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($productos as $producto)
        <tr wire:key="prod-{{ $producto->id }}">
            <td>
                @if($producto->codigo_interno)
                    <span class="bs-badge-blue">{{ $producto->codigo_interno }}</span>
                @endif
                @if($producto->codigo_barras)
                    <div style="font-size:.68rem;color:var(--bs-muted);margin-top:2px">📊 {{ $producto->codigo_barras }}</div>
                @endif
            </td>
            <td style="font-weight:500">{{ $producto->nombre }}</td>
            <td>{{ $producto->categoria->nombre ?? '—' }}</td>
            <td style="font-weight:600;color:var(--bs-blue)">${{ number_format($producto->precio_efectivo, 0, ',', '.') }} {{ $producto->moneda }}</td>
            <td>
                @if($producto->stock_actual == 0)
                    <span class="stock-zero">Sin stock</span>
                @elseif($producto->stock_actual <= $producto->stock_minimo)
                    <span class="stock-low">⚠️ {{ $producto->stock_actual }} u.</span>
                @else
                    <span class="stock-ok">{{ $producto->stock_actual }} u.</span>
                @endif
            </td>
            <td>
                @if($producto->activo)
                    <span class="bs-badge-green">Activo</span>
                @else
                    <span class="bs-badge-red">Inactivo</span>
                @endif
            </td>
            <td>
                <div class="tbl-actions">
                    <a href="{{ route('productos.editar', $producto->id) }}" class="btn-edit">Editar</a>
                    @if($producto->activo)
                        <button wire:click="eliminar({{ $producto->id }})" wire:confirm="¿Desactivar este producto?" class="btn-del">Desactivar</button>
                    @else
                        <button wire:click="activar({{ $producto->id }})" class="btn-edit">Activar</button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;padding:2rem;color:var(--bs-muted)">
                No se encontraron productos
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación --}}
<div style="margin-top:.75rem">
    {{ $productos->links() }}
</div>

</x-layouts.app>
