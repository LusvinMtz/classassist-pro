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
    public string $punteoMax   = '100';
    public string $orden       = '0';

    protected $rules = [
        'nombre'      => 'required|string|max:50',
        'descripcion' => 'nullable|string|max:255',
        'punteoMax'   => 'required|numeric|min:0.01|max:9999',
        'orden'       => 'required|integer|min:0',
    ];

    protected $messages = [
        'nombre.required'   => 'El nombre es obligatorio.',
        'nombre.max'        => 'El nombre no puede superar 50 caracteres.',
        'nombre.unique'     => 'Ya existe un tipo con ese nombre.',
        'punteoMax.required'=> 'El punteo máximo es obligatorio.',
        'punteoMax.min'     => 'El punteo debe ser mayor a 0.',
    ];

    public function openCreate(): void
    {
        $this->reset(['nombre', 'descripcion', 'punteoMax', 'orden', 'editingId']);
        $this->punteoMax = '100';
        $this->orden     = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $tipo = TipoCalificacion::findOrFail($id);
        $this->editingId   = $id;
        $this->nombre      = $tipo->nombre;
        $this->descripcion = $tipo->descripcion ?? '';
        $this->punteoMax   = (string) $tipo->punteo_max;
        $this->orden       = (string) $tipo->orden;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $uniqueRule = $this->editingId
            ? 'unique:tipo_calificacion,nombre,' . $this->editingId
            : 'unique:tipo_calificacion,nombre';

        $this->validate([
            'nombre'      => "required|string|max:50|{$uniqueRule}",
            'descripcion' => 'nullable|string|max:255',
            'punteoMax'   => 'required|numeric|min:0.01|max:9999',
            'orden'       => 'required|integer|min:0',
        ]);

        TipoCalificacion::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nombre'      => $this->nombre,
                'descripcion' => $this->descripcion ?: null,
                'punteo_max'  => (float) $this->punteoMax,
                'orden'       => (int) $this->orden,
            ]
        );

        $this->showModal = false;
        $this->reset(['nombre', 'descripcion', 'punteoMax', 'orden', 'editingId']);
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
            'tipos' => TipoCalificacion::withCount('calificaciones')->orderBy('orden')->orderBy('nombre')->get(),
        ]);
    }
}
