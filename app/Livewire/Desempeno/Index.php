<?php

namespace App\Livewire\Desempeno;

use App\Models\Clase;
use App\Models\Sesion;
use App\Models\Asistencia;
use App\Models\Participacion;
use Livewire\Component;

class Index extends Component
{
    public ?int  $claseId  = null;
    public string $ordenar = 'asistencia'; // asistencia | participaciones | calificacion

    public function render()
    {
        $clases  = Clase::where('usuario_id', auth()->id())->get();
        $ranking = collect();
        $totalSesiones = 0;

        if ($this->claseId) {
            $clase = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

            $totalSesiones = Sesion::where('clase_id', $this->claseId)->count();

            $estudiantes = $clase->estudiantes()->orderBy('nombre')->get();

            $sesionIds = Sesion::where('clase_id', $this->claseId)->pluck('id');

            // Asistencias por estudiante
            $asistenciasPor = Asistencia::whereIn('sesion_id', $sesionIds)
                ->selectRaw('estudiante_id, COUNT(*) as total')
                ->groupBy('estudiante_id')
                ->pluck('total', 'estudiante_id');

            // Participaciones y promedio por estudiante
            $participacionesPor = Participacion::whereIn('sesion_id', $sesionIds)
                ->selectRaw('estudiante_id, COUNT(*) as total, AVG(calificacion) as promedio')
                ->groupBy('estudiante_id')
                ->get()
                ->keyBy('estudiante_id');

            $ranking = $estudiantes->map(function ($e) use ($asistenciasPor, $participacionesPor, $totalSesiones) {
                $asistencias      = $asistenciasPor[$e->id] ?? 0;
                $pct              = $totalSesiones > 0 ? round($asistencias / $totalSesiones * 100) : 0;
                $part             = $participacionesPor[$e->id] ?? null;
                $numParticipaciones = $part ? $part->total : 0;
                $promedio         = $part && $part->promedio !== null ? round($part->promedio, 1) : null;

                return [
                    'id'               => $e->id,
                    'nombre'           => $e->nombre,
                    'carnet'           => $e->carnet,
                    'asistencias'      => $asistencias,
                    'pct_asistencia'   => $pct,
                    'participaciones'  => $numParticipaciones,
                    'promedio'         => $promedio,
                ];
            });

            $ranking = match ($this->ordenar) {
                'participaciones' => $ranking->sortByDesc('participaciones'),
                'calificacion'    => $ranking->sortByDesc(fn ($e) => $e['promedio'] ?? -1),
                default           => $ranking->sortByDesc('pct_asistencia'),
            };

            $ranking = $ranking->values();
        }

        return view('livewire.desempeno.index', compact('clases', 'ranking', 'totalSesiones'));
    }

    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        }
    }
}
