<?php

namespace App\Livewire\Sesiones;

use App\Models\EstadisticaRuido;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Detalle extends Component
{
    public int $sesionId;

    public function mount(int $sesionId): void
    {
        $sesion = Sesion::findOrFail($sesionId);

        // Catedrático: solo puede ver sus propias sesiones
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
            $porUsuarioId = \App\Models\Clase::where('usuario_id', $user->id)->pluck('id');
            $ids          = $porPivot->merge($porUsuarioId)->unique();
            abort_unless($ids->contains($sesion->clase_id), 403);
        }

        $this->sesionId = $sesionId;
    }

    public function render(): \Illuminate\View\View
    {
        $sesion = Sesion::with([
            'clase.catedratico',
            'clase.carrera',
            'clase.estudiantes',
        ])->findOrFail($this->sesionId);

        $totalInscritos = $sesion->clase->estudiantes->count();

        // ── Asistencia ────────────────────────────────────────────────────────
        $asistencias = DB::table('asistencia')
            ->join('estudiante', 'estudiante.id', '=', 'asistencia.estudiante_id')
            ->where('asistencia.sesion_id', $this->sesionId)
            ->select('estudiante.id', 'estudiante.nombre', 'asistencia.fecha_hora', 'asistencia.selfie')
            ->orderBy('asistencia.fecha_hora')
            ->get();

        $presenteIds = $asistencias->pluck('id');

        $ausentes = $sesion->clase->estudiantes
            ->whereNotIn('id', $presenteIds)
            ->sortBy('nombre')
            ->values();

        $pctAsistencia = $totalInscritos > 0
            ? round(($asistencias->count() / $totalInscritos) * 100, 1)
            : 0;

        // ── Participaciones (Ruleta) ──────────────────────────────────────────
        $participaciones = DB::table('participacion')
            ->join('estudiante', 'estudiante.id', '=', 'participacion.estudiante_id')
            ->where('participacion.sesion_id', $this->sesionId)
            ->select(
                'estudiante.nombre',
                'participacion.calificacion',
                'participacion.comentario',
                'participacion.created_at',
            )
            ->orderBy('participacion.created_at')
            ->get();

        $promedioRuleta = $participaciones->whereNotNull('calificacion')->avg('calificacion');

        // ── Grupos ────────────────────────────────────────────────────────────
        $grupos = DB::table('grupo')
            ->where('sesion_id', $this->sesionId)
            ->select('id', 'nombre')
            ->orderBy('id')
            ->get()
            ->map(function ($g) {
                $g->miembros = DB::table('grupo_estudiante')
                    ->join('estudiante', 'estudiante.id', '=', 'grupo_estudiante.estudiante_id')
                    ->where('grupo_estudiante.grupo_id', $g->id)
                    ->pluck('estudiante.nombre');
                return $g;
            });

        // ── Ruido ─────────────────────────────────────────────────────────────
        $ruidoRegistros = EstadisticaRuido::where('sesion_id', $this->sesionId)
            ->orderBy('iniciado_en')
            ->get();

        $ruidoResumen = null;
        if ($ruidoRegistros->isNotEmpty()) {
            $ruidoResumen = (object) [
                'db_minimo'          => $ruidoRegistros->min('db_minimo'),
                'db_maximo'          => $ruidoRegistros->max('db_maximo'),
                'db_promedio'        => round($ruidoRegistros->avg('db_promedio'), 1),
                'total_alertas'      => $ruidoRegistros->sum('total_alertas'),
                'duracion_segundos'  => $ruidoRegistros->sum('duracion_segundos'),
                'nivel_predominante' => $ruidoRegistros->last()?->nivel_predominante,
                'sesiones'           => $ruidoRegistros->count(),
            ];
        }

        return view('livewire.sesiones.detalle', compact(
            'sesion', 'totalInscritos', 'pctAsistencia',
            'asistencias', 'ausentes',
            'participaciones', 'promedioRuleta',
            'grupos', 'ruidoResumen', 'ruidoRegistros',
        ));
    }
}
