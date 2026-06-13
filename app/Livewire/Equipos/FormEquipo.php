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
    public ?int    $equipoId       = null;
    public string  $imei           = '';
    public string  $marca          = '';
    public string  $modelo         = '';
    public string  $capacidad_gb   = '';
    public string  $color          = '';
    public string  $condicion      = 'nuevo';
    public string  $precio_usd     = '';
    public string  $bateria_pct    = '';
    public string  $estado         = 'disponible';
    public string  $nombre_producto = '';
    public string  $descripcion     = '';
    public string  $categoria_id    = '';
    public string  $codigo_barras   = '';
    public bool    $modoEdicion     = false;

    public array $marcasComunes  = ['iPhone','Samsung','Motorola','Xiaomi','Oppo','Huawei','Otro'];
    public array $coloresComunes = ['Negro','Blanco','Azul','Rojo','Verde','Violeta','Titanio','Natural','Dorado','Otro'];

    protected function rules(): array
    {
        // IMEI es opcional al cargar — será requerido solo al vender
        $uniqueImei = 'nullable|string|max:20|unique:equipos_detalle,imei'
            . ($this->equipoId ? ",{$this->equipoId}" : '');

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
        'imei.unique'              => 'Este IMEI ya está registrado en otro equipo.',
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
            $this->imei          = $equipo->imei ?? '';
            $this->marca         = $equipo->marca ?? '';
            $this->modelo        = $equipo->modelo ?? '';
            $this->capacidad_gb  = (string)($equipo->capacidad_gb ?? '');
            $this->color         = $equipo->color ?? '';
            $this->condicion     = $equipo->condicion ?? 'nuevo';
            $this->precio_usd    = (string)($equipo->precio_usd ?? '');
            $this->bateria_pct   = (string)($equipo->bateria_pct ?? '');
            $this->estado        = $equipo->estado ?? 'disponible';
            if ($equipo->producto) {
                $this->nombre_producto = $equipo->producto->nombre ?? '';
                $this->descripcion     = $equipo->producto->descripcion ?? '';
                $this->categoria_id    = (string)($equipo->producto->categoria_id ?? '');
                $this->codigo_barras   = $equipo->producto->codigo_barras ?? '';
            }
        } else {
            $cat = Categoria::where('comercio_id', 1)
                ->where('tipo', 'equipo')
                ->first();
            if ($cat) $this->categoria_id = (string)$cat->id;
        }
    }

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
                $this->capacidad_gb ? $this->capacidad_gb.'GB' : null,
                $this->color,
            ]);
            $this->nombre_producto = implode(' ', $partes);
        }
    }

    public function getPrecioArsProperty(): float
    {
        return (float)$this->precio_usd * $this->dolar;
    }

    public function getDolarProperty(): float
    {
        return (float)(Configuracion::where('comercio_id', 1)->value('dolar_blue_hoy') ?? 0);
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)
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
                $equipo->producto->update([
                    'nombre'          => $this->nombre_producto,
                    'descripcion'     => $this->descripcion ?: null,
                    'categoria_id'    => (int)$this->categoria_id,
                    'precio_efectivo' => $this->precioArs,
                    'moneda'          => 'USD',
                    'codigo_barras'   => $this->codigo_barras ?: null,
                ]);
                $equipo->update([
                    'imei'         => $this->imei ?: null,
                    'marca'        => $this->marca,
                    'modelo'       => $this->modelo,
                    'capacidad_gb' => $this->capacidad_gb ?: null,
                    'color'        => $this->color ?: null,
                    'condicion'    => $this->condicion,
                    'precio_usd'   => (float)$this->precio_usd,
                    'bateria_pct'  => $this->bateria_pct ?: null,
                    'estado'       => $this->estado,
                ]);
            } else {
                $producto = Producto::create([
                    'comercio_id'     => 1,
                    'categoria_id'    => (int)$this->categoria_id,
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
                EquipoDetalle::create([
                    'producto_id'  => $producto->id,
                    'imei'         => $this->imei ?: null,
                    'marca'        => $this->marca,
                    'modelo'       => $this->modelo,
                    'capacidad_gb' => $this->capacidad_gb ?: null,
                    'color'        => $this->color ?: null,
                    'condicion'    => $this->condicion,
                    'precio_usd'   => (float)$this->precio_usd,
                    'bateria_pct'  => $this->bateria_pct ?: null,
                    'estado'       => 'disponible',
                ]);
            }
        });

        session()->flash('success', $this->modoEdicion
            ? 'Equipo actualizado.'
            : 'Equipo registrado. Recordá agregar el IMEI antes de venderlo.');
        $this->redirect(route('equipos'));
    }

    public function render()
    {
        return view('livewire.equipos.form-equipo')
            ->layout('layouts.app', [
                'title' => $this->modoEdicion ? 'Editar equipo' : 'Nuevo equipo'
            ]);
    }
}
