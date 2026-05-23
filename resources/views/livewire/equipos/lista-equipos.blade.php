@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

@php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp
<div class="bs-page-title">
    <span>📱 Equipos móviles</span>
    @if($esAdmin)
        <a href="{{ route('equipos.nuevo') }}" class="bs-btn-black">+ Nuevo equipo</a>
    @endif
</div>

{{-- Banner dólar --}}
<div class="bs-dolar-banner" style="margin-bottom:1rem">
    <div>
        <div class="bs-dolar-label">Dólar blue hoy</div>
        <div class="bs-dolar-val">${{ number_format($this->dolar, 2, ',', '.') }}</div>
    </div>
    <div style="color:#aaa;font-size:.8rem">Los precios en ARS se calculan automáticamente</div>
</div>

{{-- Filtros --}}
<div class="productos-filtros">
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por marca, modelo, IMEI o color..."/>
    <select class="bs-select" wire:model.live="marcaFiltro">
        <option value="">Todas las marcas</option>
        @foreach($this->marcas as $marca)
            <option value="{{ $marca }}">{{ $marca }}</option>
        @endforeach
    </select>
</div>

<div class="estado-tabs">
    <div class="estado-tab {{ $estadoFiltro==='disponible' ?'activo':'' }}" wire:click="$set('estadoFiltro','disponible')">Disponibles</div>
    <div class="estado-tab {{ $estadoFiltro==='reservado'  ?'activo':'' }}" wire:click="$set('estadoFiltro','reservado')">Reservados</div>
    <div class="estado-tab {{ $estadoFiltro==='vendido'    ?'activo':'' }}" wire:click="$set('estadoFiltro','vendido')">Vendidos</div>
    <div class="estado-tab {{ $estadoFiltro===''           ?'activo':'' }}" wire:click="$set('estadoFiltro','')">Todos</div>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th class="th-sort" wire:click="ordenar('marca')">Marca/Modelo @if($ordenarPor==='marca'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th class="th-sort" wire:click="ordenar('imei')">IMEI @if($ordenarPor==='imei'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Detalle</th>
                <th class="th-sort" wire:click="ordenar('precio_usd')">Precio USD @if($ordenarPor==='precio_usd'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th>Precio ARS</th>
                <th class="th-sort" wire:click="ordenar('estado')">Estado @if($ordenarPor==='estado'){{ $direccion==='asc'?'↑':'↓' }}@else<span class="th-icon">↕</span>@endif</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($equipos as $equipo)
        <tr wire:key="eq-{{ $equipo->id }}">
            <td>
                <div style="font-weight:600">{{ $equipo->marca }} {{ $equipo->modelo }}</div>
                @if($equipo->capacidad_gb)<div style="font-size:.72rem;color:var(--bs-muted)">{{ $equipo->capacidad_gb }}GB</div>@endif
            </td>
            <td style="font-family:var(--bs-font-mono);font-size:.78rem">{{ $equipo->imei }}</td>
            <td>
                <div style="font-size:.78rem">
                    @if($equipo->color)<span class="bs-badge-blue">{{ $equipo->color }}</span>@endif
                    <span class="bs-badge-{{ $equipo->condicion==='nuevo'?'green':($equipo->condicion==='usado'?'amber':'blue') }}" style="margin-left:2px">
                        {{ ucfirst($equipo->condicion) }}
                    </span>
                    @if($equipo->bateria_pct)<div style="margin-top:3px">🔋 {{ $equipo->bateria_pct }}%</div>@endif
                </div>
            </td>
            <td style="font-weight:700;color:var(--bs-blue)">u$s {{ number_format($equipo->precio_usd,0,',','.') }}</td>
            <td style="font-weight:600">${{ number_format($equipo->precio_usd * $this->dolar,0,',','.') }}</td>
            <td>
                <span class="bs-badge-{{ $equipo->estado==='disponible'?'green':($equipo->estado==='reservado'?'amber':'red') }}">
                    {{ ucfirst($equipo->estado) }}
                </span>
            </td>
            <td>
                <div class="tbl-actions">
                    @if($esAdmin)
                        <a href="{{ route('equipos.editar', $equipo->id) }}" class="btn-edit">Editar</a>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--bs-muted)">No se encontraron equipos</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $equipos->links() }}</div>
</div>
