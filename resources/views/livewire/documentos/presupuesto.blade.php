@push('estilos')@vite(['resources/css/documentos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>📊 Nuevo presupuesto</span>
    <a href="{{ route('documentos') }}" class="bs-btn-secondary">← Volver</a>
</div>

@if($documentoId)
<div style="max-width:680px;margin:0 auto">
    <div class="doc-acciones" style="margin-bottom:1rem">
        <a href="{{ route('documentos.ver', $documentoId) }}" target="_blank" class="bs-btn-black">🖨️ Imprimir / PDF</a>
        <a href="{{ route('documentos.whatsapp', $documentoId) }}" target="_blank" style="background:#25D366;color:#fff;border-color:#25D366" class="bs-btn-secondary">📱 WhatsApp</a>
        <button wire:click="$set('documentoId',null)" class="bs-btn-secondary">+ Nuevo</button>
    </div>
    @include('livewire.documentos.partials.vista-documento', ['documentoId' => $documentoId])
</div>
@else
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;max-width:900px">
    <div>
        <div class="bs-card">
            <div class="bs-card-title">
                💵 Dólar blue hoy: <span style="color:var(--bs-blue);font-weight:700">${{ number_format($this->dolar,2,',','.') }}</span>
            </div>
            <div style="position:relative;margin-bottom:.75rem">
                <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar equipo por marca o modelo..."/>
                @if($this->equiposBuscados->isNotEmpty())
                <div style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid var(--bs-gray-border);border-radius:0 0 var(--bs-radius-md) var(--bs-radius-md);z-index:20;box-shadow:var(--bs-shadow-md)">
                    @foreach($this->equiposBuscados as $eq)
                    <div wire:click="agregarEquipo({{ $eq->id }})"
                         style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #F1F5F9;display:flex;justify-content:space-between"
                         onmouseover="this.style.background='#EEF6FF'" onmouseout="this.style.background=''">
                        <span>{{ $eq->marca }} {{ $eq->modelo }} {{ $eq->capacidad_gb }}GB {{ $eq->color }}</span>
                        <span style="color:var(--bs-blue);font-weight:600">u$s {{ $eq->precio_usd }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @foreach($items as $i => $item)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #EEF2F5;font-size:.82rem">
                <span>{{ $item['nombre'] }}</span>
                <div style="display:flex;align-items:center;gap:.75rem">
                    <span style="color:var(--bs-muted)">u$s {{ number_format($item['precio_usd'],0,',','.') }}</span>
                    <span style="font-weight:600;color:var(--bs-blue)">${{ number_format($item['precio_ars'],0,',','.') }}</span>
                    <button wire:click="quitarItem({{ $i }})" style="background:none;border:none;color:var(--bs-danger-text);cursor:pointer">✕</button>
                </div>
            </div>
            @endforeach
            @if(!empty($items))
            <div style="display:flex;justify-content:space-between;font-size:.9rem;font-weight:700;padding:.6rem 0;border-top:2px solid var(--bs-dark);margin-top:.5rem;color:var(--bs-blue)">
                <span>Total</span>
                <div style="text-align:right">
                    <div>u$s {{ number_format($this->totalUsd,0,',','.') }}</div>
                    <div style="font-size:.78rem;color:var(--bs-muted)">${{ number_format($this->totalArs,0,',','.') }} ARS</div>
                </div>
            </div>
            @endif
            <div class="bs-form-group" style="margin-top:.75rem">
                <label class="bs-label">Observaciones</label>
                <textarea class="bs-input" wire:model="observaciones" rows="2" placeholder="Condiciones del presupuesto, validez, etc."></textarea>
            </div>
            <button wire:click="generar" wire:loading.attr="disabled" class="bs-btn-black" style="width:100%;padding:.75rem" @if(empty($items)) disabled @endif>
                <span wire:loading.remove>📊 Generar presupuesto</span>
                <span wire:loading>Generando...</span>
            </button>
        </div>
    </div>
    <div style="font-size:.82rem;color:var(--bs-muted);padding:1rem">
        <p style="font-weight:600;color:var(--bs-dark);margin-bottom:.5rem">¿Cómo funciona?</p>
        <p>Buscá los equipos que el cliente está interesado en comprar. El sistema usa el dólar blue del día para mostrar el precio en pesos. El presupuesto tiene validez de 24 horas (según la variación del dólar).</p>
    </div>
</div>
@endif
</div>
