<?php

namespace App\Livewire\Productos;

use Livewire\Component;
use App\Models\Producto;
use App\Models\Categoria;

class FormProducto extends Component
{
    // Campos del formulario
    public ?int    $productoId      = null;
    public string  $codigo_interno  = '';
    public string  $codigo_barras   = '';
    public string  $nombre          = '';
    public string  $descripcion     = '';
    public string  $categoria_id    = '';
    public string  $precio_efectivo = '';
    public string  $moneda          = 'ARS';
    public int     $stock_actual    = 0;
    public int     $stock_minimo    = 1;
    public bool    $tiene_variantes = false;
    public bool    $activo          = true;

    // UI
    public bool   $modoEdicion    = false;
    public bool   $guardado       = false;
    public string $codigoEscaneado = '';

    protected function rules(): array
    {
        $uniqueCodInt = 'nullable|string|max:50|unique:productos,codigo_interno' . ($this->productoId ? ",{$this->productoId}" : '');
        $uniqueCodBar = 'nullable|string|max:50|unique:productos,codigo_barras'  . ($this->productoId ? ",{$this->productoId}" : '');

        return [
            'codigo_interno'  => $uniqueCodInt,
            'codigo_barras'   => $uniqueCodBar,
            'nombre'          => 'required|string|max:200',
            'descripcion'     => 'nullable|string',
            'categoria_id'    => 'required|exists:categorias,id',
            'precio_efectivo' => 'required|numeric|min:0',
            'moneda'          => 'required|in:ARS,USD',
            'stock_actual'    => 'required|integer|min:0',
            'stock_minimo'    => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'nombre.required'          => 'El nombre es obligatorio.',
        'categoria_id.required'    => 'Seleccioná una categoría.',
        'precio_efectivo.required' => 'El precio es obligatorio.',
        'precio_efectivo.numeric'  => 'El precio debe ser un número.',
        'codigo_interno.unique'    => 'Este código interno ya existe.',
        'codigo_barras.unique'     => 'Este código de barras ya está registrado.',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->modoEdicion = true;
            $this->productoId  = $id;
            $producto = Producto::findOrFail($id);

            $this->codigo_interno  = $producto->codigo_interno  ?? '';
            $this->codigo_barras   = $producto->codigo_barras   ?? '';
            $this->nombre          = $producto->nombre;
            $this->descripcion     = $producto->descripcion     ?? '';
            $this->categoria_id    = (string) $producto->categoria_id;
            $this->precio_efectivo = (string) $producto->precio_efectivo;
            $this->moneda          = $producto->moneda;
            $this->stock_actual    = $producto->stock_actual;
            $this->stock_minimo    = $producto->stock_minimo;
            $this->tiene_variantes = $producto->tiene_variantes;
            $this->activo          = $producto->activo;
        } else {
            // Nuevo producto: generar código interno automático
            $this->codigo_interno = Producto::generarCodigoInterno();
        }
    }

    // Cuando se escanea un código de barras (desde pistola o cámara)
    public function codigoEscaneado(string $codigo): void
    {
        $this->codigo_barras = $codigo;

        // Verificar si ya existe
        $existe = Producto::where('codigo_barras', $codigo)
            ->where('id', '!=', $this->productoId)
            ->first();

        if ($existe) {
            session()->flash('warning', "Este código ya está asignado a: {$existe->nombre}");
        }
    }

    public function guardar(): void
    {
        $this->validate();

        $datos = [
            'comercio_id'     => 1,
            'codigo_interno'  => strtoupper(trim($this->codigo_interno)) ?: null,
            'codigo_barras'   => trim($this->codigo_barras) ?: null,
            'nombre'          => trim($this->nombre),
            'descripcion'     => trim($this->descripcion) ?: null,
            'categoria_id'    => (int) $this->categoria_id,
            'precio_efectivo' => (float) $this->precio_efectivo,
            'moneda'          => $this->moneda,
            'stock_actual'    => $this->stock_actual,
            'stock_minimo'    => $this->stock_minimo,
            'tiene_variantes' => $this->tiene_variantes,
            'activo'          => $this->activo,
        ];

        if ($this->modoEdicion) {
            Producto::findOrFail($this->productoId)->update($datos);
            session()->flash('success', 'Producto actualizado correctamente.');
        } else {
            Producto::create($datos);
            session()->flash('success', 'Producto creado correctamente.');
        }

        $this->guardado = true;
        $this->redirect(route('productos'));
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('comercio_id', 1)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function render()
    {
        return view('livewire.productos.form-producto')
            ->layout('layouts.app', ['title' => $this->modoEdicion ? 'Editar producto' : 'Nuevo producto']);
    }
}
