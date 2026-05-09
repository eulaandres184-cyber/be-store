@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

<div class="bs-page-title">
    <span>🛒 Compras</span>
    <a href="{{ route('compras.nueva') }}" class="bs-btn-black">+ Nueva compra</a>
</div>

<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por proveedor..."/>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('fecha')">Fecha @if($ordenarPor==='fecha'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Proveedor</th>
                <th class="th-sort" wire:click="ordenar('total_ars')">Total @if($ordenarPor==='total_ars'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Ítems</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
        @forelse($compras as $compra)
        <tr wire:key="compra-{{ $compra->id }}">
            <td>{{ $compra->fecha->format('d/m/Y') }}</td>
            <td style="font-weight:500">{{ $compra->proveedor->nombre ?? '—' }}</td>
            <td style="font-weight:700;color:var(--bs-blue)">${{ number_format($compra->total_ars,0,',','.') }}</td>
            <td>{{ $compra->items->count() }} producto(s)</td>
            <td style="font-size:.78rem;color:var(--bs-muted)">{{ $compra->notas ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay compras registradas</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $compras->links() }}</div>
</div>
