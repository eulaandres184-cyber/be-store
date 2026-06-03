@push('estilos')@vite(['resources/css/productos.css'])@endpush
<div>
<div class="bs-page-title"><span>⚙️ Configuración del sistema</span></div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">

    {{-- Recargos --}}
    <div class="form-section">
        <div class="form-section-title">💳 Recargos por medio de pago</div>
        @if(session('success_recargos'))<div class="bs-alert-success">✅ {{ session('success_recargos') }}</div>@endif

        <div class="bs-form-group">
            <label class="bs-label">Recargo tarjeta de crédito/débito (%)</label>
            <input class="bs-input" wire:model="recargo_tarjeta" type="number" step="0.1" min="0" max="100"/>
            @error('recargo_tarjeta')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Banco La Pampa — 4 cuotas (%)</label>
            <input class="bs-input" wire:model="cuotas_4_recargo" type="number" step="0.1" min="0" max="100"/>
            @error('cuotas_4_recargo')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>
        <div class="bs-form-group">
            <label class="bs-label">Banco La Pampa — 20 cuotas (%)</label>
            <input class="bs-input" wire:model="cuotas_20_recargo" type="number" step="0.1" min="0" max="100"/>
            @error('cuotas_20_recargo')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
        </div>

        <div style="background:var(--bs-blue-light);border-radius:var(--bs-radius-md);padding:.75rem;margin-bottom:.75rem;font-size:.78rem">
            <div style="font-weight:600;margin-bottom:.4rem;color:var(--bs-dark)">Preview para $10.000</div>
            <div style="display:flex;flex-direction:column;gap:.2rem;color:var(--bs-text)">
                <span>Efectivo / MP: <strong>$10.000</strong></span>
                <span>Tarjeta: <strong>${{ number_format(10000 * (1 + (float)($recargo_tarjeta ?? 15) / 100), 0, ',', '.') }}</strong></span>
                <span>BLP 4c: <strong>${{ number_format(10000 * (1 + (float)($cuotas_4_recargo ?? 2) / 100), 0, ',', '.') }}</strong></span>
                <span>BLP 20c: <strong>${{ number_format(10000 * (1 + (float)($cuotas_20_recargo ?? 20) / 100), 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <button wire:click="guardarRecargos" wire:loading.attr="disabled" class="bs-btn-black" style="width:100%">
            <span wire:loading.remove wire:target="guardarRecargos">💾 Guardar recargos</span>
            <span wire:loading wire:target="guardarRecargos">Guardando...</span>
        </button>
    </div>

    {{-- Dólar blue --}}
    <div>
        <div class="form-section" style="margin-bottom:1rem">
            <div class="form-section-title">💵 Dólar blue</div>
            @if(session('success_dolar'))<div class="bs-alert-success">✅ {{ session('success_dolar') }}</div>@endif
            @if(session('error_dolar'))<div class="bs-alert-danger">❌ {{ session('error_dolar') }}</div>@endif

            <div class="bs-dolar-banner" style="margin-bottom:.75rem">
                <div>
                    <div class="bs-dolar-label">Valor actual</div>
                    <div class="bs-dolar-val">${{ number_format((float)$dolar_blue_hoy, 2, ',', '.') }}</div>
                    <div class="bs-dolar-time">{{ $dolarActualizado }}</div>
                </div>
            </div>

            <div class="bs-form-group">
                <label class="bs-label">Valor manual (venta)</label>
                <input class="bs-input" wire:model="dolar_blue_hoy" type="number" step="0.01" min="0" style="font-size:1.1rem;font-weight:600"/>
                @error('dolar_blue_hoy')<span style="font-size:.75rem;color:var(--bs-danger-text)">{{ $message }}</span>@enderror
            </div>

            <div style="display:flex;gap:.5rem">
                <button wire:click="guardarDolar" wire:loading.attr="disabled" class="bs-btn-black" style="flex:1">
                    <span wire:loading.remove wire:target="guardarDolar">💾 Guardar</span>
                    <span wire:loading wire:target="guardarDolar">Guardando...</span>
                </button>
                <button wire:click="sincronizarDolar" wire:loading.attr="disabled" class="bs-btn-primary" style="flex:1">
                    <span wire:loading.remove wire:target="sincronizarDolar">🔄 Sync API</span>
                    <span wire:loading wire:target="sincronizarDolar">Sincronizando...</span>
                </button>
            </div>
            <div style="font-size:.7rem;color:var(--bs-muted);margin-top:.4rem;text-align:center">Fuente: bluelytics.com.ar</div>
        </div>

        {{-- Historial dólar --}}
        <div class="bs-card" style="padding:0;overflow:hidden">
            <div class="bs-card-title" style="padding:.75rem 1rem;margin:0">Historial reciente</div>
            <table class="bs-table">
                <thead><tr><th>Fecha</th><th>Compra</th><th>Venta</th><th>Fuente</th></tr></thead>
                <tbody>
                @forelse($this->historial as $h)
                <tr>
                    <td>{{ $h->fecha->format('d/m/Y') }}</td>
                    <td>${{ number_format($h->valor_compra,2,',','.') }}</td>
                    <td style="font-weight:600;color:var(--bs-blue)">${{ number_format($h->valor_venta,2,',','.') }}</td>
                    <td><span class="bs-badge-{{ $h->fuente==='bluelytics'?'green':'blue' }}">{{ $h->fuente }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:1rem;color:var(--bs-muted)">Sin historial</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
