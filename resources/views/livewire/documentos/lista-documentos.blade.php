@push('estilos')@vite(['resources/css/documentos.css'])@endpush
<div>
<div class="bs-page-title">
    <span>📄 Documentos emitidos</span>
    <a href="{{ route('documentos.presupuesto') }}" class="bs-btn-secondary">+ Presupuesto</a>
</div>

<div class="productos-filtros">
    <select class="bs-select" wire:model.live="tipoFiltro" style="width:auto">
        <option value="">Todos los tipos</option>
        <option value="ticket">Tickets</option>
        <option value="factura">Facturas C</option>
        <option value="recibo">Recibos</option>
        <option value="presupuesto">Presupuestos</option>
    </select>
    <input class="bs-input" type="date" wire:model.live="desde" style="width:auto"/>
    <input class="bs-input" type="date" wire:model.live="hasta" style="width:auto"/>
    <input class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por número..."/>
</div>

<div class="bs-card" style="padding:0;overflow:hidden">
    <table class="bs-table">
        <thead>
            <tr>
                <th>N° Documento</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($documentos as $doc)
        <tr wire:key="doc-{{ $doc->id }}">
            <td style="font-family:var(--bs-font-mono);font-size:.8rem">{{ $doc->numero }}</td>
            <td>
                <span class="bs-badge-{{ $doc->tipo==='ticket'?'blue':($doc->tipo==='factura'?'green':($doc->tipo==='recibo'?'amber':'blue')) }}">
                    {{ $doc->label_tipo }}
                </span>
            </td>
            <td>{{ $doc->cliente?->nombre ?? 'Consumidor Final' }}</td>
            <td style="font-size:.78rem">{{ $doc->emitido_en->format('d/m/Y H:i') }}</td>
            <td style="font-weight:700;color:var(--bs-blue)">${{ number_format($doc->total,0,',','.') }}</td>
            <td>
                <div class="tbl-actions">
                    <a href="{{ route('documentos.ver', $doc->id) }}" target="_blank" class="btn-edit">Ver / Imprimir</a>
                    <a href="{{ route('documentos.whatsapp', $doc->id) }}" target="_blank" style="background:#E8F5E9;color:#2E7D5E;border-color:#A9DFBF" class="btn-edit">WhatsApp</a>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--bs-muted)">No hay documentos en el período</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:.75rem">{{ $documentos->links() }}</div>
</div>
