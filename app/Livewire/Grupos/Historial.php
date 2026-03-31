<?php

namespace App\Livewire\Grupos;

use App\Models\Clase;
use App\Models\Grupo;
use App\Models\Sesion;
use Livewire\Component;

class Historial extends Component
{
    public ?int $claseId = null;

    public function render()
    {
        $clases   = Clase::where('usuario_id', auth()->id())->get();
        $sesiones = collect();

        if ($this->claseId) {
            Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

            $sesiones = Sesion::where('clase_id', $this->claseId)
                ->whereHas('grupos')
                ->with(['grupos' => fn ($q) => $q->with('estudiantes')->orderBy('id')])
                ->orderByDesc('fecha')
                ->get();
        }

        return view('livewire.grupos.historial', compact('clases', 'sesiones'));
    }

    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        }
    }
}
