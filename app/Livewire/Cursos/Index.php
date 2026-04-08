<?php

namespace App\Livewire\Cursos;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Facultad;
use Livewire\Component;

class Index extends Component
{
    public ?int $filtroFacultad = null;
    public ?int $filtroCarrera  = null;
    public ?int $filtroCiclo    = null;

    public bool   $showModal  = false;
    public ?int   $editingId  = null;
    public string $nombre     = '';
    public string $codigo     = '';
    public string $ciclo      = '1';
    public ?int   $carreraId  = null;
    public ?int   $facultadId = null;

    public function render(): \Illuminate\View\View
    {
        $query = Curso::with(['carrera', 'facultad'])->orderBy('ciclo')->orderBy('nombre');

        if ($this->filtroFacultad) $query->where('facultad_id', $this->filtroFacultad);
        if ($this->filtroCarrera)  $query->where('carrera_id',  $this->filtroCarrera);
        if ($this->filtroCiclo)    $query->where('ciclo',       $this->filtroCiclo);

        return view('livewire.cursos.index', [
            'cursos'    => $query->get(),
            'facultades'=> Facultad::orderBy('nombre')->get(),
            'carreras'  => Carrera::with('facultad')->orderBy('nombre')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['nombre', 'codigo', 'ciclo', 'carreraId', 'facultadId', 'editingId']);
        $this->ciclo     = '1';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = Curso::findOrFail($id);
        $this->editingId  = $id;
        $this->nombre     = $c->nombre;
        $this->codigo     = $c->codigo;
        $this->ciclo      = (string) $c->ciclo;
        $this->carreraId  = $c->carrera_id;
        $this->facultadId = $c->facultad_id;
        $this->showModal  = true;
    }

    public function save(): void
    {
        $uniqueCodigo = $this->editingId
            ? 'unique:curso,codigo,' . $this->editingId
            : 'unique:curso,codigo';

        $this->validate([
            'nombre'     => 'required|string|max:150',
            'codigo'     => "required|string|max:20|{$uniqueCodigo}",
            'ciclo'      => 'required|integer|min:1|max:10',
            'carreraId'  => 'required|exists:carrera,id',
            'facultadId' => 'required|exists:facultad,id',
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'codigo.required'    => 'El código es obligatorio.',
            'codigo.unique'      => 'Ya existe un curso con ese código.',
            'ciclo.required'     => 'El ciclo es obligatorio.',
            'carreraId.required' => 'Selecciona una carrera.',
            'facultadId.required'=> 'Selecciona una facultad.',
        ]);

        Curso::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nombre'      => $this->nombre,
                'codigo'      => trim($this->codigo),
                'ciclo'       => (int) $this->ciclo,
                'carrera_id'  => $this->carreraId,
                'facultad_id' => $this->facultadId,
            ]
        );

        $this->showModal = false;
        $this->reset(['nombre', 'codigo', 'ciclo', 'carreraId', 'facultadId', 'editingId']);
        $this->dispatch('notify', message: 'Curso guardado correctamente.');
    }

    public function delete(int $id): void
    {
        Curso::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Curso eliminado.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }
}
