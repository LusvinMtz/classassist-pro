<?php

namespace App\Livewire\Sesiones;

use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Sede;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{
    // ── Modal: nueva sesión ───────────────────────────────────────────────────
    public bool $showModal      = false;
    public ?int $modalSedeId    = null;
    public ?int $modalCarreraId = null;
    public ?int $modalClaseId   = null;

    // ── Filtros tabla (admin) ─────────────────────────────────────────────────
    public ?int   $filterSedeId    = null;
    public ?int   $filterCarreraId = null;
    public ?int   $filterClaseId   = null;
    public string $filterCatedratico = '';

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function esAdmin(): bool
    {
        return auth()->user()->isAdmin();
    }

    /** IDs de todas las clases del catedrático (owner + pivot). */
    private function todasClasesIdsCatedratico(): \Illuminate\Support\Collection
    {
        $user         = auth()->user();
        $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        return $porPivot->merge($porUsuarioId)->unique();
    }

    /**
     * Sesión abierta de HOY del catedrático.
     * Las sesiones de días pasados se auto-finalizan por el scheduler.
     */
    private function sesionActivaCatedratico(): ?Sesion
    {
        if ($this->esAdmin()) return null;

        return Sesion::whereIn('clase_id', $this->todasClasesIdsCatedratico())
            ->where('finalizada', false)
            ->whereDate('fecha', today())
            ->with('clase')
            ->latest()
            ->first();
    }

    /** Clases disponibles para seleccionar en el modal (respeta filtros del modal). */
    private function queryClasesModal(): \Illuminate\Database\Eloquent\Builder
    {
        if ($this->esAdmin()) {
            $q = Clase::query();
            if ($this->modalCarreraId) {
                $q->where('carrera_id', $this->modalCarreraId);
            } elseif ($this->modalSedeId) {
                $carreraIds = DB::table('sede_carrera')
                    ->where('sede_id', $this->modalSedeId)
                    ->pluck('carrera_id');
                $q->whereIn('carrera_id', $carreraIds);
            }
            return $q;
        }

        return Clase::whereIn('id', $this->todasClasesIdsCatedratico());
    }

    /** Query principal de la tabla de sesiones, filtrado por rol. */
    private function querySesiones(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Sesion::with(['clase.catedratico', 'clase.carrera'])
            ->withCount(['asistencias', 'participaciones'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($this->esAdmin()) {
            if ($this->filterClaseId) {
                $q->where('clase_id', $this->filterClaseId);
            } elseif ($this->filterCarreraId) {
                $q->whereHas('clase', fn($c) => $c->where('carrera_id', $this->filterCarreraId));
            } elseif ($this->filterSedeId) {
                $carreraIds = DB::table('sede_carrera')
                    ->where('sede_id', $this->filterSedeId)
                    ->pluck('carrera_id');
                $q->whereHas('clase', fn($c) => $c->whereIn('carrera_id', $carreraIds));
            }
            if ($this->filterCatedratico) {
                $q->whereHas('clase.catedratico', fn($u) =>
                    $u->where('nombre', 'like', '%' . $this->filterCatedratico . '%')
                );
            }
        } else {
            $q->whereIn('clase_id', $this->todasClasesIdsCatedratico());
        }

        return $q;
    }

    // ─── Watchers modal ───────────────────────────────────────────────────────

    public function updatedModalSedeId(): void
    {
        $this->modalCarreraId = null;
        $this->modalClaseId   = null;
    }

    public function updatedModalCarreraId(): void
    {
        $this->modalClaseId = null;
    }

    // ─── Watchers filtros ─────────────────────────────────────────────────────

    public function updatedFilterSedeId(): void
    {
        $this->filterCarreraId = null;
        $this->filterClaseId   = null;
    }

    public function updatedFilterCarreraId(): void
    {
        $this->filterClaseId = null;
    }

    // ─── Acciones ────────────────────────────────────────────────────────────

    public function abrirModal(): void
    {
        $this->showModal      = true;
        $this->modalSedeId    = null;
        $this->modalCarreraId = null;
        $this->modalClaseId   = null;
        $this->resetErrorBag();
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
    }

    public function crear(): void
    {
        if (!$this->modalClaseId) {
            $this->addError('modalClaseId', 'Debes seleccionar un curso.');
            return;
        }

        $clase = $this->queryClasesModal()->find($this->modalClaseId);
        if (!$clase) {
            $this->addError('modalClaseId', 'Clase no autorizada.');
            return;
        }

        // Catedrático: bloquear si ya tiene sesión activa hoy
        if (!$this->esAdmin()) {
            $activa = $this->sesionActivaCatedratico();
            if ($activa) {
                $this->addError('modalClaseId',
                    'Ya tienes una sesión activa en "' . $activa->clase->nombre . '". Finalízala primero.'
                );
                return;
            }
        }

        // Evitar duplicado del mismo día en esta clase
        if (Sesion::where('clase_id', $this->modalClaseId)->whereDate('fecha', today())->exists()) {
            $this->addError('modalClaseId', 'Ya existe una sesión para hoy en este curso.');
            return;
        }

        Sesion::create([
            'clase_id'   => $this->modalClaseId,
            'fecha'      => today(),
            'finalizada' => false,
        ]);

        $this->showModal = false;
    }

    public function finalizar(int $sesionId): void
    {
        $sesion = Sesion::findOrFail($sesionId);

        if (!$this->esAdmin()) {
            abort_unless($this->todasClasesIdsCatedratico()->contains($sesion->clase_id), 403);
        }

        $sesion->update(['finalizada' => true, 'token' => null, 'expiracion' => null]);
    }

    public function reabrir(int $sesionId): void
    {
        if (!$this->esAdmin()) return;

        $sesion = Sesion::findOrFail($sesionId);
        // Solo se permite reabrir sesiones de hoy
        if ($sesion->fecha->isToday()) {
            $sesion->update(['finalizada' => false]);
        }
    }

    public function eliminar(int $sesionId): void
    {
        $sesion = Sesion::withCount('asistencias')->findOrFail($sesionId);

        if (!$this->esAdmin()) {
            abort_unless($this->todasClasesIdsCatedratico()->contains($sesion->clase_id), 403);
        }

        if ($sesion->asistencias_count > 0) return;
        $sesion->delete();
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        $esAdmin      = $this->esAdmin();
        $sesiones     = $this->querySesiones()->get();
        $sesionActiva = $this->sesionActivaCatedratico();

        // ── Modal ─────────────────────────────────────────────────────────────
        $clasesModal   = $this->queryClasesModal()->orderBy('nombre')->get();
        $sedesModal    = $esAdmin ? Sede::orderBy('nombre')->get() : collect();
        $carrerasModal = $esAdmin
            ? ($this->modalSedeId
                ? Carrera::whereHas('sedes', fn($q) => $q->where('sede.id', $this->modalSedeId))->orderBy('nombre')->get()
                : Carrera::orderBy('nombre')->get())
            : collect();

        // ── Filtros tabla admin ───────────────────────────────────────────────
        $sedes    = $esAdmin ? Sede::orderBy('nombre')->get() : collect();
        $carreras = $esAdmin
            ? ($this->filterSedeId
                ? Carrera::whereHas('sedes', fn($q) => $q->where('sede.id', $this->filterSedeId))->orderBy('nombre')->get()
                : Carrera::orderBy('nombre')->get())
            : collect();

        $clasesFilter = collect();
        if ($esAdmin && ($this->filterCarreraId || $this->filterSedeId)) {
            if ($this->filterCarreraId) {
                $clasesFilter = Clase::where('carrera_id', $this->filterCarreraId)->orderBy('nombre')->get();
            } else {
                $carreraIds   = DB::table('sede_carrera')->where('sede_id', $this->filterSedeId)->pluck('carrera_id');
                $clasesFilter = Clase::whereIn('carrera_id', $carreraIds)->orderBy('nombre')->get();
            }
        }

        return view('livewire.sesiones.index', compact(
            'esAdmin', 'sesiones', 'sesionActiva',
            'clasesModal', 'sedesModal', 'carrerasModal',
            'sedes', 'carreras', 'clasesFilter',
        ));
    }
}
