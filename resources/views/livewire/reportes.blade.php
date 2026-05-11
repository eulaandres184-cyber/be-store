@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>📊 Reportes</span>
    <div style="display:flex;gap:.5rem">
        <div class="estado-tab {{ $periodo==='7dias'  ?'activo':'' }}" wire:click="$set('periodo','7dias')">7 días</div>
        <div class="estado-tab {{ $periodo==='30dias' ?'activo':'' }}" wire:click="$set('periodo','30dias')">30 días</div>
        <div class="estado-tab {{ $periodo==='mes'    ?'activo':'' }}" wire:click="$set('periodo','mes')">Este mes</div>
    </div>
</div>

<div class="bs-metrics">
    <div class="bs-metric"><div class="bs-metric-label">Total vendido</div><div class="bs-metric-value">${{ number_format($this->ventasTotal,0,',','.') }}</div></div>
    <div class="bs-metric" style="border-left-color:#1E8449"><div class="bs-metric-label">Transacciones</div><div class="bs-metric-value" style="color:#1E8449">{{ $this->cantVentas }}</div></div>
    <div class="bs-metric" style="border-left-color:#7D3C98"><div class="bs-metric-label">Ticket promedio</div><div class="bs-metric-value" style="color:#7D3C98">${{ number_format($this->ticketPromedio,0,',','.') }}</div></div>
    <div class="bs-metric" style="border-left-color:#C0392B"><div class="bs-metric-label">Total compras</div><div class="bs-metric-value" style="color:#C0392B">${{ number_format($this->comprasTotal,0,',','.') }}</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
    <div class="bs-card">
        <div class="bs-card-title">Ventas por medio de pago</div>
        @forelse($this->ventasPorMedioPago as $mp)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #F1F5F9;font-size:.82rem">
            <span>{{ ucfirst(str_replace('_',' ',$mp->medio_pago)) }}</span>
            <div style="text-align:right">
                <div style="font-weight:700;color:var(--bs-blue)">${{ number_format($mp->total,0,',','.') }}</div>
                <div style="font-size:.7rem;color:var(--bs-muted)">{{ $mp->cantidad }} venta(s)</div>
            </div>
        </div>
        @empty
        <p style="color:var(--bs-muted);font-size:.82rem">Sin ventas en el período</p>
        @endforelse
    </div>

    <div class="bs-card">
        <div class="bs-card-title">Ventas por categoría</div>
        @forelse($this->ventasPorCategoria as $cat)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #F1F5F9;font-size:.82rem">
            <span>{{ $cat->categoria }}</span>
            <span style="font-weight:700;color:var(--bs-blue)">${{ number_format($cat->total,0,',','.') }}</span>
        </div>
        @empty
        <p style="color:var(--bs-muted);font-size:.82rem">Sin ventas en el período</p>
        @endforelse
    </div>
</div>

<div class="bs-card">
    <div class="bs-card-title">Productos más vendidos</div>
    <table class="bs-table">
        <thead><tr><th>Producto</th><th>Unidades</th><th>Total ARS</th></tr></thead>
        <tbody>
        @forelse($this->productosMasVendidos as $item)
        <tr>
            <td>{{ $item->producto?->nombre ?? '—' }}</td>
            <td style="font-weight:600">{{ $item->total_vendido }}</td>
            <td style="color:var(--bs-blue);font-weight:700">${{ number_format($item->total_ars,0,',','.') }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center;color:var(--bs-muted);padding:1.5rem">Sin ventas en el período</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</div>
