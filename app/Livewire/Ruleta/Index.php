<?php

namespace App\Livewire\Ruleta;

use App\Models\Clase;
use App\Models\Estudiante;
use App\Models\Participacion;
use App\Models\Sesion;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Index extends Component
{
    public ?int    $claseId       = null;
    public ?int    $sesionId      = null;
    public ?int    $ganadorId     = null;
    public string  $ganadorNombre = '';
    public bool    $showModal     = false;

    #[Validate('nullable|numeric|min:0|max:10')]
    public ?string $calificacion = null;

    #[Validate('nullable|string|max:500')]
    public string $comentario = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $sesionActiva = $this->sesionActivaCatedratico();
            if ($sesionActiva) {
                $this->sesionId = $sesionActiva->id;
                $this->claseId  = $sesionActiva->clase_id;
            }
        }
    }

    private function sesionActivaCatedratico(): ?Sesion
    {
        $user = auth()->user();
        if ($user->isAdmin()) return null;
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Sesion::whereIn('clase_id', $ids)
            ->where('finalizada', false)
            ->latest()
            ->first();
    }

    private function queryClases(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::query();
        }
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Clase::whereIn('id', $ids);
    }

    public function render()
    {
        $user          = auth()->user();
        $esCatedratico = !$user->isAdmin();
        $clases        = $this->queryClases()->orderBy('nombre')->get();
        $sesion        = $this->sesionId ? Sesion::find($this->sesionId) : null;
        $presentes     = collect();
        $historial     = collect();
        $sinSesionActiva = $esCatedratico && !$sesion;

        if ($sesion) {
            $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->orderBy('nombre')->get();

            $historial = Participacion::where('sesion_id', $this->sesionId)
                ->with('estudiante')
                ->latest()
                ->get();
        }

        return view('livewire.ruleta.index', compact(
            'clases', 'sesion', 'presentes', 'historial',
            'esCatedratico', 'sinSesionActiva'
        ));
    }

    public function updatedClaseId(): void
    {
        if (!$this->claseId) {
            $this->sesionId = null;
            return;
        }

        $this->queryClases()->findOrFail($this->claseId);

        $sesion = Sesion::where('clase_id', $this->claseId)
            ->whereDate('fecha', today())
            ->where('finalizada', false)
            ->first();

        $this->sesionId = $sesion?->id;
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function girar(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if ($sesion->finalizada) return;

        $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
            $q->where('sesion_id', $this->sesionId)
        )->get();

        if ($presentes->isEmpty()) return;

        $ganador = $presentes->random();

        $this->ganadorId     = $ganador->id;
        $this->ganadorNombre = $ganador->nombre;

        $this->dispatch('iniciar-ruleta',
            ganadorNombre: $ganador->nombre,
            nombres: $presentes->pluck('nombre')->shuffle()->values()->toArray(),
        );
    }

    public function seleccionarGanador(): void
    {
        $this->showModal = true;
    }

    public function guardarParticipacion(): void
    {
        $this->validate();

        if (!$this->ganadorId || !$this->sesionId) return;

        Participacion::create([
            'sesion_id'     => $this->sesionId,
            'estudiante_id' => $this->ganadorId,
            'calificacion'  => $this->calificacion !== '' ? $this->calificacion : null,
            'comentario'    => $this->comentario ?: null,
        ]);

        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function omitir(): void
    {
        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->reset(['calificacion', 'comentario']);
        $this->resetValidation();
    }
}
