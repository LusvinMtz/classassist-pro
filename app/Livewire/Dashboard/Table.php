<?php

namespace App\Livewire\Dashboard;

use App\Models\Clase;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Table extends Component
{
    public ?int $claseId = null;
    public string $periodo = '10';

    public function mount(): void
    {
        $primera = Clase::where('usuario_id', auth()->id())
            ->orderBy('nombre')->first();
        if ($primera) {
            $this->claseId = $primera->id;
        }
    }

    public function updatedClaseId(): void
    {
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    public function updatedPeriodo(): void
    {
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    // ─── KPIs globales ────────────────────────────────────────────────────────

    private function computeKpis(): array
    {
        $userId   = auth()->id();
        $claseIds = Clase::where('usuario_id', $userId)->pluck('id');

        $totalClases = $claseIds->count();

        $totalEstudiantes = DB::table('clase_estudiante')
            ->whereIn('clase_id', $claseIds)
            ->distinct('estudiante_id')
            ->count('estudiante_id');

        $sesionesMes = Sesion::whereIn('clase_id', $claseIds)
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        $totalAsistidas = $totalPosibles = 0;

        if ($claseIds->isNotEmpty()) {
            $resumen = DB::table('sesion')
                ->whereIn('sesion.clase_id', $claseIds)
                ->select(
                    'sesion.id as sesion_id',
                    'sesion.clase_id',
                    DB::raw('(SELECT COUNT(*) FROM asistencia WHERE asistencia.sesion_id = sesion.id) as asistidas'),
                    DB::raw('(SELECT COUNT(*) FROM clase_estudiante WHERE clase_estudiante.clase_id = sesion.clase_id) as capacidad')
                )
                ->get();

            foreach ($resumen as $row) {
                if ($row->capacidad > 0) {
                    $totalAsistidas += $row->asistidas;
                    $totalPosibles  += $row->capacidad;
                }
            }
        }

        $avgAsistencia = $totalPosibles > 0
            ? round(($totalAsistidas / $totalPosibles) * 100, 1)
            : 0;

        return compact('totalClases', 'totalEstudiantes', 'sesionesMes', 'avgAsistencia');
    }

    // ─── Sesiones del periodo seleccionado ────────────────────────────────────

    private function sesionesDelPeriodo(): \Illuminate\Support\Collection
    {
        if (!$this->claseId) return collect();

        $q = Sesion::where('clase_id', $this->claseId)->orderByDesc('fecha');

        if ($this->periodo !== 'todo') {
            $q->limit((int) $this->periodo);
        }

        return $q->get()->sortBy('fecha')->values();
    }

    // ─── Builders de gráficas ─────────────────────────────────────────────────

    private function chartAsistenciaSesion(array $sesionIds, $sesiones): array
    {
        $totalEst = DB::table('clase_estudiante')
            ->where('clase_id', $this->claseId)->count();

        $asistPorSesion = DB::table('asistencia')
            ->whereIn('sesion_id', $sesionIds)
            ->select('sesion_id', DB::raw('COUNT(*) as total'))
            ->groupBy('sesion_id')
            ->pluck('total', 'sesion_id');

        return [
            'labels'    => $sesiones->map(fn($s) => $s->fecha->format('d/m'))->toArray(),
            'presentes' => $sesiones->map(fn($s) => (int)($asistPorSesion[$s->id] ?? 0))->toArray(),
            'ausentes'  => $sesiones->map(fn($s) => max(0, $totalEst - (int)($asistPorSesion[$s->id] ?? 0)))->toArray(),
            'totalEst'  => $totalEst,
        ];
    }

    private function chartAsistenciaEstudiante(array $sesionIds): array
    {
        $totalSesiones = count($sesionIds);
        if ($totalSesiones === 0 || !$this->claseId) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $estudiantes = DB::table('clase_estudiante')
            ->join('estudiante', 'estudiante.id', '=', 'clase_estudiante.estudiante_id')
            ->where('clase_estudiante.clase_id', $this->claseId)
            ->select('estudiante.id', 'estudiante.nombre')
            ->get();

        $asistCount = DB::table('asistencia')
            ->whereIn('sesion_id', $sesionIds)
            ->select('estudiante_id', DB::raw('COUNT(*) as total'))
            ->groupBy('estudiante_id')
            ->pluck('total', 'estudiante_id');

        $mapped = $estudiantes->map(fn($e) => [
            'nombre' => $e->nombre,
            'pct'    => round((($asistCount[$e->id] ?? 0) / $totalSesiones) * 100, 1),
        ])->sortByDesc('pct')->take(20)->values();

        return [
            'labels'  => $mapped->pluck('nombre')->toArray(),
            'data'    => $mapped->pluck('pct')->toArray(),
            'colores' => $mapped->map(fn($d) =>
                $d['pct'] >= 75 ? '#22c55e' : ($d['pct'] >= 50 ? '#f97316' : '#ef4444')
            )->toArray(),
        ];
    }

    private function chartParticipaciones(array $sesionIds, $sesiones): array
    {
        $partPorSesion = DB::table('participacion')
            ->whereIn('sesion_id', $sesionIds)
            ->select(
                'sesion_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('ROUND(AVG(calificacion), 1) as promedio')
            )
            ->groupBy('sesion_id')
            ->get()
            ->keyBy('sesion_id');

        return [
            'labels'   => $sesiones->map(fn($s) => $s->fecha->format('d/m'))->toArray(),
            'data'     => $sesiones->map(fn($s) => (int)($partPorSesion[$s->id]->total ?? 0))->toArray(),
            'promedio' => $sesiones->map(fn($s) => (float)($partPorSesion[$s->id]->promedio ?? 0))->toArray(),
        ];
    }

    private function chartCalificaciones(): array
    {
        if (!$this->claseId) {
            return ['labels' => [], 'data' => [], 'totales' => []];
        }

        $data = DB::table('calificacion')
            ->join('tipo_calificacion', 'tipo_calificacion.id', '=', 'calificacion.tipo_calificacion_id')
            ->where('calificacion.clase_id', $this->claseId)
            ->whereNotNull('calificacion.nota')
            ->select(
                'tipo_calificacion.nombre',
                DB::raw('ROUND(AVG(calificacion.nota), 2) as promedio'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('tipo_calificacion.id', 'tipo_calificacion.nombre')
            ->get();

        return [
            'labels'  => $data->pluck('nombre')->toArray(),
            'data'    => $data->map(fn($r) => (float) $r->promedio)->toArray(),
            'totales' => $data->pluck('total')->toArray(),
        ];
    }

    public function buildChartData(): array
    {
        if (!$this->claseId) {
            return [
                'asistenciaSesion'     => ['labels' => [], 'presentes' => [], 'ausentes' => [], 'totalEst' => 0],
                'asistenciaEstudiante' => ['labels' => [], 'data' => [], 'colores' => []],
                'participaciones'      => ['labels' => [], 'data' => [], 'promedio' => []],
                'calificaciones'       => ['labels' => [], 'data' => [], 'totales' => []],
            ];
        }

        $sesiones  = $this->sesionesDelPeriodo();
        $sesionIds = $sesiones->pluck('id')->toArray();

        return [
            'asistenciaSesion'     => $this->chartAsistenciaSesion($sesionIds, $sesiones),
            'asistenciaEstudiante' => $this->chartAsistenciaEstudiante($sesionIds),
            'participaciones'      => $this->chartParticipaciones($sesionIds, $sesiones),
            'calificaciones'       => $this->chartCalificaciones(),
        ];
    }

    public function render(): \Illuminate\View\View
    {
        $clases    = Clase::where('usuario_id', auth()->id())->orderBy('nombre')->get();
        $chartData = $this->buildChartData();

        $sesionesRecientes = $this->claseId
            ? Sesion::where('clase_id', $this->claseId)
                ->withCount('asistencias')
                ->withCount('participaciones')
                ->orderByDesc('fecha')
                ->limit(6)
                ->get()
            : collect();

        $totalEstClase = $this->claseId
            ? DB::table('clase_estudiante')->where('clase_id', $this->claseId)->count()
            : 0;

        return view('livewire.dashboard.table', [
            'kpis'              => $this->computeKpis(),
            'clases'            => $clases,
            'chartData'         => $chartData,
            'sesionesRecientes' => $sesionesRecientes,
            'totalEstClase'     => $totalEstClase,
            'claseNombre'       => $this->claseId ? $clases->firstWhere('id', $this->claseId)?->nombre : null,
        ]);
    }
}
