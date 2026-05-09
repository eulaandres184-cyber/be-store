@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>{{ $modoEdicion ? '✏️ Editar equipo' : '📱 Nuevo equipo' }}</span>
    <a href="{{ route('equipos') }}" class="bs-btn-secondary">← Volver</a>
</div>

{{-- Preview precio ARS --}}
@if($precio_usd > 0)
<div class="bs-dolar-banner" style="margin-bottom:1rem">
    <div>
        <div class="bs-dolar-label">Precio en pesos (dólar blue ${{ number_format($this->dolar,0,',','.') }})</div>
        <div class="bs-dolar-val">${{ number_format($this->precioArs,0,',','.') }}</div>
    </div>
    <div style="text-align:right">
        <div style="color:#aaa;font-size:.75rem">Precio USD ingresado</div>
        <div style="color:#fff;font-size:1.2rem;font-weight:700">u$s {{ number_format((float)$precio_usd,0,',','.') }}</div>
    </div>
</div>
@endif

<div class="form-producto">

    {{-- IMEI --}}
    <div class="form-section">
        <div class="form-section-title">🔑 Identificación del equipo</div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">IMEI *</label>
                <div class="codigo-field">
                    <input class="bs-input" wire:model.blur="imei" placeholder="15 dígitos" style="font-family:var(--bs-font-mono)" maxlength="20"/>
                    <button type="button" class="codigo-scan-btn" title="Escanear IMEI" onclick="alert('Marcá *#06# en el celular para ver el IMEI')">📷</button>
                </div>
                @error('imei')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Marcá *#06# en el celular para obtener el IMEI</div>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Estado</label>
                <select class="bs-select" wire:model="estado">
                    <option value="disponible">Disponible</option>
                    <option value="reservado">Reservado</option>
                    <option value="vendido">Vendido</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Características --}}
    <div class="form-section">
        <div class="form-section-title">📋 Características del equipo</div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Marca *</label>
                <select class="bs-select" wire:model.live="marca">
                    <option value="">Seleccioná marca</option>
                    @foreach($marcasComunes as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
                @if($marca === 'Otro')
                    <input class="bs-input" wire:model.live="marca" placeholder="Ingresá la marca" style="margin-top:.4rem"/>
                @endif
                @error('marca')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Modelo *</label>
                <input class="bs-input" wire:model.live="modelo" placeholder="Ej: 15 Pro Max, S24 Ultra"/>
                @error('modelo')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Capacidad (GB)</label>
                <select class="bs-select" wire:model.live="capacidad_gb">
                    <option value="">Sin especificar</option>
                    @foreach([32,64,128,256,512,1024] as $gb)
                        <option value="{{ $gb }}">{{ $gb }}GB</option>
                    @endforeach
                </select>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Color</label>
                <select class="bs-select" wire:model.live="color">
                    <option value="">Sin especificar</option>
                    @foreach($coloresComunes as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Condición *</label>
                <select class="bs-select" wire:model="condicion">
                    <option value="nuevo">Nuevo</option>
                    <option value="usado">Usado</option>
                    <option value="reacondicionado">Reacondicionado</option>
                </select>
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Batería (%)</label>
                <input class="bs-input" wire:model="bateria_pct" type="number" min="0" max="100" placeholder="Ej: 87"/>
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Solo para equipos usados</div>
            </div>
        </div>
    </div>

    {{-- Precio --}}
    <div class="form-section">
        <div class="form-section-title">💰 Precio</div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Precio en dólares (USD) *</label>
                <input class="bs-input" wire:model.live="precio_usd" type="number" min="0" step="0.01" placeholder="0" style="font-size:1.2rem;font-weight:700"/>
                @error('precio_usd')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Equivalente en ARS</label>
                <div class="bs-input" style="font-size:1.1rem;font-weight:700;color:var(--bs-blue);background:#F0F7FF">
                    ${{ number_format($this->precioArs, 0, ',', '.') }}
                </div>
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Calculado con dólar blue del día</div>
            </div>
        </div>
    </div>

    {{-- Datos del producto --}}
    <div class="form-section">
        <div class="form-section-title">📦 Datos en el sistema</div>
        <div class="bs-form-group">
            <label class="bs-label">Nombre en el sistema *</label>
            <input class="bs-input" wire:model="nombre_producto" placeholder="Se genera automáticamente"/>
            @error('nombre_producto')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Se completa solo al ingresar marca/modelo/capacidad/color</div>
        </div>
        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Categoría *</label>
                <select class="bs-select" wire:model="categoria_id">
                    <option value="">Seleccioná categoría</option>
                    @foreach($this->categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Código de barras (caja)</label>
                <input class="bs-input" wire:model="codigo_barras" placeholder="Opcional" style="font-family:var(--bs-font-mono)"/>
            </div>
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Notas / descripción</label>
            <textarea class="bs-input" wire:model="descripcion" rows="2" placeholder="Estado del equipo, accesorios incluidos, observaciones..."></textarea>
        </div>
    </div>

    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <button wire:click="guardar" wire:loading.attr="disabled" class="bs-btn-black" style="padding:.65rem 1.5rem;font-size:.9rem">
            <span wire:loading.remove wire:target="guardar">{{ $modoEdicion ? '💾 Guardar cambios' : '✅ Registrar equipo' }}</span>
            <span wire:loading wire:target="guardar">Guardando...</span>
        </button>
        <a href="{{ route('equipos') }}" class="bs-btn-secondary">Cancelar</a>
    </div>
</div>
</div>
