<?php

namespace App\Livewire\Grupos;

use App\Models\Clase;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Sesion;
use Livewire\Component;

class Index extends Component
{
    public ?int  $claseId  = null;
    public ?int  $sesionId = null;
    public string $modo    = 'grupos';   // 'grupos' | 'tamano'
    public int   $cantidad = 4;
    public array $preview  = [];         // grupos generados pero no guardados aún
    public bool  $generado = false;

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $sesionActiva = $this->sesionActivaCatedratico();
            if ($sesionActiva) {
                $this->sesionId = $sesionActiva->id;
                $this->claseId  = $sesionActiva->clase_id;
            }
        }
    }

    private function sesionActivaCatedratico(): ?Sesion
    {
        $user = auth()->user();
        if ($user->isAdmin()) return null;
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Sesion::whereIn('clase_id', $ids)
            ->where('finalizada', false)
            ->whereDate('fecha', today())
            ->latest()
            ->first();
    }

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
        $user            = auth()->user();
        $esCatedratico   = !$user->isAdmin();
        $clases          = $this->queryClases()->orderBy('nombre')->get();
        $sesion          = $this->sesionId ? Sesion::find($this->sesionId) : null;
        $sinSesionActiva = $esCatedratico && !$sesion;
        $presentes = collect();
        $guardados = collect();

        if ($sesion) {
            $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->orderBy('nombre')->get();

            $guardados = Grupo::where('sesion_id', $this->sesionId)
                ->with('estudiantes')
                ->orderBy('id')
                ->get();
        }

        return view('livewire.grupos.index', compact(
            'clases', 'sesion', 'presentes', 'guardados',
            'esCatedratico', 'sinSesionActiva'
        ));
    }

    public function updatedClaseId(): void
    {
        if (!$this->claseId) {
            $this->sesionId = null;
            $this->resetGenerado();
            return;
        }

        $this->queryClases()->findOrFail($this->claseId);

        $sesion = Sesion::where('clase_id', $this->claseId)
            ->whereDate('fecha', today())
            ->where('finalizada', false)
            ->first();

        $this->sesionId = $sesion?->id;
        $this->resetGenerado();
    }

    public function generar(): void
    {
        $this->validate([
            'cantidad' => 'required|integer|min:2|max:50',
        ], [
            'cantidad.min' => 'El valor mínimo es 2.',
            'cantidad.max' => 'El valor máximo es 50.',
        ]);

        if (!$this->sesionId) return;

        $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
            $q->where('sesion_id', $this->sesionId)
        )->get();

        if ($presentes->isEmpty()) return;

        $total = $presentes->count();

        if ($this->modo === 'grupos') {
            $numGrupos = min($this->cantidad, $total);
        } else {
            $numGrupos = (int) ceil($total / max(1, $this->cantidad));
        }

        // Construir matriz de co-ocurrencia a partir del historial
        $coOcurrencia = $this->buildCoOccurrenceMatrix();

        // Convertir a array simple para el algoritmo
        $lista = $presentes->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()->toArray();

        // Generar la mejor asignación minimizando repeticiones
        $mejorGrupos = $this->optimizarGrupos($lista, $numGrupos, $coOcurrencia);

        $this->preview = [];
        foreach ($mejorGrupos as $i => $miembros) {
            $this->preview[] = [
                'nombre'   => 'Grupo ' . ($i + 1),
                'miembros' => $miembros,
            ];
        }

        $this->generado = true;
    }

    /**
     * Construye un mapa de cuántas veces cada par de estudiantes ha estado
     * en el mismo grupo en sesiones anteriores de esta clase.
     * Clave: "menorId-mayorId" → cantidad de veces juntos.
     */
    private function buildCoOccurrenceMatrix(): array
    {
        $sesion = Sesion::find($this->sesionId);
        if (!$sesion) return [];

        $sesionesAnteriores = Sesion::where('clase_id', $sesion->clase_id)
            ->where('id', '!=', $this->sesionId)
            ->pluck('id');

        if ($sesionesAnteriores->isEmpty()) return [];

        $grupos = Grupo::whereIn('sesion_id', $sesionesAnteriores)
            ->with('estudiantes:id')
            ->get();

        $matrix = [];
        foreach ($grupos as $grupo) {
            $ids = $grupo->estudiantes->pluck('id')->sort()->values()->toArray();
            for ($i = 0; $i < count($ids); $i++) {
                for ($j = $i + 1; $j < count($ids); $j++) {
                    $key = $ids[$i] . '-' . $ids[$j];
                    $matrix[$key] = ($matrix[$key] ?? 0) + 1;
                }
            }
        }

        return $matrix;
    }

    /** Puntuación de un par (cuántas veces han coincidido). */
    private function parScore(int $a, int $b, array $matrix): int
    {
        $key = min($a, $b) . '-' . max($a, $b);
        return $matrix[$key] ?? 0;
    }

    /** Puntuación total de una asignación (suma de co-ocurrencias en todos los grupos). */
    private function assignmentScore(array $grupos, array $matrix): int
    {
        $score = 0;
        foreach ($grupos as $grupo) {
            $ids = array_column($grupo, 'id');
            for ($i = 0; $i < count($ids); $i++) {
                for ($j = $i + 1; $j < count($ids); $j++) {
                    $score += $this->parScore($ids[$i], $ids[$j], $matrix);
                }
            }
        }
        return $score;
    }

    /**
     * Genera múltiples asignaciones aleatorias y aplica búsqueda local
     * para encontrar la que minimice las repeticiones de pares.
     */
    private function optimizarGrupos(array $lista, int $numGrupos, array $matrix): array
    {
        $total       = count($lista);
        $tamBase     = (int) ceil($total / $numGrupos);
        $mejorScore  = PHP_INT_MAX;
        $mejorGrupos = null;

        // 30 arranques aleatorios: quedarse con el mejor
        for ($intento = 0; $intento < 30; $intento++) {
            shuffle($lista);

            // Distribuir uniformemente
            $grupos = array_fill(0, $numGrupos, []);
            foreach ($lista as $idx => $estudiante) {
                $grupos[$idx % $numGrupos][] = $estudiante;
            }

            // Búsqueda local: intercambio de estudiantes entre grupos
            $grupos = $this->busquedaLocal($grupos, $matrix);

            $score = $this->assignmentScore($grupos, $matrix);
            if ($score < $mejorScore) {
                $mejorScore  = $score;
                $mejorGrupos = $grupos;
                if ($score === 0) break; // óptimo: ninguna repetición
            }
        }

        return $mejorGrupos ?? array_fill(0, $numGrupos, []);
    }

    /**
     * Mejora la asignación intentando intercambiar estudiantes de distintos
     * grupos cuando eso reduce la puntuación total.
     */
    private function busquedaLocal(array $grupos, array $matrix): array
    {
        $numGrupos = count($grupos);
        $mejorado  = true;

        while ($mejorado) {
            $mejorado = false;

            for ($g1 = 0; $g1 < $numGrupos - 1; $g1++) {
                for ($g2 = $g1 + 1; $g2 < $numGrupos; $g2++) {
                    for ($i = 0; $i < count($grupos[$g1]); $i++) {
                        for ($j = 0; $j < count($grupos[$g2]); $j++) {
                            $antes = $this->assignmentScore($grupos, $matrix);

                            // Intercambiar estudiante i de g1 con j de g2
                            [$grupos[$g1][$i], $grupos[$g2][$j]] = [$grupos[$g2][$j], $grupos[$g1][$i]];

                            $despues = $this->assignmentScore($grupos, $matrix);

                            if ($despues < $antes) {
                                $mejorado = true; // mantener el intercambio
                            } else {
                                // Revertir
                                [$grupos[$g1][$i], $grupos[$g2][$j]] = [$grupos[$g2][$j], $grupos[$g1][$i]];
                            }
                        }
                    }
                }
            }
        }

        return $grupos;
    }

    public function guardar(): void
    {
        if (!$this->sesionId || empty($this->preview)) return;

        // Elimina grupos anteriores de esta sesión
        Grupo::where('sesion_id', $this->sesionId)->delete();

        foreach ($this->preview as $g) {
            $grupo = Grupo::create([
                'sesion_id' => $this->sesionId,
                'nombre'    => $g['nombre'],
            ]);

            $grupo->estudiantes()->attach(
                collect($g['miembros'])->pluck('id')->toArray()
            );
        }

        $this->resetGenerado();
    }

    public function eliminarGrupos(): void
    {
        if (!$this->sesionId) return;
        Grupo::where('sesion_id', $this->sesionId)->delete();
        $this->resetGenerado();
    }

    private function resetGenerado(): void
    {
        $this->preview  = [];
        $this->generado = false;
    }
}
