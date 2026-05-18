@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
@if(session('success'))<div class="bs-alert-success">✅ {{ session('success') }}</div>@endif

<div class="bs-page-title">
    <span>🔄 Devoluciones y cambios</span>
    <button wire:click="$set('mostrarForm',true)" class="bs-btn-black">+ Nueva devolución</button>
</div>

@if($mostrarForm)
<div class="form-section" style="max-width:680px;margin-bottom:1rem">
    <div class="form-section-title">🔄 Registrar devolución / cambio</div>

    {{-- Buscar venta --}}
    <div class="bs-form-group" style="position:relative">
        <label class="bs-label">Venta de referencia *</label>
        @if($ventaId)
            <div style="background:var(--bs-blue-light);border:1px solid var(--bs-blue-mid);border-radius:var(--bs-radius-md);padding:.5rem .75rem;font-size:.82rem;display:flex;justify-content:space-between;align-items:center">
                <span>{{ $ventaInfo }}</span>
                <button wire:click="$set('ventaId',null)" style="background:none;border:none;color:var(--bs-muted);cursor:pointer">✕</button>
            </div>
        @else
            <input class="bs-input" wire:model.live.debounce.300ms="busquedaVenta"
                   placeholder="Buscá por número de venta o nombre de cliente..."/>
            @if($this->ventasBuscadas->isNotEmpty())
            <div style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--bs-gray-border);border-radius:0 0 var(--bs-radius-md) var(--bs-radius-md);z-index:20;box-shadow:var(--bs-shadow-md)">
                @foreach($this->ventasBuscadas as $v)
                <div wire:click="seleccionarVenta({{ $v->id }})"
                     style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #F1F5F9"
                     onmouseover="this.style.background='#EEF6FF'" onmouseout="this.style.background=''">
                    <strong>#{{ $v->id }}</strong> — {{ $v->fecha->format('d/m/Y') }} —
                    ${{ number_format($v->total_ars,0,',','.') }}
                    @if($v->cliente) — {{ $v->cliente->nombre }}@endif
                </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>

    @if($ventaId && !empty($itemsVenta))
    {{-- Seleccionar ítem devuelto --}}
    <div class="bs-form-group">
        <label class="bs-label">Producto devuelto *</label>
        <select class="bs-select" wire:model.live="itemSeleccionado">
            @foreach($itemsVenta as $i => $item)
            <option value="{{ $i }}">{{ $item['nombre'] }} × {{ $item['cantidad'] }} — ${{ number_format($item['precio'],0,',','.') }}</option>
            @endforeach
        </select>
    </div>

    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Tipo de devolución *</label>
            <select class="bs-select" wire:model.live="tipo">
                <option value="falla">🔧 Falla del producto</option>
                <option value="cambio">🔄 Cambio por otro producto</option>
                <option value="devolucion_dinero">💵 Devolución de dinero</option>
            </select>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Cantidad</label>
            <input class="bs-input" wire:model="cantidad" type="number" min="1"/>
        </div>
    </div>

    <div class="bs-form-group">
        <label class="bs-label">Motivo *</label>
        <textarea class="bs-input" wire:model="motivo" rows="2"
                  placeholder="Describí el motivo de la devolución..."></textarea>
        @error('motivo')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
    </div>

    {{-- Si es cambio: buscar producto nuevo --}}
    @if($tipo === 'cambio')
    <div class="bs-form-group" style="position:relative">
        <label class="bs-label">Producto de reemplazo</label>
        @if($productoNuevoId)
            <div style="background:var(--bs-success-bg);border:1px solid var(--bs-success-text);border-radius:var(--bs-radius-md);padding:.5rem .75rem;font-size:.82rem;display:flex;justify-content:space-between">
                <span>{{ $productoNuevoNombre }}</span>
                <button wire:click="$set('productoNuevoId',null)" style="background:none;border:none;color:var(--bs-muted);cursor:pointer">✕</button>
            </div>
        @else
            <input class="bs-input" wire:model.live.debounce.300ms="busquedaProductoNuevo"
                   placeholder="Buscar producto de reemplazo..."/>
            @if($this->productosBuscados->isNotEmpty())
            <div style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--bs-gray-border);border-radius:0 0 var(--bs-radius-md) var(--bs-radius-md);z-index:20;box-shadow:var(--bs-shadow-md)">
                @foreach($this->productosBuscados as $prod)
                <div wire:click="seleccionarProductoNuevo({{ $prod->id }},'{{ addslashes($prod->nombre) }}')"
                     style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #F1F5F9;display:flex;justify-content:space-between"
                     onmouseover="this.style.background='#EEF6FF'" onmouseout="this.style.background=''">
                    <span>{{ $prod->nombre }}</span>
                    <span style="color:var(--bs-blue);font-weight:600">${{ number_format($prod->precio_efectivo,0,',','.') }} — Stock: {{ $prod->stock_actual }}</span>
                </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>
    @endif

    {{-- Si es devolución de dinero --}}
    @if($tipo === 'devolucion_dinero')
    <div class="bs-form-row">
        <div class="bs-form-group">
            <label class="bs-label">Monto a devolver</label>
            <input class="bs-input" wire:model="montoDevuelto" type="number" min="0"/>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Medio de devolución</label>
            <select class="bs-select" wire:model="medioDevolucion">
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia / MP</option>
            </select>
        </div>
    </div>
    @endif

    <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem">
        <input type="checkbox" wire:model="reponerStock" id="reponer"
               style="width:16px;height:16px;accent-color:var(--bs-blue)">
        <label for="reponer" class="bs-label" style="margin:0;cursor:pointer">
            Reponer stock del producto devuelto automáticamente
        </label>
    </div>

    <div class="bs-form-group">
        <label class="bs-label">Observaciones adicionales</label>
        <input class="bs-input" wire:model="observaciones" placeholder="Opcional..."/>
    </div>

    <div style="display:flex;gap:.75rem">
        <button wire:click="registrar" wire:loading.attr="disabled" class="bs-btn-black" style="padding:.65rem 1.5rem">
            <span wire:loading.remove>✅ Registrar devolución</span>
            <span wire:loading>Guardando...</span>
        </button>
        <button wire:click="$set('mostrarForm',false)" class="bs-btn-secondary">Cancelar</button>
    </div>
    @endif
</div>
@endif

{{-- Lista de devoluciones --}}
<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Venta</th>
                <th>Cliente</th>
                <th>Motivo</th>
                <th>Monto dev.</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
        @forelse($devoluciones as $dev)
        <tr wire:key="dev-{{ $dev->id }}">
            <td style="font-size:.78rem">{{ $dev->created_at->format('d/m/Y H:i') }}</td>
            <td><span class="bs-badge-{{ $dev->tipo==='falla'?'red':($dev->tipo==='cambio'?'blue':'amber') }}">{{ $dev->label_tipo }}</span></td>
            <td><a href="{{ route('ventas') }}" style="color:var(--bs-blue)">#{{ $dev->venta_id }}</a></td>
            <td>{{ $dev->cliente?->nombre ?? '—' }}</td>
            <td style="font-size:.78rem">{{ Str::limit($dev->motivo,40) }}</td>
            <td>{{ $dev->monto_devuelto > 0 ? '$'.number_format($dev->monto_devuelto,0,',','.') : '—' }}</td>
            <td>
                @if($dev->stock_repuesto)
                    <span class="bs-badge-green">Repuesto</span>
                @else
                    <span class="bs-badge-red">No repuesto</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay devoluciones registradas</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $devoluciones->links() }}</div>
</div>
