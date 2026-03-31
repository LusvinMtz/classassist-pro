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

    public function render()
    {
        $clases           = Clase::where('usuario_id', auth()->id())->get();
        $sesion           = $this->sesionId ? Sesion::with('clase')->find($this->sesionId) : null;
        $qrSvg            = null;
        $qrUrl            = null;
        $asistentes       = collect();
        $ausentes         = collect();
        $totalEstudiantes = 0;

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
            'asistentes', 'ausentes', 'totalEstudiantes'
        ));
    }

    public function updatedClaseId(): void
    {
        if (!$this->claseId) {
            $this->sesionId = null;
            return;
        }

        Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

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
