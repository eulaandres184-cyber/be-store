@php
$doc = App\Models\Documento::with(['cliente','comercio','usuario'])->find($documentoId);
$comercio = $doc->comercio;
@endphp
@if($doc)
<div class="doc-preview" id="doc-{{ $doc->id }}">

    {{-- Encabezado --}}
    <div class="doc-header">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div class="doc-emisor-nombre">{{ $comercio->nombre }}</div>
                <div class="doc-emisor-datos">
                    @if($comercio->direccion){{ $comercio->direccion }}<br>@endif
                    @if($comercio->telefono)Tel: {{ $comercio->telefono }}<br>@endif
                    @if($comercio->cuit)CUIT: {{ $comercio->cuit }}<br>@endif
                    Condición IVA: {{ $comercio->condicion_iva ?? 'Monotributista' }}
                </div>
            </div>
            <div style="text-align:right">
                <div class="doc-tipo-badge">{{ $doc->label_tipo }}</div>
                <div class="doc-numero">N° {{ $doc->numero }}</div>
                <div class="doc-fecha">{{ $doc->emitido_en->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @if($doc->tipo === 'ticket')
        <div class="doc-no-valido">NO VÁLIDO COMO FACTURA</div>
        @endif
        @if($doc->tipo === 'presupuesto')
        <div style="text-align:center;padding:.3rem;background:#FFF8E6;border:1px dashed #F5CBA7;border-radius:4px;font-size:.72rem;color:#9A6B00;margin-top:.5rem">
            PRESUPUESTO — Válido por 24 horas según cotización del dólar blue
        </div>
        @endif
    </div>

    {{-- Cliente --}}
    @if($doc->cliente || $doc->tipo === 'factura')
    <div class="doc-cliente-box">
        <div class="doc-cliente-titulo">{{ $doc->tipo === 'factura' ? 'Datos del receptor' : 'Cliente' }}</div>
        <div class="doc-cliente-nombre">
            {{ $doc->cliente?->nombre ?? ($doc->tipo === 'factura' ? 'Consumidor Final' : '—') }}
        </div>
        @if($doc->tipo === 'factura' && $doc->observaciones)
        @php $datosCliente = json_decode($doc->observaciones, true); @endphp
        @if($datosCliente)
        <div style="font-size:.78rem;color:var(--bs-muted)">
            @if($datosCliente['dni'] ?? '')DNI/CUIT: {{ $datosCliente['dni'] }}<br>@endif
            @if($datosCliente['domicilio'] ?? ''){{ $datosCliente['domicilio'] }}@endif
        </div>
        @endif
        @endif
    </div>
    @endif

    {{-- Ítems --}}
    <div class="doc-items">
        <table class="doc-items-tabla">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cant.</th>
                    @if($doc->tipo === 'presupuesto')<th class="text-right">USD</th>@endif
                    <th class="text-right">Precio unit.</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($doc->items as $item)
            <tr>
                <td>{{ $item['nombre'] }}</td>
                <td class="text-right">{{ $item['cantidad'] ?? 1 }}</td>
                @if($doc->tipo === 'presupuesto')
                <td class="text-right" style="color:var(--bs-muted)">
                    u$s {{ number_format($item['precio_usd'] ?? 0, 0, ',', '.') }}
                </td>
                @endif
                <td class="text-right">${{ number_format($item['precio_unit'] ?? $item['precio_ars'] ?? 0, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight:600">${{ number_format($item['subtotal'] ?? $item['precio_ars'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totales --}}
    <div class="doc-totales">
        @if($doc->descuento > 0)
        <div class="doc-total-row"><span>Subtotal</span><span>${{ number_format($doc->subtotal,0,',','.') }}</span></div>
        <div class="doc-total-row" style="color:#9A6B00"><span>Saldo pendiente</span><span>${{ number_format($doc->descuento,0,',','.') }}</span></div>
        @endif
        <div class="doc-total-final">
            <span>TOTAL</span>
            <span>${{ number_format($doc->total,0,',','.') }}</span>
        </div>
    </div>

    {{-- Pagos (recibo) --}}
    @if($doc->pagos && $doc->tipo === 'recibo')
    <div class="doc-pagos">
        <div class="doc-pagos-titulo">Detalle de pagos recibidos</div>
        @foreach($doc->pagos as $pago)
        <div class="doc-pago-row">
            <span>{{ ucfirst(str_replace('_',' ', $pago['metodo'])) }} ({{ $pago['moneda'] ?? 'ARS' }})</span>
            <span style="font-weight:600">${{ number_format($pago['monto'],0,',','.') }}</span>
        </div>
        @endforeach
    </div>
    @if($doc->descuento > 0)
    <div class="doc-saldo">
        <span>⚠ Saldo pendiente de pago</span>
        <span>${{ number_format($doc->descuento,0,',','.') }}</span>
    </div>
    @endif
    @endif

    {{-- Observaciones --}}
    @if($doc->observaciones && $doc->tipo !== 'factura')
    <div style="padding:.75rem 1.5rem;font-size:.78rem;color:var(--bs-muted);border-top:1px solid var(--bs-gray-border)">
        📝 {{ $doc->observaciones }}
    </div>
    @endif

    {{-- Dólar blue (presupuesto) --}}
    @if($doc->dolar_blue && $doc->tipo === 'presupuesto')
    <div class="doc-dolar">Cotización dólar blue: ${{ number_format($doc->dolar_blue,2,',','.') }} — {{ $doc->emitido_en->format('d/m/Y H:i') }}</div>
    @endif

    {{-- CAE AFIP --}}
    @if($doc->tipo === 'factura' && $doc->tiene_cae)
    <div style="text-align:center; padding: 1rem 0; font-size: .8rem; border-top: 1px dashed var(--bs-gray-border); margin-top: 1rem;">
        <div><strong>CAE:</strong> {{ $doc->cae }}</div>
        <div><strong>Vto. CAE:</strong> {{ $doc->cae_vto->format('d/m/Y') }}</div>
    </div>
    @endif

    {{-- Pie --}}
    <div class="doc-footer">
        {{ $comercio->nombre }}
        @if($comercio->telefono) · {{ $comercio->telefono }}@endif
        @if($comercio->email) · {{ $comercio->email }}@endif
        <br>¡Gracias por su compra!
    </div>
</div>
@endif
