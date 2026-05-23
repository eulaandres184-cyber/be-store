@push('estilos')@vite(['resources/css/productos.css'])@endpush
@push('scripts')@vite(['resources/js/productos.js'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif
@if(session('warning'))<div class="bs-alert-warning">⚠️ {{ session('warning') }}</div>@endif

@php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp
<div class="bs-page-title">
    <span>📦 Productos</span>
    @if($esAdmin)
        <a href="{{ route('productos.nuevo') }}" class="bs-btn-black">+ Nuevo producto</a>
    @endif
</div>

<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre, código o barras..."/>
    <select class="bs-select" wire:model.live="categoriaFiltro">
        <option value="">Todas las categorías</option>
        @foreach($this->categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>
</div>

<div class="estado-tabs">
    <div class="estado-tab {{ $estadoFiltro==='todos'      ?'activo':'' }}" wire:click="$set('estadoFiltro','todos')">Todos</div>
    <div class="estado-tab {{ $estadoFiltro==='activos'    ?'activo':'' }}" wire:click="$set('estadoFiltro','activos')">Activos</div>
    <div class="estado-tab {{ $estadoFiltro==='bajo_stock' ?'activo':'' }}" wire:click="$set('estadoFiltro','bajo_stock')">⚠️ Bajo stock</div>
    <div class="estado-tab {{ $estadoFiltro==='inactivos'  ?'activo':'' }}" wire:click="$set('estadoFiltro','inactivos')">Inactivos</div>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('codigo_interno')">
                    Código @if($ordenarPor==='codigo_interno'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif
                </th>
                <th class="th-sort" wire:click="ordenar('nombre')">
                    Nombre @if($ordenarPor==='nombre'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif
                </th>
                <th class="th-sort" wire:click="ordenar('categoria_id')">
                    Categoría @if($ordenarPor==='categoria_id'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif
                </th>
                <th class="th-sort" wire:click="ordenar('precio_efectivo')">
                    Precio @if($ordenarPor==='precio_efectivo'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif
                </th>
                <th class="th-sort" wire:click="ordenar('stock_actual')">
                    Stock @if($ordenarPor==='stock_actual'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif
                </th>
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
            <td style="font-weight:600;color:var(--bs-blue)">${{ number_format($producto->precio_efectivo,0,',','.') }} <small>{{ $producto->moneda }}</small></td>
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
                <span class="bs-badge-{{ $producto->activo ? 'green' : 'red' }}">{{ $producto->activo ? 'Activo' : 'Inactivo' }}</span>
            </td>
            <td>
                <div class="tbl-actions">
                    @if($esAdmin)
                        <a href="{{ route('productos.editar', $producto->id) }}" class="btn-edit">Editar</a>
                        @if($producto->activo)
                            <button wire:click="eliminar({{ $producto->id }})" wire:confirm="¿Desactivar este producto?" class="btn-del">Desactivar</button>
                        @else
                            <button wire:click="activar({{ $producto->id }})" class="btn-edit">Activar</button>
                        @endif
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--bs-muted)">No se encontraron productos</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $productos->links() }}</div>
</div>
