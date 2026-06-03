@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title"><span>📋 Historial de ventas</span></div>

{{-- Filtros --}}
<div class="productos-filtros" style="margin-bottom:.75rem">
    <input class="bs-input" type="date" wire:model.live="desde" style="width:auto"/>
    <input class="bs-input" type="date" wire:model.live="hasta" style="width:auto"/>
    <select class="bs-select" wire:model.live="medioPago" style="width:auto">
        <option value="">Todos los medios</option>
        <option value="efectivo">Efectivo</option>
        <option value="transferencia">Transferencia / MP</option>
        <option value="tarjeta">Tarjeta</option>
        <option value="cuotas_4">BLP 4 cuotas</option>
        <option value="cuotas_20">BLP 20 cuotas</option>
    </select>
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por cliente..."/>
</div>

{{-- Totales del período --}}
<div class="bs-metrics" style="margin-bottom:.75rem">
    <div class="bs-metric">
        <div class="bs-metric-label">Total del período</div>
        <div class="bs-metric-value">${{ number_format($this->totales['total'],0,',','.') }}</div>
    </div>
    <div class="bs-metric" style="border-left-color:#1E8449">
        <div class="bs-metric-label">Transacciones</div>
        <div class="bs-metric-value" style="color:#1E8449">{{ $this->totales['cantidad'] }}</div>
    </div>
    <div class="bs-metric" style="border-left-color:#7D3C98">
        <div class="bs-metric-label">Ticket promedio</div>
        <div class="bs-metric-value" style="color:#7D3C98">${{ number_format($this->totales['promedio'],0,',','.') }}</div>
    </div>
</div>

{{-- Tabla --}}
<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('fecha')">Fecha/Hora @if($ordenarPor==='fecha'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Cliente</th>
                <th>Ítems</th>
                <th class="th-sort" wire:click="ordenar('medio_pago')">Medio de pago @if($ordenarPor==='medio_pago'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th class="th-sort" wire:click="ordenar('total_ars')">Total @if($ordenarPor==='total_ars'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($ventas as $venta)
        <tr wire:key="v-{{ $venta->id }}">
            <td>
                <div style="font-weight:500">{{ $venta->fecha->format('d/m/Y') }}</div>
                <div style="font-size:.72rem;color:var(--bs-muted)">{{ $venta->fecha->format('H:i') }} hs</div>
            </td>
            <td>{{ $venta->cliente?->nombre ?? '—' }}</td>
            <td>{{ $venta->items->count() }}</td>
            <td>
                <span class="bs-badge-{{ in_array($venta->medio_pago,['efectivo','transferencia'])?'green':($venta->medio_pago==='tarjeta'?'amber':'blue') }}">
                    {{ $venta->label_medio_pago }}
                </span>
            </td>
            <td style="font-weight:700;color:var(--bs-blue)">${{ number_format($venta->total_ars,0,',','.') }}</td>
            <td>
                <button wire:click="verDetalle({{ $venta->id }})" class="btn-edit">Ver</button>
                    <a href="{{ route('documentos.emitir', $venta->id) }}" class="btn-edit">📄 Emitir</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay ventas en el período seleccionado</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $ventas->links() }}</div>

{{-- Modal detalle de venta --}}
@if($ventaDetalle && $this->ventaDetalleData)
@php $v = $this->ventaDetalleData; @endphp
<div class="scanner-overlay" style="display:flex">
    <div class="scanner-box" style="max-width:500px;width:100%">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
            <h3 style="font-size:1rem;font-weight:600">Venta #{{ $v->id }}</h3>
            <button wire:click="cerrarDetalle" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--bs-muted)">✕</button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.75rem;font-size:.8rem">
            <div><span style="color:var(--bs-muted)">Fecha:</span> {{ $v->fecha->format('d/m/Y H:i') }}</div>
            <div><span style="color:var(--bs-muted)">Vendedor:</span> {{ $v->usuario?->name ?? '—' }}</div>
            <div><span style="color:var(--bs-muted)">Cliente:</span> {{ $v->cliente?->nombre ?? '—' }}</div>
            <div><span style="color:var(--bs-muted)">Medio:</span> {{ $v->label_medio_pago }}</div>
        </div>

        <table class="bs-table" style="margin-bottom:.75rem">
            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
            <tbody>
            @foreach($v->items as $item)
            <tr>
                <td style="font-size:.78rem">{{ $item->producto?->nombre ?? '—' }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>${{ number_format($item->precio_unitario_ars,0,',','.') }}</td>
                <td style="font-weight:600">${{ number_format($item->subtotal_ars,0,',','.') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>

        @if($v->partePago)
        <div class="bs-alert-success" style="font-size:.8rem;margin-bottom:.75rem">
            📱 Parte de pago: {{ $v->partePago->marca }} {{ $v->partePago->modelo }}
            — u$s {{ $v->partePago->cotizacion_usd }} = ${{ number_format($v->partePago->valor_ars,0,',','.') }}
        </div>
        @endif

        <div style="border-top:2px solid var(--bs-dark);padding-top:.5rem">
            @if($v->recargo_aplicado > 0)
            <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem">
                <span>Subtotal</span><span>${{ number_format($v->subtotal_ars,0,',','.') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;color:var(--bs-warning-text)">
                <span>Recargo ({{ $v->recargo_aplicado }}%)</span>
                <span>+${{ number_format($v->subtotal_ars * $v->recargo_aplicado / 100,0,',','.') }}</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;color:var(--bs-blue)">
                <span>Total</span><span>${{ number_format($v->total_ars,0,',','.') }}</span>
            </div>
        </div>

        @if($v->notas)
        <div style="margin-top:.5rem;font-size:.78rem;color:var(--bs-muted)">📝 {{ $v->notas }}</div>
        @endif

        <div style="display:flex;gap:.5rem;margin-top:1rem">
            <button wire:click="cerrarDetalle" class="bs-btn-secondary" style="flex:1">Cerrar</button>
            <a href="javascript:window.print()" class="bs-btn-black" style="flex:1;justify-content:center">🖨️ Imprimir</a>
        </div>
    </div>
</div>
@endif
</div>
