<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Historial de ventas</h1>
        <p>Consulta todas tus transacciones de venta.</p>
    </header>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem">
        <div class="bs-metric">
            <div class="bs-metric-label">Total general</div>
            <div class="bs-metric-value">${{ number_format($totalVentas, 0, ',', '.') }}</div>
        </div>
        <div class="bs-metric">
            <div class="bs-metric-label">Ventas hoy</div>
            <div class="bs-metric-value">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr auto; gap:.75rem; margin-bottom:1rem">
        <input type="date" class="bs-input" wire:model.live="desde" />
        <input type="date" class="bs-input" wire:model.live="hasta" />
        
        <select class="bs-select" wire:model.live="medioPago">
            <option value="">Todos los medios</option>
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
            <option value="cuotas_4">BLP 4 cuotas</option>
            <option value="cuotas_20">BLP 20 cuotas</option>
        </select>

        <select class="bs-select" wire:model.live="ordenar">
            <option value="fecha_desc">Más recientes</option>
            <option value="fecha_asc">Más antiguos</option>
            <option value="monto_desc">Mayor monto</option>
        </select>

        <button wire:click="limpiarFiltros" class="bs-btn-outline">Limpiar</button>
    </div>

    @if($this->ventas->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">📋</div>
            <p>No se encontraron ventas</p>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="bs-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Fecha</th>
                        <th>Ítems</th>
                        <th>Medio pago</th>
                        <th>Subtotal</th>
                        <th>Recargo</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($this->ventas as $venta)
                <tr>
                    <td><strong>#{{ $venta->id }}</strong></td>
                    <td>
                        <div>{{ $venta->fecha->format('d/m/Y') }}</div>
                        <div style="font-size:.8rem;color:#94A3B8">{{ $venta->fecha->format('H:i') }}</div>
                    </td>
                    <td>{{ $venta->items->count() }} ítem(s)</td>
                    <td>
                        @php
                            $medioPagoLabels = [
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                                'tarjeta' => 'Tarjeta',
                                'cuotas_4' => '4 cuotas',
                                'cuotas_20' => '20 cuotas',
                            ];
                            $color = in_array($venta->medio_pago, ['efectivo','transferencia']) ? 'green' : ($venta->medio_pago === 'tarjeta' ? 'amber' : 'blue');
                        @endphp
                        <span class="bs-badge-{{ $color }}">
                            {{ $medioPagoLabels[$venta->medio_pago] ?? $venta->medio_pago }}
                        </span>
                    </td>
                    <td>${{ number_format($venta->subtotal_ars, 0, ',', '.') }}</td>
                    <td>
                        @if($venta->recargo_aplicado > 0)
                            <span style="color:#92400E">+{{ $venta->recargo_aplicado }}%</span>
                        @else
                            —
                        @endif
                    </td>
                    <td><strong style="color:#1E6FBB">${{ number_format($venta->total_ars, 0, ',', '.') }}</strong></td>
                    <td>
                        <button class="bs-btn-small" onclick="alert('Ver detalle venta #{{ $venta->id }}')">Ver</button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem">
            {{ $this->ventas->links() }}
        </div>
    @endif
</div>
