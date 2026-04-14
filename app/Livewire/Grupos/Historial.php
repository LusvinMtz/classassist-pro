<?php

namespace App\Livewire\Grupos;

use App\Models\Actividad;
use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Historial extends Component
{
    // ── Catedrático: selector de clase ────────────────────────────────────────
    public ?int $claseId = null;

    // ── Admin: filtros cascada ────────────────────────────────────────────────
    public ?int   $filterSedeId      = null;
    public ?int   $filterCarreraId   = null;
    public ?int   $filterClaseId     = null;
    public string $filterCatedratico = '';

    // ── Watchers (admin cascade) ──────────────────────────────────────────────

    public function updatedFilterSedeId(): void
    {
        $this->filterCarreraId = null;
        $this->filterClaseId   = null;
    }

    public function updatedFilterCarreraId(): void
    {
        $this->filterClaseId = null;
    }

    public function updatedClaseId(): void
    {
        $user = auth()->user();
        if ($this->claseId && !$user->isAdmin()) {
            Clase::whereIn('id', $this->clasesCatedratico($user))->findOrFail($this->claseId);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function esAdmin(): bool
    {
        return auth()->user()->isAdmin();
    }

    private function clasesCatedratico($user): \Illuminate\Support\Collection
    {
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
        return $porUsuarioId->merge($porPivot)->unique();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        $user    = auth()->user();
        $esAdmin = $this->esAdmin();

        // ── Lista de clases para selector (catedrático) ───────────────────────
        $clases = $esAdmin
            ? collect()
            : Clase::whereIn('id', $this->clasesCatedratico($user))->orderBy('nombre')->get();

        // ── Filtros admin ─────────────────────────────────────────────────────
        $sedes    = collect();
        $carreras = collect();
        $clasesFilter = collect();

        if ($esAdmin) {
            $sedes = Sede::orderBy('nombre')->get();

            $carreras = $this->filterSedeId
                ? Carrera::whereHas('sedes', fn($q) => $q->where('sede.id', $this->filterSedeId))->orderBy('nombre')->get()
                : Carrera::orderBy('nombre')->get();

            if ($this->filterCarreraId || $this->filterSedeId) {
                if ($this->filterCarreraId) {
                    $clasesFilter = Clase::where('carrera_id', $this->filterCarreraId)->orderBy('nombre')->get();
                } else {
                    $carreraIds   = DB::table('sede_carrera')->where('sede_id', $this->filterSedeId)->pluck('carrera_id');
                    $clasesFilter = Clase::whereIn('carrera_id', $carreraIds)->orderBy('nombre')->get();
                }
            }
        }

        // ── Determinar la clase efectiva ──────────────────────────────────────
        // Admin usa filterClaseId; catedrático usa claseId
        $claseEfectivaId = $esAdmin ? $this->filterClaseId : $this->claseId;

        // ── Query de sesiones con grupos ──────────────────────────────────────
        $sesiones = collect();

        if ($esAdmin || $claseEfectivaId) {
            $q = Sesion::whereHas('grupos')
                ->with([
                    'grupos' => fn($q) => $q->with('estudiantes:id,nombre')->orderBy('id'),
                    'clase',
                ])
                ->orderByDesc('fecha')
                ->orderByDesc('id');

            if ($claseEfectivaId) {
                // Verificar acceso para catedrático
                if (!$esAdmin) {
                    Clase::whereIn('id', $this->clasesCatedratico($user))->findOrFail($claseEfectivaId);
                }
                $q->where('clase_id', $claseEfectivaId);
            } elseif ($esAdmin) {
                // Filtros de cascada admin sin clase específica
                if ($this->filterCarreraId) {
                    $q->whereHas('clase', fn($c) => $c->where('carrera_id', $this->filterCarreraId));
                } elseif ($this->filterSedeId) {
                    $carreraIds = DB::table('sede_carrera')->where('sede_id', $this->filterSedeId)->pluck('carrera_id');
                    $q->whereHas('clase', fn($c) => $c->whereIn('carrera_id', $carreraIds));
                }

                if ($this->filterCatedratico) {
                    $buscar = $this->filterCatedratico;
                    $q->whereHas('clase', function ($c) use ($buscar) {
                        $c->whereHas('catedratico', fn($u) => $u->where('nombre', 'like', "%{$buscar}%"))
                          ->orWhereHas('catedraticos', fn($u) => $u->where('nombre', 'like', "%{$buscar}%"));
                    });
                }

                // Sin ningún filtro: limitar a las últimas 50 sesiones para no sobrecargar
                if (!$this->filterSedeId && !$this->filterCarreraId && !$this->filterCatedratico) {
                    $q->limit(50);
                }
            } else {
                // catedrático sin clase seleccionada
                $q->whereIn('clase_id', $this->clasesCatedratico($user));
            }

            $sesiones = $q->get();
        }

        // ── Cargar actividades vinculadas a cada sesión ───────────────────────
        $sesionIds = $sesiones->pluck('id');
        $actividadesPorSesion = Actividad::whereIn('grupo_sesion_id', $sesionIds)
            ->get()
            ->groupBy('grupo_sesion_id');

        return view('livewire.grupos.historial', compact(
            'esAdmin', 'clases', 'sesiones',
            'sedes', 'carreras', 'clasesFilter',
            'actividadesPorSesion',
        ));
    }
}
