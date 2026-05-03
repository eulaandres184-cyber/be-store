<div>
<style>
.pos-wrap { display: grid; grid-template-columns: 1.6fr 1fr; gap: 1rem; min-height: calc(100vh - 80px); }
@media(max-width:768px){ .pos-wrap { grid-template-columns: 1fr; } }

/* Panel izquierdo */
.pos-search { display: flex; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; }
.pos-search input { flex: 1; min-width: 160px; }
.pos-search select { min-width: 130px; }
.prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .6rem; }
.prod-card { background: #fff; border: 2px solid #D0DCE8; border-radius: 10px; padding: .75rem; cursor: pointer; transition: all .15s; }
.prod-card:hover { border-color: #1E6FBB; background: #EFF6FF; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(30,111,187,.12); }
.prod-card-nombre { font-size: .8rem; font-weight: 600; color: #1A1A2E; margin-bottom: .2rem; line-height: 1.3; }
.prod-card-cat { font-size: .7rem; color: #94A3B8; margin-bottom: .4rem; }
.prod-card-precio { font-size: .95rem; font-weight: 700; color: #1E6FBB; }
.prod-card-stock { font-size: .68rem; color: #94A3B8; margin-top: .2rem; }
.prod-card-stock.low { color: #EF4444; }
.no-results { text-align: center; padding: 2rem; color: #94A3B8; font-size: .85rem; }

/* Panel derecho — carrito */
.cart-panel { background: #fff; border-radius: 12px; border: 1px solid #D0DCE8; display: flex; flex-direction: column; }
.cart-header { background: #111; color: #fff; padding: .75rem 1rem; border-radius: 12px 12px 0 0; font-size: .85rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
.cart-body { flex: 1; padding: .75rem; overflow-y: auto; max-height: 280px; }
.cart-empty { text-align: center; padding: 2rem 1rem; color: #94A3B8; font-size: .82rem; }
.cart-item { display: flex; align-items: center; gap: .5rem; padding: .5rem 0; border-bottom: 1px solid #F1F5F9; }
.cart-item:last-child { border: none; }
.cart-item-name { flex: 1; font-size: .8rem; font-weight: 500; color: #1A1A2E; }
.cart-item-qty { display: flex; align-items: center; gap: 4px; }
.qty-btn { width: 22px; height: 22px; border: 1px solid #D0DCE8; border-radius: 4px; background: #F8FAFC; cursor: pointer; font-size: .85rem; display: flex; align-items: center; justify-content: center; color: #475569; }
.qty-btn:hover { background: #EFF6FF; border-color: #1E6FBB; color: #1E6FBB; }
.qty-num { width: 24px; text-align: center; font-size: .82rem; font-weight: 600; }
.cart-item-price { font-size: .82rem; font-weight: 600; color: #1E6FBB; min-width: 60px; text-align: right; }
.cart-remove { background: none; border: none; color: #CBD5E1; cursor: pointer; font-size: 1rem; padding: 0 2px; }
.cart-remove:hover { color: #EF4444; }

/* Pago */
.pay-section { padding: .75rem; border-top: 1px solid #E2ECF5; }
.pay-label { font-size: .75rem; font-weight: 600; color: #475569; margin-bottom: .4rem; }
.pay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .35rem; margin-bottom: .75rem; }
.pay-opt { padding: .45rem .5rem; border: 2px solid #D0DCE8; border-radius: 7px; font-size: .72rem; cursor: pointer; text-align: center; color: #475569; background: #F8FAFC; transition: all .1s; }
.pay-opt:hover { border-color: #1E6FBB; }
.pay-opt.selected { border-color: #111; background: #111; color: #fff; font-weight: 600; }

/* Total */
.total-section { padding: .75rem; border-top: 1px solid #E2ECF5; }
.total-row { display: flex; justify-content: space-between; font-size: .78rem; color: #64748B; margin-bottom: .3rem; }
.total-final { display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; color: #1A1A2E; padding-top: .5rem; margin-top: .3rem; border-top: 2px solid #111; }
.btn-registrar { width: 100%; padding: .75rem; background: #111; color: #fff; border: none; border-radius: 8px; font-size: .9rem; font-weight: 600; cursor: pointer; margin-top: .5rem; transition: background .15s; }
.btn-registrar:hover { background: #333; }
.btn-registrar:disabled { background: #CBD5E1; cursor: not-allowed; }

/* Éxito */
.venta-exitosa { text-align: center; padding: 2rem 1rem; }
.venta-exitosa .check { font-size: 3rem; margin-bottom: 1rem; }
.venta-exitosa h3 { font-size: 1.1rem; font-weight: 700; color: #166534; margin-bottom: .5rem; }
.venta-exitosa p { font-size: .85rem; color: #64748B; margin-bottom: 1rem; }
</style>

@if($ventaExitosa)
{{-- Pantalla de éxito --}}
<div class="bs-card" style="max-width:480px;margin:2rem auto">
    <div class="venta-exitosa">
        <div class="check">✅</div>
        <h3>¡Venta registrada!</h3>
        <p>Venta #{{ $ultimaVentaId }} guardada correctamente.</p>
        <p style="font-size:1.1rem;font-weight:700;color:#1E6FBB">${{ number_format($this->total ?? 0, 0, ',', '.') }}</p>
        <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1rem;flex-wrap:wrap">
            <button wire:click="nuevaVenta" class="bs-btn-primary">+ Nueva venta</button>
            <a href="{{ route('ventas') }}" class="bs-btn-secondary">Ver historial</a>
        </div>
    </div>
</div>
@else
<div class="pos-wrap">

    {{-- PANEL IZQUIERDO: productos --}}
    <div>
        <div class="bs-page-title">
            <span>🛒 Punto de venta</span>
        </div>

        <div class="pos-search">
            <input
                class="bs-input"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Buscar producto..."
                autocomplete="off"
            />
            <select class="bs-select" wire:model.live="categoriaFiltro" style="width:auto">
                <option value="">Todas las categorías</option>
                @foreach($this->categorias as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                @endforeach
            </select>
        </div>

        @if(strlen($busqueda) < 1 && !$categoriaFiltro)
            <div class="no-results">
                <div style="font-size:2rem;margin-bottom:.5rem">🔍</div>
                Escribí el nombre de un producto o elegí una categoría para empezar
            </div>
        @elseif($this->productos->isEmpty())
            <div class="no-results">
                <div style="font-size:2rem;margin-bottom:.5rem">📦</div>
                No se encontraron productos con stock disponible
            </div>
        @else
            <div class="prod-grid">
                @foreach($this->productos as $producto)
                <div class="prod-card" wire:click="agregarAlCarrito({{ $producto->id }})" wire:key="prod-{{ $producto->id }}">
                    <div class="prod-card-nombre">{{ $producto->nombre }}</div>
                    <div class="prod-card-cat">{{ $producto->categoria->nombre ?? '' }}</div>
                    <div class="prod-card-precio">${{ number_format($producto->precio_efectivo, 0, ',', '.') }}</div>
                    <div class="prod-card-stock {{ $producto->stock_actual <= $producto->stock_minimo ? 'low' : '' }}">
                        Stock: {{ $producto->stock_actual }} u.
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- PANEL DERECHO: carrito --}}
    <div>
        <div class="cart-panel">
            <div class="cart-header">
                <span>Carrito</span>
                @if(!empty($carrito))
                    <button wire:click="vaciarCarrito" style="background:none;border:none;color:#aaa;font-size:.75rem;cursor:pointer">Vaciar</button>
                @endif
            </div>

            <div class="cart-body">
                @if(empty($carrito))
                    <div class="cart-empty">
                        <div style="font-size:1.5rem;margin-bottom:.5rem">🛍️</div>
                        Tocá un producto para agregarlo
                    </div>
                @else
                    @foreach($carrito as $key => $item)
                    <div class="cart-item" wire:key="cart-{{ $key }}">
                        <div class="cart-item-name">{{ $item['nombre'] }}</div>
                        <div class="cart-item-qty">
                            <button class="qty-btn" wire:click="cambiarCantidad('{{ $key }}', -1)">−</button>
                            <span class="qty-num">{{ $item['cantidad'] }}</span>
                            <button class="qty-btn" wire:click="cambiarCantidad('{{ $key }}', 1)">+</button>
                        </div>
                        <div class="cart-item-price">${{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}</div>
                        <button class="cart-remove" wire:click="quitarDelCarrito('{{ $key }}')">✕</button>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Medio de pago --}}
            <div class="pay-section">
                <div class="pay-label">Medio de pago</div>
                <div class="pay-grid">
                    <div class="pay-opt {{ $medioPago === 'efectivo' ? 'selected' : '' }}" wire:click="$set('medioPago', 'efectivo')">
                        💵 Efectivo / MP
                    </div>
                    <div class="pay-opt {{ $medioPago === 'transferencia' ? 'selected' : '' }}" wire:click="$set('medioPago', 'transferencia')">
                        📲 Transferencia
                    </div>
                    <div class="pay-opt {{ $medioPago === 'tarjeta' ? 'selected' : '' }}" wire:click="$set('medioPago', 'tarjeta')">
                        💳 Tarjeta +{{ $this->config?->recargo_tarjeta ?? 15 }}%
                    </div>
                    <div class="pay-opt {{ $medioPago === 'cuotas_4' ? 'selected' : '' }}" wire:click="$set('medioPago', 'cuotas_4')">
                        🏦 BLP 4c +{{ $this->config?->cuotas_4_recargo ?? 2 }}%
                    </div>
                    <div class="pay-opt {{ $medioPago === 'cuotas_20' ? 'selected' : '' }}" wire:click="$set('medioPago', 'cuotas_20')" style="grid-column: span 2">
                        🏦 BLP 20 cuotas +{{ $this->config?->cuotas_20_recargo ?? 20 }}%
                    </div>
                </div>

                <input
                    class="bs-input"
                    wire:model="notas"
                    placeholder="Notas opcionales..."
                    style="font-size:.8rem;margin-bottom:.5rem"
                />
            </div>

            {{-- Total --}}
            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>${{ number_format($this->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($this->recargoMonto > 0)
                <div class="total-row" style="color:#92400E">
                    <span>Recargo ({{ $this->labelMedioPago }})</span>
                    <span>+${{ number_format($this->recargoMonto, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="total-final">
                    <span>Total</span>
                    <span>${{ number_format($this->total, 0, ',', '.') }}</span>
                </div>

                <button
                    class="btn-registrar"
                    wire:click="registrarVenta"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50"
                    @if(empty($carrito)) disabled @endif
                >
                    <span wire:loading.remove>✓ Registrar venta</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
