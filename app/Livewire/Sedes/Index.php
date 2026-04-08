<?php

namespace App\Livewire\Sedes;

use App\Models\Carrera;
use App\Models\Sede;
use Livewire\Component;

class Index extends Component
{
    public bool  $showModal  = false;
    public ?int  $editingId  = null;

    public string $nombre    = '';
    public string $codigo    = '';
    public string $direccion = '';
    public array  $carrerasSeleccionadas = [];

    public function render(): \Illuminate\View\View
    {
        return view('livewire.sedes.index', [
            'sedes'    => Sede::with('carreras')->orderBy('nombre')->get(),
            'carreras' => Carrera::orderBy('nombre')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['nombre', 'codigo', 'direccion', 'editingId', 'carrerasSeleccionadas']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $sede = Sede::with('carreras')->findOrFail($id);
        $this->editingId  = $id;
        $this->nombre     = $sede->nombre;
        $this->codigo     = $sede->codigo;
        $this->direccion  = $sede->direccion ?? '';
        $this->carrerasSeleccionadas = $sede->carreras->pluck('id')->map(fn($v) => (string) $v)->toArray();
        $this->showModal  = true;
    }

    public function save(): void
    {
        $uniqueCodigo = $this->editingId
            ? 'unique:sede,codigo,' . $this->editingId
            : 'unique:sede,codigo';

        $this->validate([
            'nombre'   => 'required|string|max:100',
            'codigo'   => "required|string|max:10|{$uniqueCodigo}",
            'direccion'=> 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique'   => 'Ya existe una sede con ese código.',
        ]);

        $sede = Sede::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nombre'    => $this->nombre,
                'codigo'    => strtoupper(trim($this->codigo)),
                'direccion' => $this->direccion ?: null,
            ]
        );

        $sede->carreras()->sync($this->carrerasSeleccionadas);

        $this->showModal = false;
        $this->reset(['nombre', 'codigo', 'direccion', 'editingId', 'carrerasSeleccionadas']);
        $this->dispatch('notify', message: 'Sede guardada correctamente.');
    }

    public function delete(int $id): void
    {
        Sede::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Sede eliminada.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }
}
