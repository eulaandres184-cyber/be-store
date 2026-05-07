@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>🛒 Nueva compra</span>
    <a href="{{ route('compras') }}" class="bs-btn-secondary">← Volver</a>
</div>

<div class="form-producto">

    <div class="form-section">
        <div class="form-section-title">📋 Datos de la compra</div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Proveedor *</label>
                <select class="bs-select" wire:model="proveedor_id">
                    <option value="">Seleccioná proveedor</option>
                    @foreach($this->proveedores as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>
                @error('proveedor_id')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Fecha *</label>
                <input class="bs-input" wire:model="fecha" type="date"/>
            </div>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Notas (opcional)</label>
            <input class="bs-input" wire:model="notas" placeholder="Ej: pago contra entrega, factura N°..."/>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-title">📦 Agregar productos</div>

        <div style="position:relative;margin-bottom:.75rem">
            <input class="bs-input" wire:model.live.debounce.300ms="busquedaProducto" placeholder="Buscar producto por nombre o código..."/>
            @if($this->productosBuscados->isNotEmpty())
            <div style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--bs-gray-border);border-radius:0 0 var(--bs-radius-md) var(--bs-radius-md);z-index:20;box-shadow:var(--bs-shadow-md)">
                @foreach($this->productosBuscados as $prod)
                <div wire:click="seleccionarProducto({{ $prod->id }})"
                     style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #F1F5F9;display:flex;justify-content:space-between"
                     onmouseover="this.style.background='#EFF6FF'" onmouseout="this.style.background='#fff'">
                    <span>{{ $prod->nombre }}</span>
                    <span style="color:var(--bs-blue);font-weight:600">${{ number_format($prod->precio_efectivo,0,',','.') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @if($productoSelId)
        <div class="bs-form-row" style="margin-bottom:.75rem">
            <div class="bs-form-group">
                <label class="bs-label">Cantidad</label>
                <input class="bs-input" wire:model="cantidadItem" type="number" min="1"/>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Costo unitario</label>
                <input class="bs-input" wire:model="costoItem" type="number" min="0" step="0.01"/>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Moneda</label>
                <select class="bs-select" wire:model="monedaItem">
                    <option value="ARS">ARS</option>
                    <option value="USD">USD</option>
                </select>
            </div>
        </div>
        <button wire:click="agregarItem" class="bs-btn-black" style="margin-bottom:.75rem">+ Agregar al listado</button>
        @endif

        @error('items')<div class="bs-alert-warning">{{ $message }}</div>@enderror

        @if(!empty($items))
        <table class="bs-table" style="margin-top:.5rem">
            <thead><tr><th>Producto</th><th>Cantidad</th><th>Costo unit.</th><th>Subtotal</th><th></th></tr></thead>
            <tbody>
            @foreach($items as $key => $item)
            <tr wire:key="{{ $key }}">
                <td>{{ $item['nombre'] }}</td>
                <td>{{ $item['cantidad'] }}</td>
                <td>${{ number_format($item['costo'],0,',','.') }} {{ $item['moneda'] }}</td>
                <td style="font-weight:600;color:var(--bs-blue)">${{ number_format($item['costo']*$item['cantidad'],0,',','.') }}</td>
                <td><button wire:click="quitarItem('{{ $key }}')" class="btn-del">✕</button></td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div style="text-align:right;margin-top:.75rem;font-size:1.1rem;font-weight:700;color:var(--bs-dark)">
            Total: ${{ number_format($this->total,0,',','.') }}
        </div>
        @endif
    </div>

    <div style="display:flex;gap:.75rem">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black" style="padding:.65rem 1.5rem">
            <span wire:loading.remove wire:target="guardar">💾 Registrar compra</span>
            <span wire:loading wire:target="guardar">Guardando...</span>
        </button>
        <a href="{{ route('compras') }}" class="bs-btn-secondary">Cancelar</a>
    </div>
</div>
</div>
