<?php

namespace App\Livewire\PantallaClase;

use App\Models\Actividad;
use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\EstadisticaRuido;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Participacion;
use App\Models\Sesion;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Index extends Component
{
    public ?int   $claseId  = null;
    public ?int   $sesionId = null;
    public string $tab      = 'qr';

    public ?int   $ganadorId     = null;
    public string $ganadorNombre = '';
    public bool   $showModal     = false;

    // Grupos
    public string $modo               = 'grupos';
    public string $fuente             = 'todos'; // 'presentes' | 'todos'
    public int    $cantidad           = 4;
    public string $descripcionGrupos  = '';
    public array  $preview            = [];
    public bool   $generado           = false;

    // Modal actividad grupal (post-guardar grupos)
    public bool   $showModalActividad = false;
    public string $actividadNombre    = '';
    public float  $actividadPunteo    = 100.0;

    // Actividad de sesión para ruleta (se crea antes de girar)
    public ?int   $actSesionId            = null;
    public string $actSesionNombre        = '';
    public bool   $showModalCrearActSesion = false;

    #[Validate('nullable|numeric|min:0|max:100')]
    public ?string $calificacion = null;

    #[Validate('nullable|string|max:500')]
    public string $comentario = '';

    public function mount(): void
    {
        // Sesión pasada por query string desde el listado de sesiones
        if (request()->filled('sesionId')) {
            $sesion = Sesion::find((int) request()->query('sesionId'));
            if ($sesion) {
                $this->sesionId = $sesion->id;
                $this->claseId  = $sesion->clase_id;
                return;
            }
        }

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
        $sinSesionActiva = $esCatedratico && !$this->sesionId;
        $clases = $this->queryClases()->orderBy('nombre')->get();
        $sesion = $this->sesionId ? Sesion::with('clase')->find($this->sesionId) : null;

        $qrSvg            = null;
        $qrUrl            = null;
        $asistentes       = collect();
        $totalEstudiantes = 0;
        $todosInscritos   = collect();
        $presentes        = collect();
        $historial        = collect();
        $grupos           = collect();

        if ($sesion) {
            if ($sesion->token && $sesion->expiracion > now()) {
                $qrUrl = route('asistir', $sesion->token);
                $qrSvg = QrCode::format('svg')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($qrUrl);
            }

            $asistentes = Asistencia::where('sesion_id', $sesion->id)
                ->with('estudiante')
                ->orderBy('fecha_hora')
                ->get();

            $totalEstudiantes = $sesion->clase->estudiantes()->count();

            // Todos los inscritos con flag de si ya asistieron
            $presentesIds = $asistentes->pluck('estudiante_id')->toArray();
            $todosInscritos = $sesion->clase->estudiantes()
                ->orderBy('nombre')
                ->get()
                ->map(function ($est) use ($presentesIds, $asistentes) {
                    $est->ya_asistio = in_array($est->id, $presentesIds);
                    $est->hora_registro = $est->ya_asistio
                        ? $asistentes->firstWhere('estudiante_id', $est->id)?->fecha_hora
                        : null;
                    return $est;
                });

            $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->orderBy('nombre')->get();

            $historial = Participacion::where('sesion_id', $this->sesionId)
                ->with('estudiante')
                ->latest()
                ->get();

            $grupos = Grupo::where('sesion_id', $this->sesionId)
                ->with('estudiantes')
                ->orderBy('id')
                ->get();
        }

        return view('livewire.pantalla-clase.index', compact(
            'clases', 'sesion',
            'qrSvg', 'qrUrl', 'asistentes', 'totalEstudiantes',
            'todosInscritos', 'presentes', 'historial', 'grupos',
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
        $this->tab      = 'qr';
        $this->reset(['ganadorId', 'ganadorNombre']);
        $this->resetGenerado();
        $this->actSesionId     = null;
        $this->actSesionNombre = '';
    }

    /* ─── Sesión ────────────────────────────────────────────────────── */

    public function finalizarSesion(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        $sesion->update(['finalizada' => true, 'token' => null, 'expiracion' => null]);

        $this->redirect(route('sesiones.index'), navigate: true);
    }

    /* ─── Medidor de ruido ───────────────────────────────────────────── */

    public function guardarEstadisticasRuido(
        float  $dbMinimo,
        float  $dbMaximo,
        float  $dbPromedio,
        int    $totalAlertas,
        int    $umbralDb,
        int    $duracionSegundos,
        string $nivelPredominante,
        string $iniciadoEn,
        string $finalizadoEn
    ): void {
        if (!$this->sesionId || $duracionSegundos < 5) return;

        EstadisticaRuido::create([
            'sesion_id'          => $this->sesionId,
            'usuario_id'         => auth()->id(),
            'db_minimo'          => $dbMinimo,
            'db_maximo'          => $dbMaximo,
            'db_promedio'        => $dbPromedio,
            'total_alertas'      => $totalAlertas,
            'umbral_db'          => $umbralDb,
            'duracion_segundos'  => $duracionSegundos,
            'nivel_predominante' => $nivelPredominante,
            'iniciado_en'        => $iniciadoEn,
            'finalizado_en'      => $finalizadoEn,
        ]);
    }

    /* ─── Asistencia manual ──────────────────────────────────────────── */

    public function registrarManual(int $estudianteId): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if (!$sesion->esOperativa()) return;

        $yaRegistrado = Asistencia::where('sesion_id', $this->sesionId)
            ->where('estudiante_id', $estudianteId)
            ->exists();

        if ($yaRegistrado) return;

        Asistencia::create([
            'sesion_id'     => $this->sesionId,
            'estudiante_id' => $estudianteId,
            'selfie'        => null,
            'latitud'       => null,
            'longitud'      => null,
        ]);
    }

    public function quitarAsistencia(int $estudianteId): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if (!$sesion->esOperativa()) return;

        Asistencia::where('sesion_id', $this->sesionId)
            ->where('estudiante_id', $estudianteId)
            ->delete();
    }

    /* ─── QR ─────────────────────────────────────────────────────────── */

    public function generarQR(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if (!$sesion->esOperativa()) return;

        $sesion->update([
            'token'      => Str::random(40),
            'expiracion' => now()->addMinutes(5),
        ]);
    }

    /* ─── Ruleta ─────────────────────────────────────────────────────── */

    public function girar(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if (!$sesion->esOperativa()) return;

        $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
            $q->where('sesion_id', $this->sesionId)
        )->get();

        if ($presentes->isEmpty()) return;

        $ganador = $presentes->random();

        $this->ganadorId     = $ganador->id;
        $this->ganadorNombre = $ganador->nombre;

        $this->dispatch('iniciar-ruleta-pantalla',
            ganadorNombre: $ganador->nombre,
            nombres: $presentes->pluck('nombre')->shuffle()->values()->toArray(),
        );
    }

    public function seleccionarGanador(): void
    {
        $this->showModal = true;
    }

    public function guardarParticipacion(): void
    {
        $this->validate();

        if (!$this->ganadorId || !$this->sesionId) return;

        $calificacion = $this->calificacion !== null && $this->calificacion !== ''
            ? (float) $this->calificacion
            : null;

        Participacion::create([
            'sesion_id'     => $this->sesionId,
            'estudiante_id' => $this->ganadorId,
            'calificacion'  => $calificacion,
            'comentario'    => $this->comentario ?: null,
        ]);

        // Si hay actividad de sesión activa y se asignó calificación, guardar nota
        if ($this->actSesionId && $calificacion !== null) {
            \App\Models\ActividadNota::updateOrCreate(
                ['actividad_id' => $this->actSesionId, 'estudiante_id' => $this->ganadorId],
                ['nota' => round(min(100, max(0, $calificacion)), 2), 'grupo_id' => null]
            );
        }

        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function omitir(): void
    {
        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->reset(['calificacion', 'comentario']);
        $this->resetValidation();
    }

    public function abrirModalCrearActSesion(): void
    {
        $this->actSesionNombre        = '';
        $this->showModalCrearActSesion = true;
    }

    public function crearActSesion(): void
    {
        $this->validate([
            'actSesionNombre' => 'required|string|max:100',
        ], [
            'actSesionNombre.required' => 'El nombre de la actividad es obligatorio.',
        ]);

        if (!$this->claseId) return;

        $clase    = $this->queryClases()->findOrFail($this->claseId);
        $maxOrden = Actividad::where('clase_id', $clase->id)->max('orden') ?? 0;

        $actividad = Actividad::create([
            'clase_id'        => $clase->id,
            'grupo_sesion_id' => null,
            'nombre'          => $this->actSesionNombre,
            'punteo_max'      => 100,
            'orden'           => $maxOrden + 1,
        ]);

        $this->actSesionId             = $actividad->id;
        $this->actSesionNombre         = $actividad->nombre;
        $this->showModalCrearActSesion = false;
        $this->resetValidation(['actSesionNombre']);
    }

    public function limpiarActSesion(): void
    {
        $this->actSesionId     = null;
        $this->actSesionNombre = '';
    }

    /* ─── Grupos ─────────────────────────────────────────────────────── */

    public function generar(): void
    {
        $this->validate([
            'cantidad' => 'required|integer|min:2|max:50',
        ], [
            'cantidad.min' => 'El valor mínimo es 2.',
            'cantidad.max' => 'El valor máximo es 50.',
        ]);

        if (!$this->sesionId) return;

        if ($this->fuente === 'todos') {
            $sesion    = Sesion::with('clase.estudiantes')->find($this->sesionId);
            $candidatos = $sesion?->clase->estudiantes ?? collect();
        } else {
            $candidatos = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->get();
        }

        if ($candidatos->isEmpty()) return;

        $total = $candidatos->count();

        if ($this->modo === 'grupos') {
            $numGrupos = min($this->cantidad, $total);
        } else {
            $numGrupos = (int) ceil($total / max(1, $this->cantidad));
        }

        $coOcurrencia = $this->buildCoOccurrenceMatrix();
        $lista = $candidatos->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()->toArray();
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

    public function guardarGrupos(): void
    {
        if (!$this->sesionId || empty($this->preview)) return;

        Grupo::where('sesion_id', $this->sesionId)->delete();

        foreach ($this->preview as $g) {
            $grupo = Grupo::create([
                'sesion_id'   => $this->sesionId,
                'nombre'      => $g['nombre'],
                'descripcion' => $this->descripcionGrupos ?: null,
            ]);
            $grupo->estudiantes()->attach(
                collect($g['miembros'])->pluck('id')->toArray()
            );
        }

        $this->resetGenerado();
        $this->showModalActividad = true;
    }

    public function crearActividadGrupal(): void
    {
        $this->validate([
            'actividadNombre' => 'required|string|max:100',
            'actividadPunteo' => 'required|numeric|min:1|max:9999.99',
        ], [
            'actividadNombre.required' => 'El nombre de la actividad es obligatorio.',
            'actividadPunteo.min'      => 'El punteo máximo debe ser al menos 1.',
        ]);

        if (!$this->sesionId || !$this->claseId) return;

        $clase     = $this->queryClases()->findOrFail($this->claseId);
        $maxOrden  = Actividad::where('clase_id', $clase->id)->max('orden') ?? 0;

        Actividad::create([
            'clase_id'        => $clase->id,
            'grupo_sesion_id' => $this->sesionId,
            'nombre'          => $this->actividadNombre,
            'punteo_max'      => $this->actividadPunteo,
            'orden'           => $maxOrden + 1,
        ]);

        $this->omitirActividad();
    }

    public function omitirActividad(): void
    {
        $this->showModalActividad = false;
        $this->actividadNombre    = '';
        $this->actividadPunteo    = 100.0;
        $this->resetValidation(['actividadNombre', 'actividadPunteo']);
    }

    public function eliminarGrupos(): void
    {
        if (!$this->sesionId) return;
        Grupo::where('sesion_id', $this->sesionId)->delete();
        $this->resetGenerado();
    }

    public function updatedFuente(): void
    {
        $this->resetGenerado();
    }

    private function resetGenerado(): void
    {
        $this->preview           = [];
        $this->generado          = false;
        $this->descripcionGrupos = '';
    }

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

    private function parScore(int $a, int $b, array $matrix): int
    {
        $key = min($a, $b) . '-' . max($a, $b);
        return $matrix[$key] ?? 0;
    }

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

    private function optimizarGrupos(array $lista, int $numGrupos, array $matrix): array
    {
        $mejorScore  = PHP_INT_MAX;
        $mejorGrupos = null;

        for ($intento = 0; $intento < 30; $intento++) {
            shuffle($lista);
            $grupos = array_fill(0, $numGrupos, []);
            foreach ($lista as $idx => $estudiante) {
                $grupos[$idx % $numGrupos][] = $estudiante;
            }
            $grupos = $this->busquedaLocal($grupos, $matrix);
            $score  = $this->assignmentScore($grupos, $matrix);
            if ($score < $mejorScore) {
                $mejorScore  = $score;
                $mejorGrupos = $grupos;
                if ($score === 0) break;
            }
        }

        return $mejorGrupos ?? array_fill(0, $numGrupos, []);
    }

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
                            [$grupos[$g1][$i], $grupos[$g2][$j]] = [$grupos[$g2][$j], $grupos[$g1][$i]];
                            $despues = $this->assignmentScore($grupos, $matrix);
                            if ($despues < $antes) {
                                $mejorado = true;
                            } else {
                                [$grupos[$g1][$i], $grupos[$g2][$j]] = [$grupos[$g2][$j], $grupos[$g1][$i]];
                            }
                        }
                    }
                }
            }
        }

        return $grupos;
    }
}