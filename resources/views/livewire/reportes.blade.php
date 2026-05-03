<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Reportes</h1>
        <p>Análisis y métricas del negocio.</p>
    </header>

    <div class="bs-form-row" style="margin-bottom:1rem">
        <label>Período:</label>
        <select class="bs-select" wire:model.live="periodo" style="max-width:180px">
            <option value="7">Últimos 7 días</option>
            <option value="30">Últimos 30 días</option>
            <option value="90">Últimos 90 días</option>
            <option value="365">Este año</option>
        </select>
    </div>

    {{-- KPIs principales --}}
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1.5rem">
        <div class="bs-metric">
            <div class="bs-metric-label">Ventas</div>
            <div class="bs-metric-value" style="color:#1E6FBB">${{ number_format($ventasPeriodo, 0, ',', '.') }}</div>
        </div>
        <div class="bs-metric">
            <div class="bs-metric-label">Compras</div>
            <div class="bs-metric-value" style="color:#EF4444">${{ number_format($comprasPeriodo, 0, ',', '.') }}</div>
        </div>
        <div class="bs-metric">
            <div class="bs-metric-label">Ganancia</div>
            <div class="bs-metric-value" style="color:{{ $ganancia >= 0 ? '#166534' : '#EF4444' }}">
                @if($ganancia >= 0) + @endif
                ${{ number_format($ganancia, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem">
        {{-- Productos más vendidos --}}
        <div class="bs-card">
            <div class="bs-card-title">Productos más vendidos</div>
            @if($productosMasVendidos->isEmpty())
                <div style="padding:1rem; text-align:center; color:#94A3B8; font-size:.85rem">
                    Sin datos para este período
                </div>
            @else
                <table class="bs-table" style="font-size:.85rem">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align:right">Cantidad</th>
                            <th style="text-align:right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($productosMasVendidos as $prod)
                    <tr>
                        <td><strong>{{ $prod['nombre'] }}</strong></td>
                        <td style="text-align:right">{{ $prod['cantidad'] }}</td>
                        <td style="text-align:right; color:#1E6FBB">${{ number_format($prod['monto'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Ventas por medio de pago --}}
        <div class="bs-card">
            <div class="bs-card-title">Ventas por medio de pago</div>
            @if($ventasPorMedio->isEmpty())
                <div style="padding:1rem; text-align:center; color:#94A3B8; font-size:.85rem">
                    Sin datos para este período
                </div>
            @else
                <table class="bs-table" style="font-size:.85rem">
                    <thead>
                        <tr>
                            <th>Medio</th>
                            <th style="text-align:right">Transacciones</th>
                            <th style="text-align:right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $medioPagoLabels = [
                            'efectivo' => 'Efectivo',
                            'transferencia' => 'Transferencia',
                            'tarjeta' => 'Tarjeta',
                            'cuotas_4' => '4 cuotas',
                            'cuotas_20' => '20 cuotas',
                        ];
                    @endphp
                    @foreach($ventasPorMedio as $vpm)
                    <tr>
                        <td><strong>{{ $medioPagoLabels[$vpm->medio_pago] ?? $vpm->medio_pago }}</strong></td>
                        <td style="text-align:right">{{ $vpm->cantidad }}</td>
                        <td style="text-align:right; color:#1E6FBB">${{ number_format($vpm->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
