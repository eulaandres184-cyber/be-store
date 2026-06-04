<div>
    @push('estilos')
        @vite(['resources/css/productos.css'])
    @endpush
    @push('scripts')
        @vite(['resources/js/productos.js'])
    @endpush

    @if(session('success'))
        <div class="bs-alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="bs-alert-warning">⚠️ {{ session('warning') }}</div>
    @endif

    @php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp
    <div class="bs-page-title">
    <span>📦 Productos</span>
    @if($esAdmin)
        <a href="{{ route('productos.nuevo') }}" class="bs-btn-black">+ Nuevo producto</a>
    @endif
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
                <th style="cursor:pointer;user-select:none" wire:click="cambiarOrden('codigo_interno')">
                    Código
                    @if($ordenar === 'codigo_interno')
                        <span style="margin-left:5px">{{ $ordenDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                <th style="cursor:pointer;user-select:none" wire:click="cambiarOrden('nombre')">
                    Nombre
                    @if($ordenar === 'nombre')
                        <span style="margin-left:5px">{{ $ordenDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                <th>Categoría</th>
                <th style="cursor:pointer;user-select:none" wire:click="cambiarOrden('precio_efectivo')">
                    Precio efectivo
                    @if($ordenar === 'precio_efectivo')
                        <span style="margin-left:5px">{{ $ordenDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
                </th>
                <th style="cursor:pointer;user-select:none" wire:click="cambiarOrden('stock_actual')">
                    Stock
                    @if($ordenar === 'stock_actual')
                        <span style="margin-left:5px">{{ $ordenDir === 'asc' ? '↑' : '↓' }}</span>
                    @endif
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
</div>
