<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Productos</h1>
        <p>Gestiona el inventario y precios de tu catálogo.</p>
        <a href="{{ route('productos.nuevo') }}" class="bs-btn-primary" style="margin-top:.5rem">+ Nuevo producto</a>
    </header>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:.75rem; margin-bottom:1rem">
        <input type="text" class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre..." />
        
        <select class="bs-select" wire:model.live="categoriaFiltro">
            <option value="">Todas las categorías</option>
            @foreach($this->categorias as $cat)
                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
            @endforeach
        </select>

        <select class="bs-select" wire:model.live="estado">
            <option value="">Todos</option>
            <option value="activos">Activos</option>
            <option value="inactivos">Inactivos</option>
        </select>

        <select class="bs-select" wire:model.live="ordenar">
            <option value="nombre">Por nombre</option>
            <option value="precio_efectivo">Por precio</option>
            <option value="stock_actual">Por stock</option>
            <option value="created_at">Más recientes</option>
        </select>
    </div>

    @if($this->productos->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">📦</div>
            <p>No se encontraron productos</p>
        </div>
    @else
        <table class="bs-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($this->productos as $producto)
            <tr>
                <td>
                    <strong>{{ $producto->nombre }}</strong>
                    @if($producto->descripcion)
                        <div style="font-size:.8rem;color:#94A3B8">{{ substr($producto->descripcion, 0, 50) }}...</div>
                    @endif
                </td>
                <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                <td>
                    <span class="bs-badge-{{ $producto->stock_actual <= $producto->stock_minimo ? 'red' : 'green' }}">
                        {{ $producto->stock_actual }} / {{ $producto->stock_minimo }}
                    </span>
                </td>
                <td>
                    <div style="font-weight:600">Efec: ${{ number_format($producto->precio_efectivo, 0, ',', '.') }}</div>
                    @if($producto->precio_tarjeta)
                        <div style="font-size:.8rem;color:#94A3B8">Tarj: ${{ number_format($producto->precio_tarjeta, 0, ',', '.') }}</div>
                    @endif
                </td>
                <td>
                    <span class="bs-badge-{{ $producto->activo ? 'green' : 'gray' }}">
                        {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:.5rem">
                        <a href="{{ route('productos.editar', $producto->id) }}" class="bs-btn-small">Editar</a>
                        <button wire:click="toggleActivo({{ $producto->id }})" class="bs-btn-small bs-btn-outline">
                            {{ $producto->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div style="margin-top:1rem">
            {{ $this->productos->links() }}
        </div>
    @endif
</div>
