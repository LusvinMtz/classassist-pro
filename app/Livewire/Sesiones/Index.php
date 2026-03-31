<?php

namespace App\Livewire\Sesiones;

use App\Models\Clase;
use App\Models\Sesion;
use Livewire\Component;

class Index extends Component
{
    public ?int $claseId = null;

    public function render()
    {
        $clases   = Clase::where('usuario_id', auth()->id())->get();
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

        return view('livewire.sesiones.index', compact('clases', 'sesiones', 'hoyTiene'));
    }

    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        }
    }

    public function crear(): void
    {
        if (!$this->claseId) return;

        Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

        // Evitar duplicado para el mismo día
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
        $sesion = Sesion::whereHas('clase', fn ($q) =>
            $q->where('usuario_id', auth()->id())
        )->findOrFail($sesionId);

        $sesion->update([
            'finalizada' => true,
            'token'      => null,
            'expiracion' => null,
        ]);
    }

    public function reabrir(int $sesionId): void
    {
        $sesion = Sesion::whereHas('clase', fn ($q) =>
            $q->where('usuario_id', auth()->id())
        )->findOrFail($sesionId);

        $sesion->update(['finalizada' => false]);
    }

    public function eliminar(int $sesionId): void
    {
        $sesion = Sesion::whereHas('clase', fn ($q) =>
            $q->where('usuario_id', auth()->id())
        )->withCount('asistencias')->findOrFail($sesionId);

        if ($sesion->asistencias_count > 0) return;

        $sesion->delete();
    }
}
