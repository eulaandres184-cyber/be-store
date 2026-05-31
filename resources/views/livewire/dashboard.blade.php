<div>
@php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp

<div class="bs-dashboard-grid">
    <div class="bs-dashboard-left">
        {{-- Banner dólar blue --}}
        <div class="bs-dolar-banner">
            <div>
                <div class="bs-dolar-label">Dólar blue hoy</div>
                <div class="bs-dolar-val">${{ number_format($dolarHoy,2,',','.') }}</div>
                <div class="bs-dolar-time">{{ $dolarActualizado }}</div>
            </div>
            @if($esAdmin)
            <a href="{{ route('configuracion') }}" class="bs-btn-secondary" style="font-size:.78rem">Actualizar</a>
            @endif
        </div>

        {{-- Métricas --}}
        <div class="bs-metrics">
            <div class="bs-metric">
                <div class="bs-metric-label">Ventas hoy</div>
                <div class="bs-metric-value">${{ number_format($ventasHoy,0,',','.') }}</div>
                <div class="bs-metric-sub">{{ $cantVentasHoy }} transacciones</div>
            </div>
            <div class="bs-metric" style="border-left-color:#1E8449">
                <div class="bs-metric-label">Productos activos</div>
                <div class="bs-metric-value" style="color:#1E8449">{{ $totalProductos }}</div>
                <div class="bs-metric-sub">en catálogo</div>
            </div>
            <div class="bs-metric" style="border-left-color:{{ $stockBajo>0?'#C0392B':'#1E8449' }}">
                <div class="bs-metric-label">Stock bajo mínimo</div>
                <div class="bs-metric-value" style="color:{{ $stockBajo>0?'#C0392B':'#1E8449' }}">{{ $stockBajo }}</div>
                <div class="bs-metric-sub">productos</div>
            </div>
            <div class="bs-metric" style="border-left-color:#7D3C98">
                <div class="bs-metric-label">Equipos disponibles</div>
                <div class="bs-metric-value" style="color:#7D3C98">{{ $equiposDisponibles }}</div>
                <div class="bs-metric-sub">para venta</div>
            </div>
        </div>
    </div>

    <div class="bs-dashboard-right">
        {{-- Bloques de acceso --}}
        <div class="bs-card">
            <div class="bs-card-title">Accesos rápidos</div>
            <div class="bs-grid-bloques">
                {{-- Siempre visibles --}}
                <a href="{{ route('pos') }}" class="bs-bloque bloque-pos">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                    <div class="bs-bloque-label">Nueva venta</div>
                    <div class="bs-bloque-sub">Punto de venta</div>
                </a>
                <a href="{{ route('productos') }}" class="bs-bloque bloque-productos">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                    <div class="bs-bloque-label">Productos</div>
                    <div class="bs-bloque-sub">Stock y precios</div>
                </a>
                <a href="{{ route('equipos') }}" class="bs-bloque bloque-equipos">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></div>
                    <div class="bs-bloque-label">Equipos</div>
                    <div class="bs-bloque-sub">Celulares · IMEI</div>
                </a>
                <a href="{{ route('clientes') }}" class="bs-bloque bloque-usuarios">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                    <div class="bs-bloque-label">Clientes</div>
                    <div class="bs-bloque-sub">Historial de compras</div>
                </a>
                <a href="{{ route('ventas') }}" class="bs-bloque bloque-ventas">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                    <div class="bs-bloque-label">Ventas</div>
                    <div class="bs-bloque-sub">Historial</div>
                </a>
                <a href="{{ route('devoluciones') }}" class="bs-bloque bloque-reportes">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                    <div class="bs-bloque-label">Devoluciones</div>
                    <div class="bs-bloque-sub">Fallas y cambios</div>
                </a>
                <a href="{{ route('documentos') }}" class="bs-bloque bloque-config">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                    <div class="bs-bloque-label">Documentos</div>
                    <div class="bs-bloque-sub">Tickets · Facturas</div>
                </a>

                {{-- Solo admin --}}
                @if($esAdmin)
                <a href="{{ route('compras') }}" class="bs-bloque bloque-compras">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></div>
                    <div class="bs-bloque-label">Compras</div>
                    <div class="bs-bloque-sub">Entrada de stock</div>
                </a>
                <a href="{{ route('categorias') }}" class="bs-bloque bloque-compras">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></div>
                    <div class="bs-bloque-label">Categorías</div>
                    <div class="bs-bloque-sub">ABM</div>
                </a>
                <a href="{{ route('proveedores') }}" class="bs-bloque bloque-proveedores">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg></div>
                    <div class="bs-bloque-label">Proveedores</div>
                    <div class="bs-bloque-sub">Contactos</div>
                </a>
                <a href="{{ route('reportes') }}" class="bs-bloque bloque-reportes">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                    <div class="bs-bloque-label">Reportes</div>
                    <div class="bs-bloque-sub">Análisis</div>
                </a>
                <a href="{{ route('usuarios') }}" class="bs-bloque bloque-usuarios">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg></div>
                    <div class="bs-bloque-label">Usuarios</div>
                    <div class="bs-bloque-sub">Roles y accesos</div>
                </a>
                <a href="{{ route('configuracion') }}" class="bs-bloque bloque-config">
                    <div class="bs-bloque-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg></div>
                    <div class="bs-bloque-label">Configuración</div>
                    <div class="bs-bloque-sub">Recargos · Dólar</div>
                </a>
                @endif
            </div>
        </div>

        {{-- Últimas ventas --}}
        @if($ultimasVentas->count() > 0)
        <div class="bs-card">
            <div class="bs-card-title">Últimas ventas del día</div>
            <table class="bs-table">
                <thead>
                    <tr><th>Hora</th><th>Cliente</th><th>Ítems</th><th>Medio de pago</th><th>Total</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($ultimasVentas as $venta)
                <tr>
                    <td>{{ $venta->fecha->format('H:i') }}</td>
                    <td>{{ $venta->cliente?->nombre ?? '—' }}</td>
                    <td>{{ $venta->items->count() }}</td>
                    <td>
                        <span class="bs-badge-{{ in_array($venta->medio_pago,['efectivo','transferencia'])?'green':($venta->medio_pago==='tarjeta'?'amber':'blue') }}">
                            {{ $venta->label_medio_pago }}
                        </span>
                    </td>
                    <td style="color:var(--bs-blue);font-weight:700">${{ number_format($venta->total_ars,0,',','.') }}</td>
                    <td><a href="{{ route('documentos.emitir', $venta->id) }}" class="btn-edit">📄 Emitir</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
</div>
