<?php
namespace App\Livewire\Categorias;

use Livewire\Component;
use App\Models\Categoria;

class FormCategoria extends Component
{
    public ?int   $categoriaId = null;
    public string $nombre      = '';
    public string $tipo        = 'accesorio';
    public int    $orden       = 0;
    public bool   $activo      = true;
    public bool   $modoEdicion = false;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:80',
            'tipo'   => 'required|in:accesorio,equipo',
            'orden'  => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'tipo.required'   => 'Seleccioná un tipo.',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->modoEdicion = true;
            $this->categoriaId = $id;
            $cat = Categoria::findOrFail($id);
            $this->nombre = $cat->nombre;
            $this->tipo   = $cat->tipo;
            $this->orden  = $cat->orden;
            $this->activo = $cat->activo;
        } else {
            $this->orden = (Categoria::where('comercio_id', 1)->max('orden') ?? 0) + 1;
        }
    }

    public function guardar(): void
    {
        $this->validate();
        $datos = [
            'comercio_id' => 1,
            'nombre'      => trim($this->nombre),
            'tipo'        => $this->tipo,
            'orden'       => $this->orden,
            'activo'      => $this->activo,
        ];
        if ($this->modoEdicion) {
            Categoria::findOrFail($this->categoriaId)->update($datos);
            session()->flash('success', 'Categoría actualizada.');
        } else {
            Categoria::create($datos);
            session()->flash('success', 'Categoría creada.');
        }
        $this->redirect(route('categorias'));
    }

    public function render()
    {
        return view('livewire.categorias.form-categoria')
            ->layout('layouts.app', ['title' => $this->modoEdicion ? 'Editar categoría' : 'Nueva categoría']);
    }
}
