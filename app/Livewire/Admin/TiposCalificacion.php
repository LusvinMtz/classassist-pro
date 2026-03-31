<?php

namespace App\Livewire\Admin;

use App\Models\TipoCalificacion;
use Livewire\Component;

class TiposCalificacion extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $nombre      = '';
    public string $descripcion = '';

    protected $rules = [
        'nombre'      => 'required|string|max:50',
        'descripcion' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.max'      => 'El nombre no puede superar 50 caracteres.',
        'nombre.unique'   => 'Ya existe un tipo con ese nombre.',
    ];

    public function openCreate(): void
    {
        $this->reset(['nombre', 'descripcion', 'editingId']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $tipo = TipoCalificacion::findOrFail($id);
        $this->editingId   = $id;
        $this->nombre      = $tipo->nombre;
        $this->descripcion = $tipo->descripcion ?? '';
        $this->showModal   = true;
    }

    public function save(): void
    {
        $uniqueRule = $this->editingId
            ? 'unique:tipo_calificacion,nombre,' . $this->editingId
            : 'unique:tipo_calificacion,nombre';

        $this->validate(['nombre' => "required|string|max:50|{$uniqueRule}", 'descripcion' => 'nullable|string|max:255']);

        TipoCalificacion::updateOrCreate(
            ['id' => $this->editingId],
            ['nombre' => $this->nombre, 'descripcion' => $this->descripcion ?: null]
        );

        $this->showModal = false;
        $this->reset(['nombre', 'descripcion', 'editingId']);
    }

    public function delete(int $id): void
    {
        TipoCalificacion::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.tipos-calificacion', [
            'tipos' => TipoCalificacion::withCount('calificaciones')->orderBy('nombre')->get(),
        ]);
    }
}
