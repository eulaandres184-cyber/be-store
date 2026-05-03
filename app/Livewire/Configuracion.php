<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuracion as ConfiguracionModel;

class Configuracion extends Component
{
    public float $recargo_tarjeta = 15.00;
    public float $cuotas_4_recargo = 2.00;
    public float $cuotas_20_recargo = 20.00;
    public float $dolar_blue_hoy = 0.00;
    public ?string $dolar_actualizado_en = null;

    protected function rules(): array
    {
        return [
            'recargo_tarjeta'   => ['required', 'numeric', 'between:0,100'],
            'cuotas_4_recargo'  => ['required', 'numeric', 'between:0,100'],
            'cuotas_20_recargo' => ['required', 'numeric', 'between:0,100'],
            'dolar_blue_hoy'    => ['required', 'numeric', 'between:0,999999.99'],
        ];
    }

    public function mount(): void
    {
        $this->loadConfiguracion();
    }

    public function saveConfiguracion(): void
    {
        $this->validate();

        $config = ConfiguracionModel::firstOrNew([
            'comercio_id' => $this->comercioId(),
        ]);

        $config->fill([
            'recargo_tarjeta'   => $this->recargo_tarjeta,
            'cuotas_4_recargo'  => $this->cuotas_4_recargo,
            'cuotas_20_recargo' => $this->cuotas_20_recargo,
            'dolar_blue_hoy'    => $this->dolar_blue_hoy,
            'dolar_actualizado_en' => now(),
        ]);

        $config->save();

        $this->dolar_actualizado_en = $config->dolar_actualizado_en?->format('d/m/Y H:i');
        session()->flash('success', 'Configuración guardada correctamente.');
    }

    private function loadConfiguracion(): void
    {
        $config = ConfiguracionModel::where('comercio_id', $this->comercioId())->first();

        if (! $config) {
            return;
        }

        $this->recargo_tarjeta = (float) $config->recargo_tarjeta;
        $this->cuotas_4_recargo = (float) $config->cuotas_4_recargo;
        $this->cuotas_20_recargo = (float) $config->cuotas_20_recargo;
        $this->dolar_blue_hoy = (float) $config->dolar_blue_hoy;
        $this->dolar_actualizado_en = $config->dolar_actualizado_en?->format('d/m/Y H:i');
    }

    private function comercioId(): int
    {
        return Auth::user()?->comercio_id ?? 1;
    }

    public function render()
    {
        return view('livewire.configuracion')
            ->layout('layouts.app', ['title' => 'Configuración']);
    }
}
