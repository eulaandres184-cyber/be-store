<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Configuración</h1>
        <p>Actualiza los parámetros de recargos y tipo de cambio para tu comercio.</p>
    </header>

    @if(session('success'))
        <div class="bs-alert bs-alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="saveConfiguracion" class="bs-form">
        <div class="bs-form-row">
            <label for="recargo_tarjeta">Recargo tarjeta (%)</label>
            <input id="recargo_tarjeta" type="number" step="0.01" min="0" max="100" wire:model.defer="recargo_tarjeta" />
            @error('recargo_tarjeta') <span class="bs-error">{{ $message }}</span> @enderror
        </div>

        <div class="bs-form-row">
            <label for="cuotas_4_recargo">Cuotas 4 recargo (%)</label>
            <input id="cuotas_4_recargo" type="number" step="0.01" min="0" max="100" wire:model.defer="cuotas_4_recargo" />
            @error('cuotas_4_recargo') <span class="bs-error">{{ $message }}</span> @enderror
        </div>

        <div class="bs-form-row">
            <label for="cuotas_20_recargo">Cuotas 20 recargo (%)</label>
            <input id="cuotas_20_recargo" type="number" step="0.01" min="0" max="100" wire:model.defer="cuotas_20_recargo" />
            @error('cuotas_20_recargo') <span class="bs-error">{{ $message }}</span> @enderror
        </div>

        <div class="bs-form-row">
            <label for="dolar_blue_hoy">Dólar blue hoy</label>
            <input id="dolar_blue_hoy" type="number" step="0.01" min="0" wire:model.defer="dolar_blue_hoy" />
            @error('dolar_blue_hoy') <span class="bs-error">{{ $message }}</span> @enderror
        </div>

        <div class="bs-form-row">
            <label>Última actualización</label>
            <div class="bs-form-note">{{ $dolar_actualizado_en ?? 'Aún no actualizado' }}</div>
        </div>

        <button type="submit" class="bs-btn-primary">Guardar configuración</button>
    </form>
</div>
