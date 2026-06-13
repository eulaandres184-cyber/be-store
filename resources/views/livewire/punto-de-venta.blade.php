@push('estilos')@vite(['resources/css/pos.css'])@endpush
@push('scripts')@vite(['resources/js/pos.js'])@endpush
<div>
@if($ventaExitosa)
<div class="bs-card" style="max-width:480px;margin:2rem auto">
    <div class="venta-exitosa">
        <div class="venta-exitosa-check">✅</div>
        <h3>¡Venta registrada!</h3>
        <p>Venta #{{ $ultimaVentaId }} guardada correctamente.</p>
        <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1rem;flex-wrap:wrap">
            <button wire:click="nuevaVenta" class="bs-btn-black">+ Nueva venta</button>
            <a href="{{ route('ventas') }}" class="bs-btn-secondary">Ver historial</a>
        </div>
    </div>
</div>
@else
@if(session('error_imei'))
<div class="bs-alert-danger" style="margin-bottom:.75rem">
    ⚠ {{ session('error_imei') }}
    <a href="{{ route('equipos') }}" style="color:var(--bs-danger-text);font-weight:600;margin-left:.5rem;text-decoration:underline">
        Ir a Equipos para agregar el IMEI →
    </a>
</div>
@endif
<div class="pos-wrap">

    {{-- IZQUIERDA --}}
    <div>
        <div class="bs-page-title" style="margin-bottom:.5rem">🛒 Punto de venta</div>

        {{-- Tabs de categorías --}}
        <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:.6rem">
            <div class="cat-tab {{ $categoriaFiltro==='' ? 'active' : '' }}"
                 wire:click="$set('categoriaFiltro','')">Todos</div>
            @foreach($this->categorias as $cat)
            <div class="cat-tab {{ $categoriaFiltro==$cat->id ? 'active' : '' }}"
                 wire:click="$set('categoriaFiltro','{{ $cat->id }}')">{{ $cat->nombre }}</div>
            @endforeach
        </div>

        {{-- Buscador --}}
        <div style="margin-bottom:.75rem">
            <input class="bs-input" wire:model.live.debounce.300ms="busqueda"
                   placeholder="Buscar por nombre o código... (F2)"
                   autocomplete="off"/>
        </div>

        {{-- Grilla de productos --}}
        @if($this->productos->isEmpty())
            <div class="pos-empty"><div class="pos-empty-icon">📦</div>Sin productos disponibles</div>
        @else
            <div class="prod-grid">
                @foreach($this->productos as $producto)
                <div class="prod-card" wire:click="agregarAlCarrito({{ $producto->id }})" wire:key="prod-{{ $producto->id }}">
                    <div class="prod-card-nombre">{{ $producto->nombre }}</div>
                    <div class="prod-card-cat">{{ $producto->categoria->nombre ?? '' }}</div>
                    <div class="prod-card-precio">${{ number_format($producto->precio_efectivo,0,',','.') }}</div>
                    <div class="prod-card-stock {{ $producto->stock_actual <= $producto->stock_minimo ? 'low' : '' }}">
                        Stock: {{ $producto->stock_actual }} u.
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- DERECHA: carrito --}}
    <div>
        <div class="cart-panel">
            <div class="cart-header">
                <span>Carrito</span>
                @if(!empty($carrito))
                    <button wire:click="vaciarCarrito" class="cart-clear-btn">Vaciar</button>
                @endif
            </div>

            {{-- Cliente --}}
            <div style="padding:.6rem .75rem;border-bottom:1px solid #EEF2F5">
                @if($clienteId)
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:.8rem">
                        <span>👤 <strong>{{ $clienteNombre }}</strong></span>
                        <button wire:click="quitarCliente" style="background:none;border:none;color:var(--bs-muted);cursor:pointer;font-size:.75rem">✕</button>
                    </div>
                @else
                    <div style="position:relative">
                        <input class="bs-input" wire:model.live.debounce.300ms="busquedaCliente"
                               placeholder="Buscar cliente (opcional)..."
                               style="font-size:.78rem;padding:.4rem .7rem"/>
                        @if($this->clientesBuscados->isNotEmpty())
                        <div style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--bs-gray-border);border-radius:0 0 var(--bs-radius-md) var(--bs-radius-md);z-index:20;box-shadow:var(--bs-shadow-md)">
                            @foreach($this->clientesBuscados as $cli)
                            <div wire:click="seleccionarCliente({{ $cli->id }},'{{ addslashes($cli->nombre) }}')"
                                 style="padding:.45rem .75rem;cursor:pointer;font-size:.8rem;border-bottom:1px solid #F1F5F9"
                                 onmouseover="this.style.background='#EEF6FF'" onmouseout="this.style.background=''">
                                {{ $cli->nombre }} @if($cli->telefono)<span style="color:var(--bs-muted)"> · {{ $cli->telefono }}</span>@endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="cart-body">
                @if(empty($carrito))
                    <div class="cart-empty"><div class="cart-empty-icon">🛍️</div>Tocá un producto para agregarlo</div>
                @else
                    @foreach($carrito as $key => $item)
                    <div class="cart-item" wire:key="cart-{{ $key }}">
                        <div class="cart-item-name">{{ $item['nombre'] }}</div>
                        <div class="qty-ctrl">
                            <button class="qty-btn" wire:click="cambiarCantidad('{{ $key }}',-1)">−</button>
                            <span class="qty-num">{{ $item['cantidad'] }}</span>
                            <button class="qty-btn" wire:click="cambiarCantidad('{{ $key }}',1)">+</button>
                        </div>
                        <div class="cart-item-price">${{ number_format($item['precio']*$item['cantidad'],0,',','.') }}</div>
                        <button class="cart-remove" wire:click="quitarDelCarrito('{{ $key }}')">✕</button>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Parte de pago --}}
            <div style="padding:.6rem .75rem;border-top:1px solid #EEF2F5">
                <label style="display:flex;align-items:center;gap:.5rem;font-size:.78rem;cursor:pointer">
                    <input type="checkbox" wire:model.live="tieneParte" style="accent-color:var(--bs-blue)">
                    📱 El cliente entrega un equipo en parte de pago
                </label>
                @if($tieneParte)
                <div style="margin-top:.5rem;display:grid;grid-template-columns:1fr 1fr;gap:.35rem">
                    <input class="bs-input" wire:model="parteMarca"   placeholder="Marca" style="font-size:.75rem;padding:.35rem .6rem"/>
                    <input class="bs-input" wire:model="parteModelo"  placeholder="Modelo" style="font-size:.75rem;padding:.35rem .6rem"/>
                    <input class="bs-input" wire:model="parteImei"    placeholder="IMEI * (obligatorio)" style="font-size:.75rem;padding:.35rem .6rem"/>
                    <input class="bs-input" wire:model="parteBateria" placeholder="Batería %" type="number" style="font-size:.75rem;padding:.35rem .6rem"/>
                    <input class="bs-input" wire:model.live="parteCotUsd" placeholder="Cotización USD" type="number" style="font-size:.75rem;padding:.35rem .6rem"/>
                    <div class="bs-input" style="font-size:.75rem;padding:.35rem .6rem;background:#F0F7FF;color:var(--bs-blue);font-weight:600">
                        -${{ number_format($this->parteCotArs,0,',','.') }} ARS
                    </div>
                </div>
                @endif
            </div>

            {{-- Medio de pago --}}
            <div class="pay-section">
                <div class="pay-label">Medio de pago</div>
                <div class="pay-grid">
                    <div class="pay-opt {{ $medioPago==='efectivo'?'selected':'' }}"     wire:click="$set('medioPago','efectivo')">💵 Efectivo</div>
                    <div class="pay-opt {{ $medioPago==='transferencia'?'selected':'' }}" wire:click="$set('medioPago','transferencia')">📲 MP / Transfer.</div>
                    <div class="pay-opt {{ $medioPago==='tarjeta'?'selected':'' }}"     wire:click="$set('medioPago','tarjeta')">💳 Tarjeta +{{ $this->config?->recargo_tarjeta??15 }}%</div>
                    <div class="pay-opt {{ $medioPago==='cuotas_4'?'selected':'' }}"    wire:click="$set('medioPago','cuotas_4')">🏦 BLP 4c +{{ $this->config?->cuotas_4_recargo??2 }}%</div>
                    <div class="pay-opt pay-full {{ $medioPago==='cuotas_20'?'selected':'' }}" wire:click="$set('medioPago','cuotas_20')">🏦 BLP 20 cuotas +{{ $this->config?->cuotas_20_recargo??20 }}%</div>
                </div>
                <input class="bs-input" wire:model="notas" placeholder="Notas opcionales..." style="font-size:.78rem"/>
            </div>

            {{-- Total --}}
            <div class="total-section">
                <div class="total-row"><span>Subtotal</span><span>${{ number_format($this->subtotal,0,',','.') }}</span></div>
                @if($this->recargoMonto > 0)
                <div class="total-row total-row-warn"><span>Recargo</span><span>+${{ number_format($this->recargoMonto,0,',','.') }}</span></div>
                @endif
                @if($tieneParte && $this->parteCotArs > 0)
                <div class="total-row" style="color:var(--bs-success-text)"><span>Parte de pago</span><span>-${{ number_format($this->parteCotArs,0,',','.') }}</span></div>
                @endif
                <div class="total-final"><span>Total a cobrar</span><span>${{ number_format($this->total,0,',','.') }}</span></div>
                <button class="btn-registrar" wire:click="registrarVenta" wire:loading.attr="disabled" @if(empty($carrito)) disabled @endif>
                    <span wire:loading.remove wire:target="registrarVenta">✓ Registrar venta</span>
                    <span wire:loading wire:target="registrarVenta">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
