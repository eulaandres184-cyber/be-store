@push('estilos')
    @vite(['resources/css/productos.css'])
@endpush
@push('scripts')
    @vite(['resources/js/productos.js'])
@endpush

@if(session('warning'))
    <div class="bs-alert-warning">⚠️ {{ session('warning') }}</div>
@endif

<div class="bs-page-title">
    <span>{{ $modoEdicion ? '✏️ Editar producto' : '➕ Nuevo producto' }}</span>
    <a href="{{ route('productos') }}" class="bs-btn-secondary">← Volver</a>
</div>

<div class="form-producto">

    {{-- SECCIÓN: Identificación --}}
    <div class="form-section">
        <div class="form-section-title">🔖 Identificación</div>

        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Código interno</label>
                <input
                    class="bs-input"
                    wire:model="codigo_interno"
                    placeholder="BE-0001"
                    style="font-family:var(--bs-font-mono)"
                />
                @error('codigo_interno') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Generado automáticamente, podés modificarlo</div>
            </div>

            <div class="bs-form-group">
                <label class="bs-label">Código de barras</label>
                <div class="codigo-field">
                    <input
                        class="bs-input"
                        wire:model="codigo_barras"
                        placeholder="Escaneá o ingresá manualmente"
                        data-scanner="true"
                        style="font-family:var(--bs-font-mono)"
                    />
                    <button
                        type="button"
                        class="codigo-scan-btn"
                        wire:click="$dispatch('abrirScanner')"
                        title="Escanear con cámara"
                    >📷</button>
                </div>
                @error('codigo_barras') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Pistola USB: apuntá al código y escaneá</div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: Datos principales --}}
    <div class="form-section">
        <div class="form-section-title">📋 Datos del producto</div>

        <div class="bs-form-group">
            <label class="bs-label">Nombre del producto *</label>
            <input class="bs-input" wire:model="nombre" placeholder="Ej: Funda Transparente Borde Color MagSafe" />
            @error('nombre') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
        </div>

        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Categoría *</label>
                <select class="bs-select" wire:model="categoria_id">
                    <option value="">Seleccioná una categoría</option>
                    @foreach($this->categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
            </div>

            <div class="bs-form-group">
                <label class="bs-label">Moneda</label>
                <select class="bs-select" wire:model="moneda">
                    <option value="ARS">Pesos (ARS) — Accesorios</option>
                    <option value="USD">Dólares (USD) — Equipos</option>
                </select>
            </div>
        </div>

        <div class="bs-form-group">
            <label class="bs-label">Descripción (opcional)</label>
            <textarea class="bs-input" wire:model="descripcion" rows="2" placeholder="Características, colores disponibles, compatibilidad..."></textarea>
        </div>
    </div>

    {{-- SECCIÓN: Precios --}}
    <div class="form-section">
        <div class="form-section-title">💰 Precio</div>

        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Precio en efectivo / transferencia *</label>
                <input
                    class="bs-input"
                    wire:model.live="precio_efectivo"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    style="font-size:1.1rem;font-weight:600"
                />
                @error('precio_efectivo') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Preview de precios con recargos --}}
        @if($precio_efectivo > 0)
        <div class="precio-preview">
            <div class="precio-preview-title">Vista previa de precios según medio de pago</div>
            <div class="precio-preview-grid">
                <div class="precio-preview-item">
                    <div class="precio-preview-label">Efectivo / MP</div>
                    <div class="precio-preview-val">${{ number_format((float)$precio_efectivo, 0, ',', '.') }}</div>
                </div>
                <div class="precio-preview-item">
                    <div class="precio-preview-label">Tarjeta (+15%)</div>
                    <div class="precio-preview-val">${{ number_format((float)$precio_efectivo * 1.15, 0, ',', '.') }}</div>
                </div>
                <div class="precio-preview-item">
                    <div class="precio-preview-label">BLP 4 cuotas (+2%)</div>
                    <div class="precio-preview-val">${{ number_format((float)$precio_efectivo * 1.02, 0, ',', '.') }}</div>
                </div>
                <div class="precio-preview-item">
                    <div class="precio-preview-label">BLP 20 cuotas (+20%)</div>
                    <div class="precio-preview-val">${{ number_format((float)$precio_efectivo * 1.20, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- SECCIÓN: Stock --}}
    <div class="form-section">
        <div class="form-section-title">📊 Control de stock</div>

        <div class="bs-form-row">
            <div class="bs-form-group">
                <label class="bs-label">Stock actual</label>
                <input class="bs-input" wire:model="stock_actual" type="number" min="0" />
                @error('stock_actual') <span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span> @enderror
            </div>
            <div class="bs-form-group">
                <label class="bs-label">Stock mínimo (alerta)</label>
                <input class="bs-input" wire:model="stock_minimo" type="number" min="0" />
                <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.2rem">Se mostrará alerta cuando llegue a este número</div>
            </div>
        </div>

        <div class="bs-form-group" style="display:flex;align-items:center;gap:.5rem">
            <input type="checkbox" wire:model="activo" id="activo" style="width:16px;height:16px;accent-color:var(--bs-blue)">
            <label for="activo" class="bs-label" style="margin:0;cursor:pointer">Producto activo (visible en el punto de venta)</label>
        </div>
    </div>

    {{-- Botones --}}
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <button
            wire:click="guardar"
            wire:loading.attr="disabled"
            class="bs-btn-black"
            style="padding:.65rem 1.5rem;font-size:.9rem"
        >
            <span wire:loading.remove wire:target="guardar">
                {{ $modoEdicion ? '💾 Guardar cambios' : '✅ Crear producto' }}
            </span>
            <span wire:loading wire:target="guardar">Guardando...</span>
        </button>
        <a href="{{ route('productos') }}" class="bs-btn-secondary">Cancelar</a>
    </div>
</div>

{{-- Modal scanner de cámara --}}
<div id="scanner-modal" class="scanner-overlay" style="display:none">
    <div class="scanner-box">
        <h3>📷 Escanear código de barras</h3>
        <video id="scanner-video" playsinline></video>
        <div id="scanner-resultado" class="scanner-resultado"></div>
        <div class="scanner-btns">
            <button
                type="button"
                class="bs-btn-secondary w-full"
                onclick="BeScanner.detener(); document.getElementById('scanner-modal').style.display='none'"
            >
                Cancelar
            </button>
        </div>
        <div style="font-size:.72rem;color:var(--bs-muted);text-align:center;margin-top:.5rem">
            Apuntá la cámara al código de barras del producto
        </div>
    </div>
</div>


