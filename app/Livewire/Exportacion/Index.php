<?php

namespace App\Livewire\Exportacion;

use App\Exports\AsistenciaExport;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\Sesion;
use App\Models\Asistencia;
use App\Models\Participacion;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    public ?int $claseId = null;

    private function claseQuery()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::query();
        }
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
        $ids          = $porUsuarioId->merge($porPivot)->unique();
        return Clase::whereIn('id', $ids);
    }

    public function render()
    {
        $clases = $this->claseQuery()->get();
        $stats  = null;

        if ($this->claseId) {
            $clase     = $this->claseQuery()->findOrFail($this->claseId);
            $sesionIds = Sesion::where('clase_id', $this->claseId)->pluck('id');

            $stats = [
                'estudiantes'    => $clase->estudiantes()->count(),
                'sesiones'       => $sesionIds->count(),
                'asistencias'    => Asistencia::whereIn('sesion_id', $sesionIds)->count(),
                'participaciones'=> Participacion::whereIn('sesion_id', $sesionIds)->count(),
                'calificaciones' => Calificacion::where('clase_id', $this->claseId)->count(),
            ];
        }

        return view('livewire.exportacion.index', compact('clases', 'stats'));
    }

    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            $this->claseQuery()->findOrFail($this->claseId);
        }
    }

    public function exportar(): mixed
    {
        if (!$this->claseId) return null;

        $clase = $this->claseQuery()->findOrFail($this->claseId);

        $nombre = 'classassist_' . \Illuminate\Support\Str::slug($clase->nombre) . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AsistenciaExport($clase->id, $clase->nombre), $nombre);
    }
}
