<x-layouts.app title="Dashboard">
<style>
.bs-grid-bloques {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: .75rem;
    margin-bottom: 1.25rem;
}
.bs-bloque {
    background: #fff;
    border: 1px solid var(--bs-gray-border);
    border-radius: 14px;
    padding: 1.25rem 1rem;
    text-align: center;
    text-decoration: none;
    color: var(--bs-text);
    transition: all .15s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
}
.bs-bloque:hover { border-color: var(--bs-blue); background: var(--bs-blue-light); color: var(--bs-blue); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30,111,187,.1); }
.bs-bloque-icon { width: 44px; height: 44px; border-radius: 12px; background: var(--bs-blue-mid); display: flex; align-items: center; justify-content: center; }
.bs-bloque-icon svg { width: 22px; height: 22px; color: var(--bs-blue); }
.bs-bloque-label { font-size: .82rem; font-weight: 500; }
.bs-bloque-sub { font-size: .72rem; color: var(--bs-muted); }
.bs-bloque.bloque-pos { border-color: var(--bs-blue); background: var(--bs-blue); color: #fff; }
.bs-bloque.bloque-pos .bs-bloque-icon { background: rgba(255,255,255,.2); }
.bs-bloque.bloque-pos .bs-bloque-icon svg { color: #fff; }
.bs-bloque.bloque-pos .bs-bloque-sub { color: #93C5FD; }
.bs-bloque.bloque-pos:hover { background: #1558A0; }
.bs-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
.bs-metric { background: #fff; border: 1px solid var(--bs-gray-border); border-radius: 12px; padding: .85rem 1rem; }
.bs-metric-label { font-size: .72rem; color: var(--bs-muted); margin-bottom: .25rem; }
.bs-metric-value { font-size: 1.35rem; font-weight: 600; color: var(--bs-blue); }
.bs-metric-sub { font-size: .7rem; color: #94A3B8; margin-top: .15rem; }
.dolar-banner { background: var(--bs-blue-light); border: 1px solid var(--bs-blue-mid); border-radius: 12px; padding: .85rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; }
.dolar-banner-left { display: flex; flex-direction: column; gap: 2px; }
.dolar-banner-label { font-size: .72rem; color: #3B82F6; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.dolar-banner-val { font-size: 1.6rem; font-weight: 700; color: var(--bs-blue); }
.dolar-banner-time { font-size: .7rem; color: #94A3B8; }
</style>

{{-- Banner dólar blue --}}
<div class="dolar-banner">
    <div class="dolar-banner-left">
        <div class="dolar-banner-label">Dólar blue hoy</div>
        <div class="dolar-banner-val">${{ number_format($dolarHoy, 2, ',', '.') }}</div>
        <div class="dolar-banner-time">{{ $dolarActualizado }}</div>
    </div>
    <a href="{{ route('configuracion') }}" class="bs-btn-secondary" style="font-size:.78rem">Actualizar</a>
</div>

{{-- Métricas del día --}}
<div class="bs-metrics">
    <div class="bs-metric">
        <div class="bs-metric-label">Ventas hoy</div>
        <div class="bs-metric-value">${{ number_format($ventasHoy, 0, ',', '.') }}</div>
        <div class="bs-metric-sub">{{ $cantVentasHoy }} transacciones</div>
    </div>
    <div class="bs-metric">
        <div class="bs-metric-label">Productos activos</div>
        <div class="bs-metric-value">{{ $totalProductos }}</div>
        <div class="bs-metric-sub">en catálogo</div>
    </div>
    <div class="bs-metric">
        <div class="bs-metric-label">Stock bajo mínimo</div>
        <div class="bs-metric-value" style="color:{{ $stockBajo > 0 ? '#EF4444' : '#1E6FBB' }}">{{ $stockBajo }}</div>
        <div class="bs-metric-sub">productos</div>
    </div>
    <div class="bs-metric">
        <div class="bs-metric-label">Equipos disponibles</div>
        <div class="bs-metric-value">{{ $equiposDisponibles }}</div>
        <div class="bs-metric-sub">para venta</div>
    </div>
</div>

{{-- Bloques de acceso directo --}}
<div class="bs-grid-bloques">
    <a href="{{ route('pos') }}" class="bs-bloque bloque-pos">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
        <div class="bs-bloque-label">Nueva venta</div>
        <div class="bs-bloque-sub">Punto de venta</div>
    </a>
    <a href="{{ route('productos') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
        <div class="bs-bloque-label">Productos</div>
        <div class="bs-bloque-sub">Stock y precios</div>
    </a>
    <a href="{{ route('equipos') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
        <div class="bs-bloque-label">Equipos</div>
        <div class="bs-bloque-sub">Celulares · IMEI</div>
    </a>
    <a href="{{ route('ventas') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        <div class="bs-bloque-label">Ventas</div>
        <div class="bs-bloque-sub">Historial</div>
    </a>
    <a href="{{ route('compras') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
        <div class="bs-bloque-label">Compras</div>
        <div class="bs-bloque-sub">Proveedores</div>
    </a>
    <a href="{{ route('reportes') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
        <div class="bs-bloque-label">Reportes</div>
        <div class="bs-bloque-sub">Análisis de ventas</div>
    </a>
    <a href="{{ route('usuarios') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <div class="bs-bloque-label">Usuarios</div>
        <div class="bs-bloque-sub">Accesos y roles</div>
    </a>
    <a href="{{ route('proveedores') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
        <div class="bs-bloque-label">Proveedores</div>
        <div class="bs-bloque-sub">Contactos</div>
    </a>
    <a href="{{ route('configuracion') }}" class="bs-bloque">
        <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg></div>
        <div class="bs-bloque-label">Configuración</div>
        <div class="bs-bloque-sub">Recargos · Dólar</div>
    </a>
</div>

{{-- Últimas ventas del día --}}
@if($ultimasVentas->count() > 0)
<div class="bs-card">
    <div class="bs-card-title">Últimas ventas del día</div>
    <table class="bs-table">
        <thead><tr><th>Hora</th><th>Productos</th><th>Medio de pago</th><th>Total</th></tr></thead>
        <tbody>
        @foreach($ultimasVentas as $venta)
        <tr>
            <td>{{ $venta->fecha->format('H:i') }}</td>
            <td>{{ $venta->items->count() }} ítem(s)</td>
            <td><span class="bs-badge-{{ $venta->medio_pago === 'efectivo' || $venta->medio_pago === 'transferencia' ? 'green' : ($venta->medio_pago === 'tarjeta' ? 'amber' : 'blue') }}">{{ $venta->label_medio_pago }}</span></td>
            <td style="color:#1E6FBB;font-weight:600">${{ number_format($venta->total_ars, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
</x-layouts.app>