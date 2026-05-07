<?php

namespace App\Livewire\Equipos;

use Livewire\Component;
use App\Models\EquipoDetalle;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;

class FormEquipo extends Component
{
    // Datos del equipo
    public ?int    $equipoId      = null;
    public string  $imei          = '';
    public string  $marca         = '';
    public string  $modelo        = '';
    public string  $capacidad_gb  = '';
    public string  $color         = '';
    public string  $condicion     = 'nuevo';
    public string  $precio_usd    = '';
    public string  $bateria_pct   = '';
    public string  $estado        = 'disponible';

    // Datos del producto asociado
    public string  $nombre_producto = '';
    public string  $descripcion     = '';
    public string  $categoria_id    = '';
    public string  $codigo_barras   = '';

    public bool    $modoEdicion = false;

    // Marcas y modelos comunes
    public array $marcasComunes  = ['iPhone', 'Samsung', 'Motorola', 'Xiaomi', 'Otro'];
    public array $coloresComunes = ['Negro', 'Blanco', 'Azul', 'Rojo', 'Verde', 'Violeta', 'Titanio', 'Natural', 'Otro'];

    protected function rules(): array
    {
        $uniqueImei = 'required|string|max:20|unique:equipos_detalle,imei' . ($this->equipoId ? ",{$this->equipoId}" : '');
        return [
            'imei'            => $uniqueImei,
            'marca'           => 'required|string|max:60',
            'modelo'          => 'required|string|max:100',
            'capacidad_gb'    => 'nullable|integer|min:1',
            'color'           => 'nullable|string|max:50',
            'condicion'       => 'required|in:nuevo,usado,reacondicionado',
            'precio_usd'      => 'required|numeric|min:0',
            'bateria_pct'     => 'nullable|integer|min:0|max:100',
            'estado'          => 'required|in:disponible,vendido,reservado',
            'nombre_producto' => 'required|string|max:200',
            'categoria_id'    => 'required|exists:categorias,id',
        ];
    }

    protected $messages = [
        'imei.required'            => 'El IMEI es obligatorio.',
        'imei.unique'              => 'Este IMEI ya está registrado.',
        'marca.required'           => 'La marca es obligatoria.',
        'modelo.required'          => 'El modelo es obligatorio.',
        'precio_usd.required'      => 'El precio en USD es obligatorio.',
        'nombre_producto.required' => 'El nombre del producto es obligatorio.',
        'categoria_id.required'    => 'Seleccioná una categoría.',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->modoEdicion = true;
            $this->equipoId    = $id;
            $equipo = EquipoDetalle::with('producto')->findOrFail($id);

            $this->imei         = $equipo->imei;
            $this->marca        = $equipo->marca;
            $this->modelo       = $equipo->modelo;
            $this->capacidad_gb = (string) ($equipo->capacidad_gb ?? '');
            $this->color        = $equipo->color ?? '';
            $this->condicion    = $equipo->condicion;
            $this->precio_usd   = (string) $equipo->precio_usd;
            $this->bateria_pct  = (string) ($equipo->bateria_pct ?? '');
            $this->estado       = $equipo->estado;

            if ($equipo->producto) {
                $this->nombre_producto = $equipo->producto->nombre;
                $this->descripcion     = $equipo->producto->descripcion ?? '';
                $this->categoria_id    = (string) $equipo->producto->categoria_id;
                $this->codigo_barras   = $equipo->producto->codigo_barras ?? '';
            }
        } else {
            // Categoría por defecto: Celulares
            $cat = Categoria::where('comercio_id', 1)->where('nombre', 'Celulares')->first();
            if ($cat) $this->categoria_id = (string) $cat->id;
        }
    }

    // Nombre auto-generado al cambiar marca/modelo/capacidad/color
    public function updatedMarca()       { $this->autoNombre(); }
    public function updatedModelo()      { $this->autoNombre(); }
    public function updatedCapacidadGb() { $this->autoNombre(); }
    public function updatedColor()       { $this->autoNombre(); }

    private function autoNombre(): void
    {
        if (!$this->modoEdicion) {
            $partes = array_filter([
                $this->marca,
                $this->modelo,
                $this->capacidad_gb ? $this->capacidad_gb . 'GB' : null,
                $this->color,
            ]);
            $this->nombre_producto = implode(' ', $partes);
        }
    }

    public function getPrecioArsProperty(): float
    {
        $dolar = Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0;
        return (float) $this->precio_usd * $dolar;
    }

    public function getDolarProperty(): float
    {
        return (float) (Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0);
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)
            ->where('tipo', 'equipo')
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->modoEdicion) {
                $equipo = EquipoDetalle::findOrFail($this->equipoId);

                // Actualizar producto asociado
                $equipo->producto->update([
                    'nombre'          => $this->nombre_producto,
                    'descripcion'     => $this->descripcion ?: null,
                    'categoria_id'    => (int) $this->categoria_id,
                    'precio_efectivo' => $this->precioArs,
                    'moneda'          => 'USD',
                    'codigo_barras'   => $this->codigo_barras ?: null,
                ]);

                // Actualizar equipo
                $equipo->update([
                    'imei'        => $this->imei,
                    'marca'       => $this->marca,
                    'modelo'      => $this->modelo,
                    'capacidad_gb'=> $this->capacidad_gb ?: null,
                    'color'       => $this->color ?: null,
                    'condicion'   => $this->condicion,
                    'precio_usd'  => (float) $this->precio_usd,
                    'bateria_pct' => $this->bateria_pct ?: null,
                    'estado'      => $this->estado,
                ]);
            } else {
                // Crear producto primero
                $producto = Producto::create([
                    'comercio_id'     => 1,
                    'categoria_id'    => (int) $this->categoria_id,
                    'nombre'          => $this->nombre_producto,
                    'descripcion'     => $this->descripcion ?: null,
                    'precio_efectivo' => $this->precioArs,
                    'moneda'          => 'USD',
                    'stock_actual'    => 1,
                    'stock_minimo'    => 1,
                    'activo'          => true,
                    'codigo_interno'  => Producto::generarCodigoInterno(),
                    'codigo_barras'   => $this->codigo_barras ?: null,
                ]);

                // Crear equipo
                EquipoDetalle::create([
                    'producto_id' => $producto->id,
                    'imei'        => $this->imei,
                    'marca'       => $this->marca,
                    'modelo'      => $this->modelo,
                    'capacidad_gb'=> $this->capacidad_gb ?: null,
                    'color'       => $this->color ?: null,
                    'condicion'   => $this->condicion,
                    'precio_usd'  => (float) $this->precio_usd,
                    'bateria_pct' => $this->bateria_pct ?: null,
                    'estado'      => 'disponible',
                ]);
            }
        });

        session()->flash('success', $this->modoEdicion ? 'Equipo actualizado.' : 'Equipo registrado correctamente.');
        $this->redirect(route('equipos'));
    }

    public function render()
    {
        return view('livewire.equipos.form-equipo')
            ->layout('layouts.app', ['title' => $this->modoEdicion ? 'Editar equipo' : 'Nuevo equipo']);
    }
}
