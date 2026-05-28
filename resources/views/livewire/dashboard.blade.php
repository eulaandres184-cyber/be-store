<div>
@php $esAdmin = (auth()->user()->rol ?? 'vendedor') === 'admin'; @endphp

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

{{-- Bloques de acceso rápido en cuadrícula --}}
<div class="bs-card">
    <div class="bs-card-title">Accesos rápidos</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem;">

        {{-- Siempre visibles --}}
        <a href="{{ route('pos') }}" style="display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:1rem .75rem;background:#2C3E50;border-radius:12px;text-decoration:none;color:#fff;transition:all .15s;">
            <div style="width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div style="font-size:.82rem;font-weight:600;text-align:center;">Nueva venta</div>
            <div style="font-size:.68rem;color:#A8C6E0;text-align:center;">Punto de venta</div>
        </a>

        @php
        $bloques = [
            ['ruta'=>'productos',   'icono'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label'=>'Productos',    'sub'=>'Stock y precios',    'color'=>'#EBF5FF', 'icon_color'=>'#2471A3'],
            ['ruta'=>'equipos',     'icono'=>'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z', 'label'=>'Equipos', 'sub'=>'Celulares · IMEI', 'color'=>'#D5F5E3', 'icon_color'=>'#1E8449'],
            ['ruta'=>'clientes',    'icono'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label'=>'Clientes', 'sub'=>'Historial de compras', 'color'=>'#D6EAF8', 'icon_color'=>'#1A5276'],
            ['ruta'=>'ventas',      'icono'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label'=>'Ventas', 'sub'=>'Historial', 'color'=>'#FEF9E7', 'icon_color'=>'#9A7D0A'],
            ['ruta'=>'devoluciones','icono'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'label'=>'Devoluciones', 'sub'=>'Fallas y cambios', 'color'=>'#FDEDEC', 'icon_color'=>'#922B21'],
            ['ruta'=>'documentos',  'icono'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label'=>'Documentos', 'sub'=>'Tickets · Facturas', 'color'=>'#EAECEE', 'icon_color'=>'#566573'],
        ];

        $bloquesAdmin = [
            ['ruta'=>'compras',     'icono'=>'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'label'=>'Compras', 'sub'=>'Entrada de stock', 'color'=>'#F3E8FF', 'icon_color'=>'#7D3C98'],
            ['ruta'=>'categorias',  'icono'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', 'label'=>'Categorías', 'sub'=>'ABM', 'color'=>'#FEF3C7', 'icon_color'=>'#9A6B00'],
            ['ruta'=>'proveedores', 'icono'=>'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z', 'label'=>'Proveedores', 'sub'=>'Contactos', 'color'=>'#FDF2F8', 'icon_color'=>'#9D174D'],
            ['ruta'=>'reportes',    'icono'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label'=>'Reportes', 'sub'=>'Análisis', 'color'=>'#FFEDD5', 'icon_color'=>'#C2410C'],
            ['ruta'=>'usuarios',    'icono'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197', 'label'=>'Usuarios', 'sub'=>'Roles y accesos', 'color'=>'#E0F2FE', 'icon_color'=>'#0369A1'],
            ['ruta'=>'configuracion','icono'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M12 9a3 3 0 110 6 3 3 0 010-6z', 'label'=>'Configuración', 'sub'=>'Recargos · Dólar', 'color'=>'#F1F5F9', 'icon_color'=>'#475569'],
        ];

        $listaBloques = array_merge($bloques, $esAdmin ? $bloquesAdmin : []);
        @endphp

        @foreach($listaBloques as $b)
        <a href="{{ route($b['ruta']) }}" style="display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:1rem .75rem;background:#fff;border:1.5px solid #DDE6EE;border-radius:12px;text-decoration:none;color:#2C3E50;transition:all .15s;" onmouseover="this.style.borderColor='{{ $b['icon_color'] }}';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 12px rgba(0,0,0,.08)'" onmouseout="this.style.borderColor='#DDE6EE';this.style.transform='';this.style.boxShadow=''">
            <div style="width:44px;height:44px;border-radius:10px;background:{{ $b['color'] }};display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" stroke="{{ $b['icon_color'] }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $b['icono'] }}"/></svg>
            </div>
            <div style="font-size:.82rem;font-weight:600;text-align:center;">{{ $b['label'] }}</div>
            <div style="font-size:.68rem;color:#7A8FA6;text-align:center;">{{ $b['sub'] }}</div>
        </a>
        @endforeach
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
