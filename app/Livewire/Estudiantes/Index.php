<?php

namespace App\Livewire\Estudiantes;

use App\Imports\EstudiantesImport;
use App\Models\Clase;
use App\Models\Estudiante;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;

    public ?int $claseId = null;

    // --- Modal individual ---
    public bool  $showModal  = false;
    public ?int  $editingId  = null;

    #[Validate('required|string|max:50')]
    public string $carnet = '';

    #[Validate('required|string|max:100')]
    public string $nombre = '';

    #[Validate('nullable|email|max:100')]
    public string $correo = '';

    // --- Modal importar ---
    public bool  $showImportModal = false;
    public       $archivo         = null;
    public array $erroresImport   = [];
    public int   $importados      = 0;

    public function render()
    {
        $clases = Clase::whereHas('catedraticos', function ($q) {$q->where('users.id', auth()->id());})->get();
        $estudiantes = collect();

        if ($this->claseId) {
            $clase = Clase::where('usuario_id', auth()->id())->find($this->claseId);
            if ($clase) {
                $estudiantes = $clase->estudiantes()->orderBy('nombre')->get();
            }
        }

        return view('livewire.estudiantes.index', compact('clases', 'estudiantes'));
    }

    // ---- Individual ----

    public function openCreate(): void
    {
        $this->reset(['carnet', 'nombre', 'correo', 'editingId']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Estudiante::findOrFail($id);
        $this->editingId = $id;
        $this->carnet    = $e->carnet;
        $this->nombre    = $e->nombre;
        $this->correo    = $e->correo ?? '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if (!$this->claseId) return;

        $clase = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

        // Carnet único por clase
        $carnetDuplicado = $clase->estudiantes()
            ->where('carnet', $this->carnet)
            ->when($this->editingId, fn ($q) => $q->where('estudiante.id', '!=', $this->editingId))
            ->exists();

        if ($carnetDuplicado) {
            $this->addError('carnet', 'Ya existe un estudiante con este carné en la clase.');
            return;
        }

        // Correo único por clase (solo si se proporcionó)
        if ($this->correo) {
            $correoDuplicado = $clase->estudiantes()
                ->where('correo', $this->correo)
                ->when($this->editingId, fn ($q) => $q->where('estudiante.id', '!=', $this->editingId))
                ->exists();

            if ($correoDuplicado) {
                $this->addError('correo', 'Ya existe un estudiante con este correo en la clase.');
                return;
            }
        }

        $data = [
            'carnet' => $this->carnet,
            'nombre' => $this->nombre,
            'correo' => $this->correo ?: null,
        ];

        if ($this->editingId) {
            Estudiante::findOrFail($this->editingId)->update($data);
        } else {
            $estudiante = Estudiante::create($data);
            $clase->estudiantes()->attach($estudiante->id);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        if (!$this->claseId) return;

        Clase::where('usuario_id', auth()->id())
            ->findOrFail($this->claseId)
            ->estudiantes()
            ->detach($id);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['carnet', 'nombre', 'correo', 'editingId']);
        $this->resetValidation();
    }

    // ---- Importar ----

    public function openImport(): void
    {
        $this->reset(['archivo', 'erroresImport', 'importados']);
        $this->showImportModal = true;
    }

    public function importar(): void
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if (!$this->claseId) return;

        $clase  = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        $import = new EstudiantesImport($clase);

        Excel::import($import, $this->archivo->getRealPath());

        $this->importados    = $import->importados;
        $this->erroresImport = $import->errores;
        $this->archivo       = null;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['archivo', 'erroresImport', 'importados']);
    }
}
