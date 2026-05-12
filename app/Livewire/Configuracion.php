<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Configuracion as ConfigModel;
use App\Models\HistorialDolar;
use Illuminate\Support\Facades\Http;

class Configuracion extends Component
{
    public string $recargo_tarjeta   = '';
    public string $cuotas_4_recargo  = '';
    public string $cuotas_20_recargo = '';
    public string $dolar_blue_hoy    = '';
    public string $dolarActualizado  = '';
    public bool   $sincronizando     = false;

    protected function rules(): array
    {
        return [
            'recargo_tarjeta'   => 'required|numeric|min:0|max:100',
            'cuotas_4_recargo'  => 'required|numeric|min:0|max:100',
            'cuotas_20_recargo' => 'required|numeric|min:0|max:100',
            'dolar_blue_hoy'    => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'recargo_tarjeta.required'   => 'Ingresá el recargo de tarjeta.',
        'cuotas_4_recargo.required'  => 'Ingresá el recargo de 4 cuotas.',
        'cuotas_20_recargo.required' => 'Ingresá el recargo de 20 cuotas.',
        'dolar_blue_hoy.required'    => 'Ingresá el valor del dólar.',
    ];

    public function mount(): void
    {
        $config = ConfigModel::where('comercio_id', 1)->first();
        if ($config) {
            $this->recargo_tarjeta   = (string) $config->recargo_tarjeta;
            $this->cuotas_4_recargo  = (string) $config->cuotas_4_recargo;
            $this->cuotas_20_recargo = (string) $config->cuotas_20_recargo;
            $this->dolar_blue_hoy    = (string) $config->dolar_blue_hoy;
            $this->dolarActualizado  = $config->dolar_actualizado_en
                ? $config->dolar_actualizado_en->format('d/m/Y H:i')
                : 'Sin actualizar';
        }
    }

    public function guardarRecargos(): void
    {
        $this->validateOnly('recargo_tarjeta');
        $this->validateOnly('cuotas_4_recargo');
        $this->validateOnly('cuotas_20_recargo');

        ConfigModel::where('comercio_id', 1)->update([
            'recargo_tarjeta'   => (float) $this->recargo_tarjeta,
            'cuotas_4_recargo'  => (float) $this->cuotas_4_recargo,
            'cuotas_20_recargo' => (float) $this->cuotas_20_recargo,
        ]);

        session()->flash('success_recargos', 'Recargos actualizados correctamente.');
    }

    public function guardarDolar(): void
    {
        $this->validateOnly('dolar_blue_hoy');

        $config = ConfigModel::where('comercio_id', 1)->first();
        $config->update([
            'dolar_blue_hoy'      => (float) $this->dolar_blue_hoy,
            'dolar_actualizado_en'=> now(),
        ]);

        HistorialDolar::updateOrCreate(
            ['fecha' => today()],
            [
                'valor_compra' => (float) $this->dolar_blue_hoy,
                'valor_venta'  => (float) $this->dolar_blue_hoy,
                'fuente'       => 'manual',
            ]
        );

        $this->dolarActualizado = now()->format('d/m/Y H:i');
        session()->flash('success_dolar', 'Dólar blue actualizado.');
    }

    public function sincronizarDolar(): void
    {
        $this->sincronizando = true;

        try {
            $response = Http::timeout(8)->get('https://api.bluelytics.com.ar/v2/latest');

            if ($response->successful()) {
                $data      = $response->json();
                $valorVenta = $data['blue']['value_sell'] ?? null;
                $valorCompra = $data['blue']['value_buy'] ?? null;

                if ($valorVenta) {
                    $this->dolar_blue_hoy = (string) $valorVenta;

                    ConfigModel::where('comercio_id', 1)->update([
                        'dolar_blue_hoy'       => $valorVenta,
                        'dolar_actualizado_en' => now(),
                    ]);

                    HistorialDolar::updateOrCreate(
                        ['fecha' => today()],
                        [
                            'valor_compra' => $valorCompra ?? $valorVenta,
                            'valor_venta'  => $valorVenta,
                            'fuente'       => 'bluelytics',
                        ]
                    );

                    $this->dolarActualizado = now()->format('d/m/Y H:i');
                    session()->flash('success_dolar', "Dólar sincronizado: $" . number_format($valorVenta, 2, ',', '.'));
                }
            } else {
                session()->flash('error_dolar', 'No se pudo conectar con la API. Ingresá el valor manualmente.');
            }
        } catch (\Exception $e) {
            session()->flash('error_dolar', 'Error de conexión. Ingresá el valor manualmente.');
        }

        $this->sincronizando = false;
    }

    public function getHistorialProperty()
    {
        return HistorialDolar::orderByDesc('fecha')->limit(10)->get();
    }

    public function render()
    {
        return view('livewire.configuracion')
            ->layout('layouts.app', ['title' => 'Configuración']);
    }
}
