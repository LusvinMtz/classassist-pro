<?php

namespace App\Livewire\Desempeno;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Participacion;
use App\Models\Sede;
use App\Models\Sesion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filtros admin
    public ?int $filterSede    = null;
    public ?int $filterCarrera = null;

    // Filtro compartido
    public ?int   $claseId  = null;
    public string $ordenar  = 'asistencia';
    public int    $pageRank = 1;   // paginación manual para ranking de estudiantes

    // ── Cascada admin ─────────────────────────────────────────────────────────

    public function updatedFilterSede(): void
    {
        $this->filterCarrera = null;
        $this->claseId       = null;
        $this->resetPage();
    }

    public function updatedFilterCarrera(): void
    {
        $this->claseId = null;
        $this->resetPage();
    }

    public function updatedClaseId(): void
    {
        $this->resetPage();
        $this->pageRank = 1;
        if ($this->claseId && !auth()->user()->isAdmin()) {
            $this->queryClasesCatedratico()->findOrFail($this->claseId);
        }
    }

    public function updatedOrdenar(): void
    {
        $this->pageRank = 1;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->renderAdmin();
        }

        return $this->renderCatedratico();
    }

    // ── Vista Admin ───────────────────────────────────────────────────────────

    private function renderAdmin()
    {
        $sedes    = Sede::orderBy('nombre')->get();
        $carreras = collect();

        if ($this->filterSede) {
            $carreras = Sede::find($this->filterSede)?->carreras()->orderBy('nombre')->get() ?? collect();
        } else {
            $carreras = Carrera::orderBy('nombre')->get();
        }

        // Query base de clases para el selector (sin paginar, solo para el <select>)
        $claseQuery = Clase::with('carrera', 'catedratico')->orderBy('nombre');
        if ($this->filterCarrera) {
            $claseQuery->where('carrera_id', $this->filterCarrera);
        } elseif ($this->filterSede) {
            $claseQuery->whereIn('carrera_id', $carreras->pluck('id'));
        }
        $clasesSelect = $claseQuery->get();

        // Si hay clase específica: ranking por estudiante paginado
        if ($this->claseId) {
            [$rankingTotal, $totalSesiones] = $this->buildRankingEstudiantes($this->claseId);
            $totalRank  = $rankingTotal->count();
            $ranking    = $rankingTotal->forPage($this->pageRank, 10)->values();
            $lastPage   = (int) ceil($totalRank / 10);

            return view('livewire.desempeno.index', [
                'esAdmin'       => true,
                'modoVista'     => 'estudiantes',
                'sedes'         => $sedes,
                'carreras'      => $carreras,
                'clases'        => $clasesSelect,
                'ranking'       => $ranking,
                'rankingTotal'  => $rankingTotal,
                'totalSesiones' => $totalSesiones,
                'claseActual'   => Clase::with('carrera', 'catedratico')->find($this->claseId),
                'pageRank'      => $this->pageRank,
                'lastPageRank'  => $lastPage,
                'resumenClases' => null,
                'clasesPaginator' => null,
            ]);
        }

        // Sin clase: vista agregada por clase con Eloquent paginate
        $clasesPaginator = (clone $claseQuery)->paginate(5);
        $resumenClases   = $this->buildResumenClases(collect($clasesPaginator->items()));

        return view('livewire.desempeno.index', [
            'esAdmin'         => true,
            'modoVista'       => 'clases',
            'sedes'           => $sedes,
            'carreras'        => $carreras,
            'clases'          => $clasesSelect,
            'resumenClases'   => $resumenClases,
            'clasesPaginator' => $clasesPaginator,
            'ranking'         => collect(),
            'rankingTotal'    => collect(),
            'totalSesiones'   => 0,
            'claseActual'     => null,
            'pageRank'        => 1,
            'lastPageRank'    => 1,
        ]);
    }

    // ── Vista Catedrático ─────────────────────────────────────────────────────

    private function renderCatedratico()
    {
        $clases        = $this->queryClasesCatedratico()->orderBy('nombre')->get();
        $ranking       = collect();
        $rankingTotal  = collect();
        $totalSesiones = 0;
        $lastPage      = 1;

        if ($this->claseId) {
            [$rankingTotal, $totalSesiones] = $this->buildRankingEstudiantes($this->claseId);
            $lastPage = (int) ceil($rankingTotal->count() / 10);
            $ranking  = $rankingTotal->forPage($this->pageRank, 10)->values();
        }

        return view('livewire.desempeno.index', [
            'esAdmin'         => false,
            'modoVista'       => 'estudiantes',
            'sedes'           => collect(),
            'carreras'        => collect(),
            'clases'          => $clases,
            'ranking'         => $ranking,
            'rankingTotal'    => $rankingTotal,
            'totalSesiones'   => $totalSesiones,
            'resumenClases'   => collect(),
            'clasesPaginator' => null,
            'pageRank'        => $this->pageRank,
            'lastPageRank'    => $lastPage,
            'claseActual'     => null,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function queryClasesCatedratico(): \Illuminate\Database\Eloquent\Builder
    {
        $user         = auth()->user();
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
        $ids          = $porUsuarioId->merge($porPivot)->unique();

        return Clase::whereIn('id', $ids);
    }

    private function buildRankingEstudiantes(int $claseId): array
    {
        $clase         = Clase::findOrFail($claseId);
        $totalSesiones = Sesion::where('clase_id', $claseId)->count();
        $estudiantes   = $clase->estudiantes()->orderBy('nombre')->get();
        $sesionIds     = Sesion::where('clase_id', $claseId)->pluck('id');

        $asistenciasPor = Asistencia::whereIn('sesion_id', $sesionIds)
            ->selectRaw('estudiante_id, COUNT(*) as total')
            ->groupBy('estudiante_id')
            ->pluck('total', 'estudiante_id');

        $participacionesPor = Participacion::whereIn('sesion_id', $sesionIds)
            ->selectRaw('estudiante_id, COUNT(*) as total, AVG(calificacion) as promedio')
            ->groupBy('estudiante_id')
            ->get()
            ->keyBy('estudiante_id');

        $notasPor = Calificacion::where('clase_id', $claseId)
            ->selectRaw('estudiante_id, AVG(nota) as promedio_notas, COUNT(*) as total_notas')
            ->groupBy('estudiante_id')
            ->get()
            ->keyBy('estudiante_id');

        $ranking = $estudiantes->map(function ($e) use ($asistenciasPor, $participacionesPor, $notasPor, $totalSesiones) {
            $asistencias        = $asistenciasPor[$e->id] ?? 0;
            $pct                = $totalSesiones > 0 ? round($asistencias / $totalSesiones * 100) : 0;
            $part               = $participacionesPor[$e->id] ?? null;
            $numParticipaciones = $part ? $part->total : 0;
            $promedio           = $part && $part->promedio !== null ? round($part->promedio, 1) : null;
            $notasRec           = $notasPor[$e->id] ?? null;
            $promNotas          = $notasRec ? round((float) $notasRec->promedio_notas, 1) : null;

            return [
                'id'              => $e->id,
                'nombre'          => $e->nombre,
                'carnet'          => $e->carnet,
                'asistencias'     => $asistencias,
                'pct_asistencia'  => $pct,
                'participaciones' => $numParticipaciones,
                'promedio'        => $promedio,
                'prom_notas'      => $promNotas,
            ];
        });

        $ranking = match ($this->ordenar) {
            'participaciones' => $ranking->sortByDesc('participaciones'),
            'calificacion'    => $ranking->sortByDesc(fn ($e) => $e['promedio'] ?? -1),
            'notas'           => $ranking->sortByDesc(fn ($e) => $e['prom_notas'] ?? -1),
            default           => $ranking->sortByDesc('pct_asistencia'),
        };

        return [$ranking->take(10)->values(), $totalSesiones];
    }

    private function buildResumenClases(Collection $clases): Collection
    {
        if ($clases->isEmpty()) return collect();

        $claseIds = $clases->pluck('id')->all();

        // Estudiantes por clase (una query)
        $estudiantesPor = DB::table('asignacion')
            ->whereIn('clase_id', $claseIds)
            ->selectRaw('clase_id, COUNT(*) as total')
            ->groupBy('clase_id')
            ->pluck('total', 'clase_id');

        // Sesiones por clase (una query) + mapeo claseId→sesionIds
        $sesiones = DB::table('sesion')
            ->whereIn('clase_id', $claseIds)
            ->selectRaw('id, clase_id')
            ->get();

        $sesionesPorClase = $sesiones->groupBy('clase_id')
            ->map(fn($g) => $g->count());

        $sesionIds = $sesiones->pluck('id')->all();

        // Asistencias por sesión → agregado por clase (una query)
        $asistenciasPorClase = collect();
        if (!empty($sesionIds)) {
            $asistenciasPorClase = DB::table('asistencia')
                ->join('sesion', 'sesion.id', '=', 'asistencia.sesion_id')
                ->whereIn('asistencia.sesion_id', $sesionIds)
                ->selectRaw('sesion.clase_id, COUNT(*) as total')
                ->groupBy('sesion.clase_id')
                ->pluck('total', 'clase_id');
        }

        // Participaciones por clase (una query)
        $participacionesPorClase = collect();
        if (!empty($sesionIds)) {
            $participacionesPorClase = DB::table('participacion')
                ->join('sesion', 'sesion.id', '=', 'participacion.sesion_id')
                ->whereIn('participacion.sesion_id', $sesionIds)
                ->selectRaw('sesion.clase_id, COUNT(*) as total')
                ->groupBy('sesion.clase_id')
                ->pluck('total', 'clase_id');
        }

        // Notas promedio por clase (una query)
        $notasPorClase = DB::table('calificacion')
            ->whereIn('clase_id', $claseIds)
            ->selectRaw('clase_id, AVG(nota) as promedio')
            ->groupBy('clase_id')
            ->pluck('promedio', 'clase_id');

        return $clases->map(function ($clase) use (
            $estudiantesPor, $sesionesPorClase, $asistenciasPorClase,
            $participacionesPorClase, $notasPorClase
        ) {
            $totalEstudiantes = $estudiantesPor[$clase->id] ?? 0;
            $totalSesiones    = $sesionesPorClase[$clase->id] ?? 0;
            $totalAsistencias = $asistenciasPorClase[$clase->id] ?? 0;
            $pctAsistencia    = ($totalEstudiantes > 0 && $totalSesiones > 0)
                ? round($totalAsistencias / ($totalEstudiantes * $totalSesiones) * 100, 1)
                : 0;
            $promedio = $notasPorClase[$clase->id] ?? null;

            return [
                'id'                    => $clase->id,
                'nombre'                => $clase->nombre,
                'carrera'               => $clase->carrera?->nombre ?? '—',
                'catedratico'           => $clase->catedratico?->nombre ?? '—',
                'total_estudiantes'     => $totalEstudiantes,
                'total_sesiones'        => $totalSesiones,
                'pct_asistencia'        => $pctAsistencia,
                'total_participaciones' => $participacionesPorClase[$clase->id] ?? 0,
                'promedio_notas'        => $promedio ? round($promedio, 1) : null,
            ];
        })->sortByDesc('pct_asistencia')->values();
    }
}
