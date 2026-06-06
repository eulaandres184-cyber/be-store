<div>
    <div class="bs-page-title">
        <span>📊 Historial de Cambios</span>
        <a href="{{ route('configuracion') }}" class="bs-btn-secondary">← Atrás</a>
    </div>

    {{-- Tabs de tipo de historial --}}
    <div class="estado-tabs" style="margin-bottom: 1rem;">
        <div class="estado-tab {{ $tipoHistorial === 'precios' ? 'activo' : '' }}" wire:click="$set('tipoHistorial','precios')">
            💰 Precios de Productos
        </div>
        <div class="estado-tab {{ $tipoHistorial === 'configuracion' ? 'activo' : '' }}" wire:click="$set('tipoHistorial','configuracion')">
            ⚙️ Configuración del Sistema
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bs-card" style="margin-bottom: 1rem; padding: 1rem;">
        <div class="bs-form-row">
            <div class="bs-form-group" style="flex: 1; min-width: 200px;">
                <label class="bs-label">Buscar</label>
                <input
                    class="bs-input"
                    wire:model.live.debounce.300ms="busqueda"
                    placeholder="Buscar en historial..."
                />
            </div>

            <div class="bs-form-group" style="flex: 0 1 auto; min-width: 150px;">
                <label class="bs-label">Últimos días</label>
                <select class="bs-select" wire:model.live="diasAtras">
                    <option value="7">Últimos 7 días</option>
                    <option value="15">Últimos 15 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="60">Últimos 60 días</option>
                    <option value="90">Últimos 90 días</option>
                    <option value="365">Último año</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Historial de Precios --}}
    @if($tipoHistorial === 'precios')
    <div class="bs-card" style="padding: 0; overflow: hidden;">
        <table class="bs-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Anterior</th>
                    <th>Precio Nuevo</th>
                    <th>Cambio</th>
                    <th>%</th>
                    <th>Usuario</th>
                    <th>Razón</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historial as $registro)
                <tr wire:key="precio-{{ $registro->id }}">
                    <td style="font-weight: 500;">
                        <div>{{ $registro->producto->nombre ?? '—' }}</div>
                        <div style="font-size: 0.75rem; color: var(--bs-muted);">
                            {{ $registro->producto->codigo_interno ?? '' }}
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <span style="color: var(--bs-muted);">${{ number_format($registro->precio_anterior, 0, ',', '.') }}</span>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        ${{ number_format($registro->precio_nuevo, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right;">
                        <span style="color: {{ $registro->obtenerDiferencia() >= 0 ? 'var(--bs-success-text)' : 'var(--bs-danger-text)' }}; font-weight: 600;">
                            {{ $registro->obtenerDiferencia() >= 0 ? '+' : '' }}${{ number_format($registro->obtenerDiferencia(), 2, ',', '.') }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <span style="color: {{ $registro->obtenerPorcentajeCambio() >= 0 ? 'var(--bs-success-text)' : 'var(--bs-danger-text)' }};">
                            {{ $registro->obtenerPorcentajeCambio() >= 0 ? '+' : '' }}{{ number_format($registro->obtenerPorcentajeCambio(), 1) }}%
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem;">{{ $registro->usuario->name ?? 'Sistema' }}</span>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--bs-muted);">
                        {{ $registro->razon_cambio ?? '—' }}
                    </td>
                    <td style="font-size: 0.8rem; white-space: nowrap;">
                        {{ $registro->cambio_en->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: var(--bs-muted);">
                        No se encontraron cambios de precios
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- Historial de Configuración --}}
    @if($tipoHistorial === 'configuracion')
    <div class="bs-card" style="padding: 0; overflow: hidden;">
        <table class="bs-table">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor Anterior</th>
                    <th>Valor Nuevo</th>
                    <th>Usuario</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historial as $registro)
                <tr wire:key="config-{{ $registro->id }}">
                    <td style="font-weight: 600;">
                        {{ $registro->getNombreCampo() }}
                        <div style="font-size: 0.75rem; color: var(--bs-muted);">{{ $registro->campo }}</div>
                    </td>
                    <td style="color: var(--bs-muted); text-align: center;">
                        <code style="background: var(--bs-gray-light); padding: 2px 6px; border-radius: 3px;">
                            {{ $registro->valor_anterior ?? '—' }}
                        </code>
                    </td>
                    <td style="text-align: center; font-weight: 600;">
                        <code style="background: var(--bs-blue-light); padding: 2px 6px; border-radius: 3px; color: var(--bs-blue);">
                            {{ $registro->valor_nuevo }}
                        </code>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem;">{{ $registro->usuario->name ?? 'Sistema' }}</span>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--bs-muted);">
                        {{ $registro->descripcion ?? '—' }}
                    </td>
                    <td style="font-size: 0.8rem; white-space: nowrap;">
                        {{ $registro->cambio_en->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--bs-muted);">
                        No se encontraron cambios en configuración
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- Paginación --}}
    <div style="margin-top: 1rem;">
        {{ $historial->links() }}
    </div>
</div>
