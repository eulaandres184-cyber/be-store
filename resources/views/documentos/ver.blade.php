<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $doc->label_tipo }} N° {{ $doc->numero }} — BE Store</title>
    @vite(['resources/css/documentos.css'])
    <style>
        body { background: #f0f0f0; padding: 2rem; font-family: system-ui, sans-serif; }
        .doc-preview { max-width: 680px; margin: 0 auto; }
        .acciones-print { display: flex; gap: .75rem; justify-content: center; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .btn-print { padding: .6rem 1.25rem; border-radius: 8px; font-size: .9rem; font-weight: 500; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; }
        @media print {
            body { background: #fff; padding: 0; }
            .acciones-print { display: none; }
        }
    </style>
</head>
<body>

<div class="acciones-print">
    <button class="btn-print" style="background:#2C3E50;color:#fff" onclick="window.print()">
        🖨️ Imprimir / Guardar PDF
    </button>
    <a class="btn-print" style="background:#25D366;color:#fff;text-decoration:none"
       href="{{ route('documentos.whatsapp', $doc->id) }}" target="_blank">
        📱 Compartir por WhatsApp
    </a>
    <button class="btn-print" style="background:#f0f0f0;color:#333;border:1px solid #ccc" onclick="window.close()">
        ✕ Cerrar
    </button>
</div>

<div class="doc-preview">
    @php $documentoId = $doc->id; @endphp
    @include('livewire.documentos.partials.vista-documento')
</div>

</body>
</html>
