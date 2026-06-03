@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

@php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp
<div class="bs-page-title">
    <span>👥 Clientes</span>
    @if($esAdmin)
        <a href="{{ route('clientes.nuevo') }}" class="bs-btn-black">+ Nuevo cliente</a>
    @endif
</div>

<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por nombre, teléfono o DNI..."/>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('nombre')">Nombre @if($ordenarPor==='nombre'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th class="th-sort" wire:click="ordenar('telefono')">Teléfono @if($ordenarPor==='telefono'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>DNI</th>
                <th>Email</th>
                <th class="th-sort" wire:click="ordenar('ventas_count')">Compras @if($ordenarPor==='ventas_count'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($clientes as $cliente)
        <tr wire:key="cli-{{ $cliente->id }}">
            <td style="font-weight:600">{{ $cliente->nombre }}</td>
            <td>
                @if($cliente->telefono)
                    <a href="https://wa.me/54{{ preg_replace('/\D/','',$cliente->telefono) }}" target="_blank" style="color:var(--bs-success-text)">
                        📱 {{ $cliente->telefono }}
                    </a>
                @else —
                @endif
            </td>
            <td>{{ $cliente->dni ?? '—' }}</td>
            <td style="font-size:.78rem">{{ $cliente->email ?? '—' }}</td>
            <td><span class="bs-badge-blue">{{ $cliente->ventas_count }}</span></td>
            <td><span class="bs-badge-{{ $cliente->activo?'green':'red' }}">{{ $cliente->activo?'Activo':'Inactivo' }}</span></td>
            <td>
                <div class="tbl-actions">
                    @if($esAdmin)
                        <a href="{{ route('clientes.editar', $cliente->id) }}" class="btn-edit">Editar</a>
                        <button wire:click="toggleActivo({{ $cliente->id }})" class="btn-edit">
                            {{ $cliente->activo ? 'Desactivar' : 'Activar' }}
                        </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay clientes registrados</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $clientes->links() }}</div>
</div>
