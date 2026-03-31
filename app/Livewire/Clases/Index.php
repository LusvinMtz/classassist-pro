<?php

namespace App\Livewire\Clases;

use App\Models\Clase;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:100')]
    public string $nombre = '';

    #[Validate('nullable|string|max:500')]
    public string $descripcion = '';

    public function render()
    {
        return view('livewire.clases.index', [
            'clases' => Clase::where('usuario_id', auth()->id())
                ->withCount(['estudiantes', 'sesiones'])
                ->latest()
                ->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['nombre', 'descripcion', 'editingId']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $clase = Clase::where('usuario_id', auth()->id())->findOrFail($id);
        $this->editingId  = $id;
        $this->nombre     = $clase->nombre;
        $this->descripcion = $clase->descripcion ?? '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'usuario_id'  => auth()->id(),
        ];

        if ($this->editingId) {
            Clase::where('usuario_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            Clase::create($data);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        Clase::where('usuario_id', auth()->id())->findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['nombre', 'descripcion', 'editingId']);
        $this->resetValidation();
    }
}
