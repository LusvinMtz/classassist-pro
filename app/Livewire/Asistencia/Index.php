<?php

namespace App\Livewire\Asistencia;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Sesion;
use Illuminate\Support\Str;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Index extends Component
{
    public ?int $claseId  = null;
    public ?int $sesionId = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            // Catedrático: auto-cargar la sesión activa
            $sesionActiva = $this->sesionActivaCatedratico();
            if ($sesionActiva) {
                $this->sesionId = $sesionActiva->id;
                $this->claseId  = $sesionActiva->clase_id;
            }
        }
    }

    /** Sesión activa (no finalizada) entre las clases asignadas al catedrático. */
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

    /** Query de clases según el rol del usuario. */
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
        $user             = auth()->user();
        $esCatedratico    = !$user->isAdmin();
        $clases           = $this->queryClases()->orderBy('nombre')->get();
        $sesion           = $this->sesionId ? Sesion::with('clase')->find($this->sesionId) : null;
        $qrSvg            = null;
        $qrUrl            = null;
        $asistentes       = collect();
        $ausentes         = collect();
        $totalEstudiantes = 0;
        $sinSesionActiva  = $esCatedratico && !$sesion;

        if ($sesion) {
            $asistentes = Asistencia::where('sesion_id', $sesion->id)
                ->with('estudiante')
                ->orderBy('fecha_hora')
                ->get();

            $todosLosEstudiantes = $sesion->clase->estudiantes()->orderBy('nombre')->get();
            $totalEstudiantes    = $todosLosEstudiantes->count();
            $presentesIds        = $asistentes->pluck('estudiante_id');
            $ausentes            = $todosLosEstudiantes->whereNotIn('id', $presentesIds);

            if ($sesion->token && $sesion->expiracion > now()) {
                $qrUrl = route('asistir', $sesion->token);
                $qrSvg = QrCode::format('svg')
                    ->size(260)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($qrUrl);
            }
        }

        return view('livewire.asistencia.index', compact(
            'clases', 'sesion', 'qrSvg', 'qrUrl',
            'asistentes', 'ausentes', 'totalEstudiantes',
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
            ->first();

        $this->sesionId = $sesion?->id;
    }

    public function generarQR(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if ($sesion->finalizada) return;

        $sesion->update([
            'token'      => Str::random(40),
            'expiracion' => now()->addMinutes(5),
        ]);
    }

    public function marcarManual(int $estudianteId): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if ($sesion->finalizada) return;

        Asistencia::firstOrCreate([
            'sesion_id'     => $this->sesionId,
            'estudiante_id' => $estudianteId,
        ]);
    }

    public function quitarAsistencia(int $asistenciaId): void
    {
        $asistencia = Asistencia::with('sesion')->findOrFail($asistenciaId);
        if ($asistencia->sesion->finalizada) return;

        $asistencia->delete();
    }
}
