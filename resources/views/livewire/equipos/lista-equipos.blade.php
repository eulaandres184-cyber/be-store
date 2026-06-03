<div class="bs-panel">
    <header class="bs-panel-header">
        <h1>Equipos</h1>
        <p>Gestiona tu inventario de celulares con IMEI.</p>
        <a href="{{ route('equipos.nuevo') }}" class="bs-btn-primary" style="margin-top:.5rem">+ Nuevo equipo</a>
    </header>

    <div class="bs-metric" style="margin-bottom:1rem">
        <div class="bs-metric-label">Equipos disponibles</div>
        <div class="bs-metric-value" style="color:#166534">{{ $equiposDisponibles }}</div>
    </div>

    <div class="bs-form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:.75rem; margin-bottom:1rem">
        <input type="text" class="bs-input" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar por IMEI o nombre..." />
        
        <select class="bs-select" wire:model.live="marcaFiltro">
            <option value="">Todas las marcas</option>
            @foreach($this->marcas as $marca)
                <option value="{{ $marca }}">{{ $marca }}</option>
            @endforeach
        </select>

        <select class="bs-select" wire:model.live="estadoFiltro">
            <option value="todos">Todos los estados</option>
            <option value="disponible">Disponibles</option>
            <option value="vendido">Vendidos</option>
            <option value="dañado">Dañados</option>
        </select>
    </div>

    @if($this->equipos->isEmpty())
        <div style="text-align:center; padding:2rem; color:#94A3B8">
            <div style="font-size:2rem;margin-bottom:.5rem">📱</div>
            <p>No se encontraron equipos</p>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="bs-table">
                <thead>
                    <tr>
                        <th>IMEI</th>
                        <th>Marca · Modelo</th>
                        <th>Producto</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($this->equipos as $equipo)
                <tr>
                    <td><strong>{{ $equipo->imei }}</strong></td>
                    <td>{{ $equipo->marca ?? '?' }} {{ $equipo->modelo ?? '?' }}</td>
                    <td>{{ $equipo->producto->nombre ?? '—' }}</td>
                    <td>
                        @php
                            $estadoColor = match($equipo->estado) {
                                'disponible' => 'green',
                                'vendido' => 'blue',
                                'dañado' => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <span class="bs-badge-{{ $estadoColor }}">
                            {{ ucfirst($equipo->estado) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('equipos.editar', $equipo->id) }}" class="bs-btn-small">Editar</a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:1rem">
            {{ $this->equipos->links() }}
        </div>
    @endif
</div>
