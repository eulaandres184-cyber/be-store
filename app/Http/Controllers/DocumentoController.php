<?php
namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;

/**
 * DocumentoController
 *
 * Maneja la vista de impresión/PDF y el enlace de WhatsApp
 * para todos los tipos de documentos del sistema.
 */
class DocumentoController extends Controller
{
    /**
     * Muestra el documento en una página limpia lista para imprimir o guardar como PDF.
     * El usuario puede usar Ctrl+P del navegador para imprimir o guardar como PDF.
     */
    public function ver(int $id)
    {
        $doc = Documento::with(['cliente','comercio','usuario','venta'])
            ->findOrFail($id);

        return view('documentos.ver', compact('doc'));
    }

    /**
     * Genera el texto del documento y redirige a WhatsApp con ese texto pre-cargado.
     * Si hay número de teléfono del cliente lo usa, si no abre WhatsApp vacío.
     */
    public function whatsapp(int $id)
    {
        $doc     = Documento::with(['cliente','comercio'])->findOrFail($id);
        $comercio = $doc->comercio;

        // Construir el texto del mensaje
        $texto  = "*{$comercio->nombre}*\n";
        $texto .= "{$doc->label_tipo} N° {$doc->numero}\n";
        $texto .= "Fecha: {$doc->emitido_en->format('d/m/Y H:i')}\n\n";

        foreach ($doc->items as $item) {
            $cant  = $item['cantidad'] ?? 1;
            $sub   = number_format($item['subtotal'] ?? $item['precio_ars'] ?? 0, 0, ',', '.');
            $texto .= "• {$item['nombre']} × {$cant}: \${$sub}\n";
        }

        $texto .= "\n*TOTAL: $" . number_format($doc->total, 0, ',', '.') . "*";

        if ($doc->descuento > 0) {
            $texto .= "\nSaldo pendiente: $" . number_format($doc->descuento, 0, ',', '.');
        }

        if ($doc->tipo === 'presupuesto' && $doc->dolar_blue) {
            $texto .= "\n\n_Cotización dólar blue: $" . number_format($doc->dolar_blue, 2, ',', '.') . "_";
            $texto .= "\n_Válido por 24 horas_";
        }

        $texto .= "\n\n¡Gracias!";

        // Si el cliente tiene número de WhatsApp
        $telefono = $doc->cliente?->telefono
            ? preg_replace('/\D/', '', $doc->cliente->telefono)
            : '';

        $url = $telefono
            ? "https://wa.me/54{$telefono}?text=" . urlencode($texto)
            : "https://wa.me/?text=" . urlencode($texto);

        return redirect($url);
    }
}
