<?php

namespace App\Livewire\Sesiones;

use App\Models\Clase;
use App\Models\Sesion;
use Livewire\Component;

class Index extends Component
{
    public ?int $claseId = null;

    public function mount(): void
    {
        // Pre-seleccionar la primera clase disponible
        $primera = $this->queryClases()->orderBy('nombre')->first();
        if ($primera) {
            $this->claseId = $primera->id;
        }
    }

    /** Retorna un query builder limitado a las clases que el usuario puede gestionar. */
    private function queryClases(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::query();
        }
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Clase::whereIn('id', $ids);
    }

    /** Verifica que el claseId pertenezca al usuario; lanza 404 si no. */
    private function autorizarClase(int $claseId): Clase
    {
        return $this->queryClases()->findOrFail($claseId);
    }

    /** Para catedráticos: sesión activa (no finalizada) en cualquiera de sus clases. */
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

    public function render()
    {
        $clases   = $this->queryClases()->orderBy('nombre')->get();
        $sesiones = collect();
        $hoyTiene = false;

        if ($this->claseId) {
            $sesiones = Sesion::where('clase_id', $this->claseId)
                ->withCount(['asistencias', 'participaciones'])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->get();

            $hoyTiene = $sesiones->where('fecha', today())->isNotEmpty();
        }

        $sesionActiva = $this->sesionActivaCatedratico();

        return view('livewire.sesiones.index', compact('clases', 'sesiones', 'hoyTiene', 'sesionActiva'));
    }

    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            $this->autorizarClase($this->claseId);
        }
    }

    public function crear(): void
    {
        if (!$this->claseId) return;

        $this->autorizarClase($this->claseId);

        // Para catedráticos: bloquear si ya existe alguna sesión activa en cualquier clase
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $activa = $this->sesionActivaCatedratico();
            if ($activa) {
                $this->addError('sesion', 'Debes finalizar la sesión activa de "' . $activa->clase->nombre . '" antes de crear una nueva.');
                return;
            }
        }

        // Evitar duplicado para el mismo día en esta clase
        $existe = Sesion::where('clase_id', $this->claseId)
            ->whereDate('fecha', today())
            ->exists();

        if ($existe) return;

        Sesion::create([
            'clase_id'   => $this->claseId,
            'fecha'      => today(),
            'finalizada' => false,
        ]);
    }

    public function finalizar(int $sesionId): void
    {
        $sesion = Sesion::findOrFail($sesionId);
        $this->autorizarClase($sesion->clase_id);

        $sesion->update([
            'finalizada' => true,
            'token'      => null,
            'expiracion' => null,
        ]);
    }

    public function reabrir(int $sesionId): void
    {
        // Solo admin puede reabrir; catedráticos no pueden (evita romper la restricción)
        $user = auth()->user();
        if (!$user->isAdmin()) return;

        Sesion::findOrFail($sesionId)->update(['finalizada' => false]);
    }

    public function eliminar(int $sesionId): void
    {
        $sesion = Sesion::withCount('asistencias')->findOrFail($sesionId);

        // Verificar acceso
        $this->autorizarClase($sesion->clase_id);

        if ($sesion->asistencias_count > 0) return;

        $sesion->delete();
    }
}
