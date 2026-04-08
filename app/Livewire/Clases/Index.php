<?php

namespace App\Livewire\Clases;

use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Sesion;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Index extends Component
{
    // ── Modal crear/editar (solo admin) ──────────────────────────────
    public bool $showModal = false;
    public ?int $editingId = null;

    #[Validate('required|string|max:100')]
    public string $nombre = '';

    #[Validate('nullable|string|max:500')]
    public string $descripcion = '';

    #[Validate('required|exists:carrera,id')]
    public ?int $carreraId = null;

    // ── Modal resumen (catedrático) ───────────────────────────────────
    public bool  $showResumen = false;
    public ?int  $resumenClaseId = null;

    public function render()
    {
        $user  = auth()->user();
        $esAdmin = $user->isAdmin();

        if ($esAdmin) {
            $clases = Clase::where('usuario_id', auth()->id())
                ->with('carrera')
                ->withCount(['estudiantes', 'sesiones'])
                ->latest()
                ->get();
        } else {
            $clases = $user->clasesImpartidas()
                ->with('carrera')
                ->withCount(['estudiantes', 'sesiones'])
                ->latest()
                ->get();
        }

        $resumenClase   = null;
        $sesionesRecientes = collect();

        if ($this->resumenClaseId) {
            $resumenClase = Clase::with(['carrera', 'catedratico'])
                ->withCount(['estudiantes', 'sesiones'])
                ->find($this->resumenClaseId);

            if ($resumenClase) {
                $sesionesRecientes = Sesion::where('clase_id', $resumenClase->id)
                    ->withCount(['asistencias', 'participaciones'])
                    ->latest('fecha')
                    ->limit(5)
                    ->get();
            }
        }

        return view('livewire.clases.index', compact(
            'clases', 'esAdmin', 'resumenClase', 'sesionesRecientes',
        ) + ['carreras' => $esAdmin ? Carrera::orderBy('nombre')->get() : collect()]);
    }

    // ── Resumen ───────────────────────────────────────────────────────

    public function verResumen(int $id): void
    {
        $this->resumenClaseId = $id;
        $this->showResumen    = true;
    }

    public function cerrarResumen(): void
    {
        $this->showResumen    = false;
        $this->resumenClaseId = null;
    }

    // ── CRUD (solo admin) ─────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->reset(['nombre', 'descripcion', 'carreraId', 'editingId']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $clase = Clase::where('usuario_id', auth()->id())->findOrFail($id);
        $this->editingId   = $id;
        $this->nombre      = $clase->nombre;
        $this->descripcion = $clase->descripcion ?? '';
        $this->carreraId   = $clase->carrera_id;
        $this->resetValidation();
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'carrera_id'  => $this->carreraId,
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
        $this->reset(['nombre', 'descripcion', 'carreraId', 'editingId']);
        $this->resetValidation();
    }
}
