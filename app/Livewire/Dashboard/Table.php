<?php

namespace App\Livewire\Dashboard;

use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Sede;
use App\Models\Sesion;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Table extends Component
{
    // Filtros admin (cascada)
    public ?int $sedeId    = null;
    public ?int $carreraId = null;

    // Filtro compartido
    public ?int    $claseId    = null;
    public string  $fechaDesde = '';
    public string  $fechaHasta = '';

    // ─── Inicialización ──────────────────────────────────────────────────────

    public function mount(): void
    {
        // Default: todo el historial para no ocultar datos de años anteriores
        $this->fechaHasta = now()->format('Y-m-d');
        $this->fechaDesde = '';

        $primera = $this->queryClases()->orderBy('nombre')->first();
        if ($primera) {
            $this->claseId = $primera->id;
        }
    }

    // ─── Helpers de acceso ───────────────────────────────────────────────────

    private function esAdmin(): bool
    {
        return auth()->user()->isAdmin();
    }

    private function queryClases(): \Illuminate\Database\Eloquent\Builder
    {
        if ($this->esAdmin()) {
            $q = Clase::query();
            if ($this->carreraId) {
                $q->where('carrera_id', $this->carreraId);
            } elseif ($this->sedeId) {
                $carreraIds = DB::table('sede_carrera')
                    ->where('sede_id', $this->sedeId)
                    ->pluck('carrera_id');
                $q->whereIn('carrera_id', $carreraIds);
            }
            return $q;
        }

        // Catedrático: clases asignadas via pivot O donde es el usuario_id directo
        $user = auth()->user();
        $porPivot    = $user->clasesImpartidas()->pluck('clase.id');
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        $ids = $porPivot->merge($porUsuarioId)->unique();

        return Clase::whereIn('id', $ids);
    }

    // ─── Watchers ────────────────────────────────────────────────────────────

    public function updatedSedeId(): void
    {
        $this->carreraId = null;
        $this->claseId   = null;
        $primera = $this->queryClases()->orderBy('nombre')->first();
        $this->claseId = $primera?->id;
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    public function updatedCarreraId(): void
    {
        $this->claseId = null;
        $primera = $this->queryClases()->orderBy('nombre')->first();
        $this->claseId = $primera?->id;
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    public function updatedClaseId(): void
    {
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    public function updatedFechaDesde(): void
    {
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    public function updatedFechaHasta(): void
    {
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    /** Aplica un atajo de rango de fechas en un solo request */
    public function aplicarAtajo(string $desde, string $hasta): void
    {
        $this->fechaDesde = $desde;
        $this->fechaHasta = $hasta;
        $this->dispatch('dashboard-charts', data: $this->buildChartData());
    }

    // ─── KPIs ────────────────────────────────────────────────────────────────

    private function computeKpis(): array
    {
        $claseIds = $this->queryClases()->pluck('id');

        $totalClases = $claseIds->count();

        $totalEstudiantes = DB::table('clase_estudiante')
            ->whereIn('clase_id', $claseIds)
            ->distinct()
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

    // ─── Sesiones del rango de fechas ────────────────────────────────────────

    private function sesionesDelPeriodo(): \Illuminate\Support\Collection
    {
        if (!$this->claseId) return collect();

        $q = Sesion::where('clase_id', $this->claseId)->orderBy('fecha');

        if ($this->fechaDesde) {
            $q->whereDate('fecha', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $q->whereDate('fecha', '<=', $this->fechaHasta);
        }

        return $q->get();
    }

    // ─── Gráficas ────────────────────────────────────────────────────────────

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

    private function chartRankingParticipacion(array $sesionIds): array
    {
        if (empty($sesionIds) || !$this->claseId) {
            return ['labels' => [], 'data' => [], 'promedios' => []];
        }

        $rows = DB::table('participacion')
            ->join('estudiante', 'estudiante.id', '=', 'participacion.estudiante_id')
            ->whereIn('participacion.sesion_id', $sesionIds)
            ->select(
                'estudiante.nombre',
                DB::raw('COUNT(*) as total'),
                DB::raw('ROUND(AVG(calificacion), 1) as promedio_cal')
            )
            ->groupBy('estudiante.id', 'estudiante.nombre')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        return [
            'labels'    => $rows->pluck('nombre')->toArray(),
            'data'      => $rows->map(fn($r) => (int) $r->total)->toArray(),
            'promedios' => $rows->map(fn($r) => (float) $r->promedio_cal)->toArray(),
        ];
    }

    public function buildChartData(): array
    {
        if (!$this->claseId) {
            return [
                'asistenciaSesion'     => ['labels' => [], 'presentes' => [], 'ausentes' => [], 'totalEst' => 0],
                'asistenciaEstudiante' => ['labels' => [], 'data' => [], 'colores' => []],
                'participaciones'      => ['labels' => [], 'data' => [], 'promedio' => []],
                'rankingParticipacion' => ['labels' => [], 'data' => [], 'promedios' => []],
            ];
        }

        $sesiones  = $this->sesionesDelPeriodo();
        $sesionIds = $sesiones->pluck('id')->toArray();

        return [
            'asistenciaSesion'       => $this->chartAsistenciaSesion($sesionIds, $sesiones),
            'asistenciaEstudiante'   => $this->chartAsistenciaEstudiante($sesionIds),
            'participaciones'        => $this->chartParticipaciones($sesionIds, $sesiones),
            'rankingParticipacion'   => $this->chartRankingParticipacion($sesionIds),
        ];
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        $clases    = $this->queryClases()->orderBy('nombre')->get();
        $chartData = $this->buildChartData();

        // Datos para filtros admin en cascada
        $sedes    = $this->esAdmin() ? Sede::orderBy('nombre')->get() : collect();
        $carreras = $this->esAdmin()
            ? ($this->sedeId
                ? Carrera::whereHas('sedes', fn($q) => $q->where('sede.id', $this->sedeId))->orderBy('nombre')->get()
                : Carrera::orderBy('nombre')->get())
            : collect();

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
            'sedes'             => $sedes,
            'carreras'          => $carreras,
            'chartData'         => $chartData,
            'sesionesRecientes' => $sesionesRecientes,
            'totalEstClase'     => $totalEstClase,
            'claseNombre'       => $this->claseId ? $clases->firstWhere('id', $this->claseId)?->nombre : null,
            'esAdmin'           => $this->esAdmin(),
        ]);
    }
}
