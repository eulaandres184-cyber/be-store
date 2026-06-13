@push('estilos')@vite(['resources/css/productos.css'])@endpush
@push('scripts')@vite(['resources/js/productos.js'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

<div class="bs-page-title">
    <span>📦 Productos</span>
    @if((auth()->user()->rol ?? 'vendedor') === 'admin')
    <a href="{{ route('productos.nuevo') }}" class="bs-btn-black">+ Nuevo producto</a>
    @endif
</div>

{{-- Filtros combinables --}}
<div style="background:var(--bs-white);border:1px solid var(--bs-gray-border);border-radius:var(--bs-radius-lg);padding:.85rem 1rem;margin-bottom:.75rem">
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:.6rem;align-items:end">
        <div>
            <label class="bs-label">Buscar</label>
            <input class="bs-input" wire:model.live.debounce.300ms="busqueda"
                   placeholder="Nombre, código interno o código de barras..."/>
        </div>
        <div>
            <label class="bs-label">Categoría</label>
            <select class="bs-select" wire:model.live="categoriaFiltro">
                <option value="">Todas</option>
                @foreach($this->categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="bs-label">Estado</label>
            <select class="bs-select" wire:model.live="estadoFiltro">
                <option value="activos">Activos</option>
                <option value="todos">Todos</option>
                <option value="bajo_stock">⚠ Bajo stock</option>
                <option value="sin_stock">🔴 Sin stock</option>
                <option value="inactivos">Inactivos</option>
            </select>
        </div>
        <div>
            <label class="bs-label" style="opacity:0">.</label>
            <button wire:click="limpiarFiltros" class="bs-btn-secondary" style="width:100%;white-space:nowrap">
                🗑 Limpiar
            </button>
        </div>
    </div>

    {{-- Indicadores de filtros activos --}}
    @if($busqueda || $categoriaFiltro || $estadoFiltro !== 'activos')
    <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.6rem;padding-top:.6rem;border-top:1px solid #EEF2F5">
        <span style="font-size:.7rem;color:var(--bs-muted);margin-right:.2rem">Filtros activos:</span>
        @if($busqueda)
        <span style="background:var(--bs-blue-light);color:var(--bs-blue);border:1px solid var(--bs-blue-mid);padding:1px 8px;border-radius:20px;font-size:.72rem;display:flex;align-items:center;gap:4px">
            🔍 "{{ $busqueda }}"
            <button wire:click="$set('busqueda','')" style="background:none;border:none;color:var(--bs-blue);cursor:pointer;font-size:.8rem;padding:0;line-height:1">✕</button>
        </span>
        @endif
        @if($categoriaFiltro)
        <span style="background:#FEF3C7;color:#9A6B00;border:1px solid #F5CBA7;padding:1px 8px;border-radius:20px;font-size:.72rem;display:flex;align-items:center;gap:4px">
            📂 {{ $this->categorias->firstWhere('id', $categoriaFiltro)?->nombre ?? 'Categoría' }}
            <button wire:click="$set('categoriaFiltro','')" style="background:none;border:none;color:#9A6B00;cursor:pointer;font-size:.8rem;padding:0;line-height:1">✕</button>
        </span>
        @endif
        @if($estadoFiltro !== 'activos')
        <span style="background:#F3E5F5;color:#7D3C98;border:1px solid #D7BDE2;padding:1px 8px;border-radius:20px;font-size:.72rem;display:flex;align-items:center;gap:4px">
            {{ match($estadoFiltro) {
                'todos'      => '📋 Todos',
                'bajo_stock' => '⚠ Bajo stock',
                'sin_stock'  => '🔴 Sin stock',
                'inactivos'  => '❌ Inactivos',
                default      => $estadoFiltro
            } }}
            <button wire:click="$set('estadoFiltro','activos')" style="background:none;border:none;color:#7D3C98;cursor:pointer;font-size:.8rem;padding:0;line-height:1">✕</button>
        </span>
        @endif
        <span style="font-size:.7rem;color:var(--bs-muted);margin-left:auto">
            {{ $this->totalFiltrado }} resultado(s)
        </span>
    </div>
    @endif
</div>

{{-- Tabla --}}
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
                    <div style="font-size:.68rem;color:var(--bs-muted);margin-top:2px">
                        📊 {{ $producto->codigo_barras }}
                    </div>
                @endif
            </td>
            <td style="font-weight:500">{{ $producto->nombre }}</td>
            <td>
                <span class="bs-badge-blue" style="background:#FEF3C7;color:#9A6B00;border:none">
                    {{ $producto->categoria->nombre ?? '—' }}
                </span>
            </td>
            <td style="font-weight:600;color:var(--bs-blue)">
                ${{ number_format($producto->precio_efectivo,0,',','.') }}
                <small style="color:var(--bs-muted)">{{ $producto->moneda }}</small>
            </td>
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
                <span class="bs-badge-{{ $producto->activo ? 'green' : 'red' }}">
                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </td>
            <td>
                <div class="tbl-actions">
                    @if((auth()->user()->rol ?? 'vendedor') === 'admin')
                    <a href="{{ route('productos.editar', $producto->id) }}" class="btn-edit">Editar</a>
                    @if($producto->activo)
                        <button wire:click="eliminar({{ $producto->id }})"
                                wire:confirm="¿Desactivar '{{ $producto->nombre }}'?"
                                class="btn-del">Desactivar</button>
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
                @if($busqueda || $categoriaFiltro)
                    No se encontraron productos con los filtros seleccionados.
                    <button wire:click="limpiarFiltros" style="background:none;border:none;color:var(--bs-blue);cursor:pointer;text-decoration:underline;font-size:.82rem">Limpiar filtros</button>
                @else
                    No hay productos cargados aún.
                @endif
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:.5rem;font-size:.75rem;color:var(--bs-muted)">
    <span>Total: {{ count($productos) }} producto(s)</span>
    @if($busqueda || $categoriaFiltro)
    <span>Filtrando por: {{ $busqueda ? '"'.$busqueda.'"' : '' }} {{ $categoriaFiltro ? 'en categoría seleccionada' : '' }}</span>
    @endif
</div>
</div>
