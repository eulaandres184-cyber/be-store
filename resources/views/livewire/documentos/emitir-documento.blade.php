@push('estilos')@vite(['resources/css/documentos.css'])@endpush
<div>
@if($documentoId)
    {{-- Documento generado: mostrar opciones --}}
    <div class="bs-page-title">
        <span>✅ Documento emitido</span>
        <a href="{{ route('documentos') }}" class="bs-btn-secondary">Ver todos</a>
    </div>
    <div style="max-width:680px;margin:0 auto">
        <div class="doc-acciones" style="margin-bottom:1rem">
            <a href="{{ route('documentos.ver', $documentoId) }}" target="_blank" class="bs-btn-black">
                🖨️ Imprimir / PDF
            </a>
            <a href="{{ route('documentos.whatsapp', $documentoId) }}" target="_blank" class="bs-btn-secondary" style="background:#25D366;color:#fff;border-color:#25D366">
                📱 Compartir por WhatsApp
            </a>
            <a href="{{ route('pos') }}" class="bs-btn-secondary">+ Nueva venta</a>
        </div>
        @include('livewire.documentos.partials.vista-documento', ['documentoId' => $documentoId])
    </div>
@else
    <div class="bs-page-title">
        <span>📄 Emitir documento</span>
        <a href="{{ route('ventas') }}" class="bs-btn-secondary">← Volver</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;max-width:900px">
        {{-- Panel izquierdo: selector --}}
        <div>
            <div class="bs-card">
                <div class="bs-card-title">Tipo de documento</div>
                <div class="doc-tipo-grid">
                    <div class="doc-tipo-card {{ $tipo==='ticket'?'selected':'' }}" wire:click="$set('tipo','ticket')">
                        <div class="doc-tipo-icon">🧾</div>
                        <div class="doc-tipo-label">Ticket</div>
                        <div class="doc-tipo-sub">No válido como factura</div>
                    </div>
                    <div class="doc-tipo-card {{ $tipo==='factura'?'selected':'' }}" wire:click="$set('tipo','factura')">
                        <div class="doc-tipo-icon">📋</div>
                        <div class="doc-tipo-label">Factura C</div>
                        <div class="doc-tipo-sub">Monotributista</div>
                    </div>
                    <div class="doc-tipo-card {{ $tipo==='recibo'?'selected':'' }}" wire:click="$set('tipo','recibo')">
                        <div class="doc-tipo-icon">💰</div>
                        <div class="doc-tipo-label">Recibo de pago</div>
                        <div class="doc-tipo-sub">Múltiples métodos</div>
                    </div>
                </div>
            </div>

            {{-- Datos adicionales según tipo --}}
            @if($tipo === 'factura')
            <div class="bs-card">
                <div class="bs-card-title">Datos del cliente (factura)</div>
                <div class="bs-form-group">
                    <label class="bs-label">Nombre / Razón social *</label>
                    <input class="bs-input" wire:model="clienteNombre" placeholder="Consumidor Final"/>
                </div>
                <div class="bs-form-row">
                    <div class="bs-form-group">
                        <label class="bs-label">DNI / CUIT</label>
                        <input class="bs-input" wire:model="clienteDni" placeholder="00.000.000"/>
                    </div>
                    <div class="bs-form-group">
                        <label class="bs-label">Domicilio</label>
                        <input class="bs-input" wire:model="clienteDomicilio" placeholder="Opcional"/>
                    </div>
                </div>
            </div>
            @endif

            @if($tipo === 'recibo')
            <div class="bs-card">
                <div class="bs-card-title">Detalle de pagos recibidos</div>
                <div class="bs-form-row" style="margin-bottom:.5rem">
                    <div class="bs-form-group">
                        <label class="bs-label">Método</label>
                        <select class="bs-select" wire:model="pagoMetodo">
                            <option value="efectivo">Efectivo ARS</option>
                            <option value="efectivo_usd">Efectivo USD</option>
                            <option value="transferencia">Transferencia / MP</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="cheque">E-Cheque</option>
                        </select>
                    </div>
                    <div class="bs-form-group">
                        <label class="bs-label">Monto</label>
                        <input class="bs-input" wire:model="pagoMonto" type="number" min="0" placeholder="0"/>
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;margin-bottom:.75rem">
                    <select class="bs-select" wire:model="pagoMoneda" style="width:auto">
                        <option value="ARS">ARS</option>
                        <option value="USD">USD (× dólar blue)</option>
                    </select>
                    <button wire:click="agregarPago" class="bs-btn-black" style="white-space:nowrap">+ Agregar</button>
                </div>

                @foreach($pagos as $i => $pago)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #EEF2F5;font-size:.82rem">
                    <span>{{ ucfirst(str_replace('_',' ',$pago['metodo'])) }} ({{ $pago['moneda'] }})</span>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <span style="font-weight:600">${{ number_format($pago['monto'],0,',','.') }}</span>
                        <button wire:click="quitarPago({{ $i }})" style="background:none;border:none;color:var(--bs-danger-text);cursor:pointer">✕</button>
                    </div>
                </div>
                @endforeach

                @if(!empty($pagos))
                <div style="display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;padding:.5rem 0;border-top:2px solid var(--bs-dark);margin-top:.5rem">
                    <span>Total pagado</span>
                    <span style="color:var(--bs-blue)">${{ number_format($this->totalPagado,0,',','.') }}</span>
                </div>
                @if($this->saldo > 0)
                <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#9A6B00;padding:.3rem 0">
                    <span>⚠ Saldo pendiente</span>
                    <span>${{ number_format($this->saldo,0,',','.') }}</span>
                </div>
                @endif
                @endif

                <div class="bs-form-group" style="margin-top:.75rem">
                    <label class="bs-label">Observaciones</label>
                    <textarea class="bs-input" wire:model="observaciones" rows="2" placeholder="Ej: Saldo a cancelar el 15/06..."></textarea>
                </div>
            </div>
            @endif

            <button wire:click="emitir" wire:loading.attr="disabled"
                class="bs-btn-black" style="width:100%;padding:.75rem;font-size:.95rem">
                <span wire:loading.remove>📄 Emitir {{ $tipo === 'ticket'?'ticket':($tipo==='factura'?'factura C':'recibo') }}</span>
                <span wire:loading>Generando...</span>
            </button>
        </div>

        {{-- Panel derecho: resumen de la venta --}}
        <div>
            <div class="bs-card">
                <div class="bs-card-title">Resumen de la venta #{{ $this->venta->id }}</div>
                <div style="font-size:.78rem;color:var(--bs-muted);margin-bottom:.75rem">
                    {{ $this->venta->fecha->format('d/m/Y H:i') }} —
                    <span class="bs-badge-{{ in_array($this->venta->medio_pago,['efectivo','transferencia'])?'green':'amber' }}">
                        {{ $this->venta->label_medio_pago }}
                    </span>
                </div>
                @foreach($this->venta->items as $item)
                <div style="display:flex;justify-content:space-between;font-size:.82rem;padding:.3rem 0;border-bottom:1px solid #EEF2F5">
                    <span>{{ $item->producto?->nombre }} × {{ $item->cantidad }}</span>
                    <span style="font-weight:600">${{ number_format($item->subtotal_ars,0,',','.') }}</span>
                </div>
                @endforeach
                <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:700;color:var(--bs-blue);padding:.6rem 0;border-top:2px solid var(--bs-dark);margin-top:.5rem">
                    <span>Total</span>
                    <span>${{ number_format($this->venta->total_ars,0,',','.') }}</span>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
