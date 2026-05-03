<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Compras</h1>
        <p>Gestiona tus compras a proveedores.</p>
        <a href="{{ route('compras.nueva') }}" class="bs-btn-primary" style="margin-top:.5rem">+ Nueva compra</a>
    </header>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
        <div class="bs-metric">
            <div class="bs-metric-label">Total general</div>
            <div class="bs-metric-value">${{ number_format($totalCompras, 0, ',', '.') }}</div>
        </div>
        <div class="bs-metric">
            <div class="bs-metric-label">Compras hoy</div>
            <div class="bs-metric-value">${{ number_format($comprasHoy, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:.75rem; margin-bottom:1rem">
        <input type="date" class="bs-input" wire:model.live="desde" />
        <input type="date" class="bs-input" wire:model.live="hasta" />
        
        <select class="bs-select" wire:model.live="proveedorFiltro">
            <option value="">Todos los proveedores</option>
            @foreach($this->proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
            @endforeach
        </select>

        <select class="bs-select" wire:model.live="ordenar">
            <option value="fecha_desc">Más recientes</option>
            <option value="fecha_asc">Más antiguos</option>
            <option value="monto_desc">Mayor monto</option>
        </select>

        <button wire:click="limpiarFiltros" class="bs-btn-outline">Limpiar</button>
    </div>

    @if($this->compras->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">📋</div>
            <p>No se encontraron compras</p>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="bs-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Ítems</th>
                        <th>Subtotal</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($this->compras as $compra)
                <tr>
                    <td><strong>#{{ $compra->id }}</strong></td>
                    <td>
                        <div>{{ $compra->fecha->format('d/m/Y') }}</div>
                        <div style="font-size:.8rem;color:#94A3B8">{{ $compra->fecha->format('H:i') }}</div>
                    </td>
                    <td>{{ $compra->proveedor->nombre ?? '—' }}</td>
                    <td>{{ $compra->items->count() }} ítem(s)</td>
                    <td>${{ number_format($compra->total_ars, 0, ',', '.') }}</td>
                    <td><strong style="color:#1E6FBB">${{ number_format($compra->total_ars, 0, ',', '.') }}</strong></td>
                    <td>
                        <button class="bs-btn-small" onclick="alert('Ver detalle compra #{{ $compra->id }}')">Ver</button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem">
            {{ $this->compras->links() }}
        </div>
    @endif
</div>
