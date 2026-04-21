<?php

namespace App\Livewire\Calificaciones;

use App\Exports\ActividadesPlantillaExport;
use App\Imports\ActividadesImport;
use App\Models\Actividad;
use App\Models\ActividadNota;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\Grupo;
use App\Models\Participacion;
use App\Models\TipoCalificacion;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithFileUploads;

    public ?int    $claseId   = null;
    public string  $tab       = 'resumen'; // 'resumen' | 'actividades' | (string) tipo_id

    /* ── Notas para tipos fijos ────────────────────────────────────── */
    public array $notas = []; // [estudiante_id => nota (string)]

    /* ── Estado de guardado (para controlar candado visual) ─────────── */
    public bool $notasGuardadas      = false;
    public bool $notasActsGuardadas  = false;
    public bool $notasGruposGuardadas = false;

    /* ── Actividades ───────────────────────────────────────────────── */
    // Modal definir actividades
    public bool   $showActModal  = false;
    public string $actNombre     = '';
    public string $actPunteo     = '100';
    public ?int   $actEditId     = null;

    // Modal plantilla (wizard antes de descargar)
    public bool  $showPlantillaModal = false;
    public int   $numActs            = 3;
    public array $actsWizard         = []; // [{nombre, punteo_max}]

    // Notas de actividades [actividad_id][estudiante_id] => nota
    public array $notasActs = [];

    // Notas por grupo [actividad_id][grupo_id] => nota
    public array $notasGrupos = [];

    // Import
    public bool  $showImportModal = false;
    public       $archivoImport   = null;
    public string $importMsg      = '';
    public string $importType     = ''; // 'success' | 'error'

    /* ── Modal actividad grupal (disparado desde Grupos) ───────────── */
    public bool   $showActGrupalModal = false;
    public string $actGrupalNombre    = '';
    public string $actGrupalPunteo    = '10';
    public ?int   $actGrupalSesionId  = null;

    /* ── Acceso a clases por rol ────────────────────────────────────── */
    private function clasesPropias(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::orderBy('nombre')->get();
        }
        $ids = Clase::where('usuario_id', $user->id)->pluck('id')
            ->merge($user->clasesImpartidas()->pluck('clase.id'))
            ->unique();
        return Clase::whereIn('id', $ids)->orderBy('nombre')->get();
    }

    private function queryClaseAutorizada(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::query();
        }
        $ids = Clase::where('usuario_id', $user->id)->pluck('id')
            ->merge($user->clasesImpartidas()->pluck('clase.id'))
            ->unique();
        return Clase::whereIn('id', $ids);
    }

    /* ── Render ────────────────────────────────────────────────────── */
    public function render()
    {
        $clases     = $this->clasesPropias();
        $tipos      = TipoCalificacion::orderBy('orden')->get();
        $clase      = $this->claseId
            ? $this->queryClaseAutorizada()->with('estudiantes', 'actividades')->find($this->claseId)
            : null;

        $estudiantes  = $clase?->estudiantes()->orderBy('nombre')->get() ?? collect();
        $actividades  = $clase?->actividades ?? collect();
        $tipoActivo   = $this->tab !== 'resumen' && $this->tab !== 'actividades'
            ? $tipos->firstWhere('id', (int) $this->tab)
            : null;

        // Grupos de la sesión activa (para vista grupal en actividades)
        $gruposPorActividad = collect();
        if ($tipoActivo && $tipoActivo->esActividades() && $clase) {
            $gruposPorActividad = $this->cargarGruposPorActividad($actividades);
        }

        // Resumen: calcular notas finales
        $resumen = collect();
        if ($this->tab === 'resumen' && $clase) {
            $resumen = $this->calcularResumen($clase, $tipos, $estudiantes, $actividades);
        }

        $esAdmin      = auth()->user()->isAdmin();
        $gradoCerrada = $clase ? $this->claseGradoCerrada($clase, $tipos, $estudiantes) : false;

        return view('livewire.calificaciones.index', compact(
            'clases', 'tipos', 'clase', 'estudiantes',
            'actividades', 'tipoActivo', 'resumen', 'gruposPorActividad',
            'esAdmin', 'gradoCerrada'
        ) + [
            'notasGuardadas'       => $this->notasGuardadas,
            'notasActsGuardadas'   => $this->notasActsGuardadas,
            'notasGruposGuardadas' => $this->notasGruposGuardadas,
        ]);
    }

    private function claseGradoCerrada($clase, $tipos, $estudiantes): bool
    {
        if (auth()->user()->isAdmin()) return false;
        $tiposFijos = $tipos->filter(fn ($t) => !$t->esActividades());
        if ($tiposFijos->isEmpty() || $estudiantes->isEmpty()) return false;
        foreach ($tiposFijos as $tipo) {
            $saved = Calificacion::where('clase_id', $clase->id)
                ->where('tipo_calificacion_id', $tipo->id)
                ->count();
            if ($saved < $estudiantes->count()) return false;
        }
        return true;
    }

    /* ── Selección de clase ─────────────────────────────────────────── */
    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            $this->queryClaseAutorizada()->findOrFail($this->claseId); // verificar acceso
        }
        $this->tab                 = 'resumen';
        $this->notas               = [];
        $this->notasGrupos         = [];
        $this->notasGuardadas      = false;
        $this->notasActsGuardadas  = false;
        $this->notasGruposGuardadas = false;
    }

    /* ── Cambio de tab ──────────────────────────────────────────────── */
    public function updatedTab(): void
    {
        $this->notas                = [];
        $this->notasActs            = [];
        $this->notasGrupos          = [];
        $this->notasGuardadas       = false;
        $this->notasActsGuardadas   = false;
        $this->notasGruposGuardadas = false;

        if (!$this->claseId) return;
        if ($this->tab === 'resumen') return;

        $tipo = TipoCalificacion::find((int) $this->tab);
        if (!$tipo) return;

        if ($tipo->esActividades()) {
            $this->loadNotasActividades();
            $this->loadNotasGrupos();
        } else {
            $this->loadNotas((int) $this->tab);
        }
    }

    private function loadNotas(int $tipoId): void
    {
        $notas = Calificacion::where('clase_id', $this->claseId)
            ->where('tipo_calificacion_id', $tipoId)
            ->pluck('nota', 'estudiante_id')
            ->map(fn ($v) => $v !== null ? (string) $v : '');

        $this->notas = $notas->toArray();

        // Auto-detectar si todas las notas ya están en BD (recarga de pestaña)
        $totalEstudiantes = \Illuminate\Support\Facades\DB::table('asignacion')
            ->where('clase_id', $this->claseId)->count();
        if ($totalEstudiantes > 0 && $notas->count() >= $totalEstudiantes) {
            $this->notasGuardadas = true;
        }
    }

    private function loadNotasActividades(): void
    {
        $this->notasActs = [];
        $actividades = Actividad::where('clase_id', $this->claseId)->get();
        foreach ($actividades as $act) {
            $this->notasActs[$act->id] = ActividadNota::where('actividad_id', $act->id)
                ->pluck('nota', 'estudiante_id')
                ->map(fn ($v) => $v !== null ? (string) $v : '')
                ->toArray();
        }

        // Auto-detectar si todas las actividades individuales ya tienen notas para todos
        $totalEstudiantes = \Illuminate\Support\Facades\DB::table('asignacion')
            ->where('clase_id', $this->claseId)->count();
        $indActividades = $actividades->filter(fn($a) => $a->grupo_sesion_id === null);

        if ($totalEstudiantes > 0 && $indActividades->isNotEmpty()) {
            $allSaved = $indActividades->every(
                fn($act) => count($this->notasActs[$act->id] ?? []) >= $totalEstudiantes
            );
            if ($allSaved) $this->notasActsGuardadas = true;
        }
    }

    private function loadNotasGrupos(): void
    {
        $this->notasGrupos = [];
        $actividades = Actividad::where('clase_id', $this->claseId)
            ->whereNotNull('grupo_sesion_id')
            ->get();

        foreach ($actividades as $act) {
            // Para actividades grupales tomamos una nota representativa por grupo
            // (todos los miembros del grupo tienen la misma nota)
            $grupos = Grupo::where('sesion_id', $act->grupo_sesion_id)
                ->with('estudiantes:id')
                ->get();

            foreach ($grupos as $grupo) {
                $primerMiembro = $grupo->estudiantes->first();
                if (!$primerMiembro) continue;

                $nota = ActividadNota::where('actividad_id', $act->id)
                    ->where('estudiante_id', $primerMiembro->id)
                    ->value('nota');

                $this->notasGrupos[$act->id][$grupo->id] = $nota !== null ? (string) $nota : '';
            }
        }

        // Auto-detectar si todos los grupos ya tienen notas guardadas
        if (!empty($this->notasGrupos)) {
            $allSaved = true;
            foreach ($this->notasGrupos as $porGrupo) {
                foreach ($porGrupo as $nota) {
                    if ($nota === '') { $allSaved = false; break 2; }
                }
            }
            if ($allSaved) $this->notasGruposGuardadas = true;
        }
    }

    private function cargarGruposPorActividad($actividades): \Illuminate\Support\Collection
    {
        return $actividades
            ->filter(fn ($a) => $a->grupo_sesion_id !== null)
            ->mapWithKeys(function ($act) {
                $grupos = Grupo::where('sesion_id', $act->grupo_sesion_id)
                    ->with('estudiantes:id,nombre')
                    ->orderBy('id')
                    ->get();
                return [$act->id => $grupos];
            });
    }

    /* ── Guardar notas tipo fijo ────────────────────────────────────── */
    public function guardarNotas(): void
    {
        if (!$this->claseId || $this->tab === 'resumen' || $this->tab === 'actividades') return;

        $esAdmin = auth()->user()->isAdmin();
        $tipoId  = (int) $this->tab;
        $tipo    = TipoCalificacion::findOrFail($tipoId);

        foreach ($this->notas as $estudianteId => $valor) {
            $valor = trim((string) $valor);

            // Catedrático no puede modificar notas ya guardadas
            if (!$esAdmin) {
                $existente = Calificacion::where('clase_id', $this->claseId)
                    ->where('tipo_calificacion_id', $tipoId)
                    ->where('estudiante_id', $estudianteId)
                    ->exists();
                if ($existente) continue;
            }

            if ($valor === '') {
                Calificacion::where('clase_id', $this->claseId)
                    ->where('tipo_calificacion_id', $tipoId)
                    ->where('estudiante_id', $estudianteId)
                    ->delete();
                continue;
            }

            $nota = (float) $valor;
            $nota = max(0, min($nota, (float) $tipo->punteo_max));

            Calificacion::updateOrCreate(
                [
                    'clase_id'             => $this->claseId,
                    'tipo_calificacion_id' => $tipoId,
                    'estudiante_id'        => $estudianteId,
                ],
                ['nota' => round($nota, 2)]
            );
        }

        $this->notasGuardadas = true;
        $this->loadNotas((int) $this->tab);
        $this->dispatch('notify', message: 'Notas guardadas correctamente.');
    }

    /* ── Actividades: CRUD ──────────────────────────────────────────── */
    public function abrirNuevaActividad(): void
    {
        $this->actNombre = '';
        $this->actPunteo = '100';
        $this->actEditId = null;
        $this->showActModal = true;
    }

    public function abrirEditarActividad(int $id): void
    {
        $act = Actividad::where('clase_id', $this->claseId)->findOrFail($id);
        $this->actEditId = $id;
        $this->actNombre = $act->nombre;
        $this->actPunteo = (string) $act->punteo_max;
        $this->showActModal = true;
    }

    public function guardarActividad(): void
    {
        $this->validate([
            'actNombre' => 'required|string|max:100',
        ], [
            'actNombre.required' => 'El nombre es obligatorio.',
        ]);

        if ($this->actEditId) {
            $act = Actividad::where('clase_id', $this->claseId)->findOrFail($this->actEditId);
            $act->update(['nombre' => $this->actNombre]);
        } else {
            $orden = Actividad::where('clase_id', $this->claseId)->max('orden') + 1;
            Actividad::create([
                'clase_id'   => $this->claseId,
                'nombre'     => $this->actNombre,
                'punteo_max' => 100,
                'orden'      => $orden,
            ]);
        }

        $this->showActModal = false;
        $this->actNombre    = '';
        $this->actPunteo    = '100';

        if ($this->actEditId) {
            // Solo cambió el nombre — el ID es el mismo, las notas en memoria se preservan
            $this->actEditId = null;
        } else {
            // Nueva actividad: agregar su slot vacío sin destruir el resto de $notasActs
            $this->actEditId = null;
            $nueva = Actividad::where('clase_id', $this->claseId)->orderByDesc('orden')->first();
            if ($nueva && !isset($this->notasActs[$nueva->id])) {
                $this->notasActs[$nueva->id] = [];
            }
        }
    }

    public function eliminarActividad(int $id): void
    {
        Actividad::where('clase_id', $this->claseId)->findOrFail($id)->delete();
        unset($this->notasActs[$id]);
        unset($this->notasGrupos[$id]);
    }

    /* ── Guardar notas de actividades individuales ─────────────────── */
    public function guardarNotasActividades(): void
    {
        if (!$this->claseId) return;

        $esAdmin     = auth()->user()->isAdmin();
        $actividades = Actividad::where('clase_id', $this->claseId)->get()->keyBy('id');

        foreach ($this->notasActs as $actId => $porEstudiante) {
            $act = $actividades[$actId] ?? null;
            if (!$act || $act->esGrupal()) continue;

            foreach ($porEstudiante as $estudianteId => $valor) {
                $valor = trim((string) $valor);

                // Catedrático no puede modificar notas ya guardadas
                if (!$esAdmin) {
                    $existente = ActividadNota::where('actividad_id', $actId)
                        ->where('estudiante_id', $estudianteId)
                        ->exists();
                    if ($existente) continue;
                }

                if ($valor === '') {
                    ActividadNota::where('actividad_id', $actId)
                        ->where('estudiante_id', $estudianteId)
                        ->delete();
                    continue;
                }

                $nota = max(0, min((float) $valor, (float) $act->punteo_max));
                ActividadNota::updateOrCreate(
                    ['actividad_id' => $actId, 'estudiante_id' => $estudianteId],
                    ['nota' => round($nota, 2), 'grupo_id' => null]
                );
            }
        }

        $this->notasActsGuardadas = true;
        $this->loadNotasActividades();
        $this->dispatch('notify', message: 'Notas de actividades guardadas.');
    }

    /* ── Guardar notas grupales ─────────────────────────────────────── */
    public function guardarNotasGrupos(): void
    {
        if (!$this->claseId) return;

        $esAdmin     = auth()->user()->isAdmin();
        $actividades = Actividad::where('clase_id', $this->claseId)
            ->whereNotNull('grupo_sesion_id')
            ->get()
            ->keyBy('id');

        foreach ($this->notasGrupos as $actId => $porGrupo) {
            $act = $actividades[$actId] ?? null;
            if (!$act) continue;

            foreach ($porGrupo as $grupoId => $valor) {
                $valor = trim((string) $valor);

                $grupo = Grupo::with('estudiantes:id')->find($grupoId);
                if (!$grupo) continue;

                // Catedrático no puede modificar notas ya guardadas
                if (!$esAdmin) {
                    $primerMiembro = $grupo->estudiantes->first();
                    if ($primerMiembro) {
                        $existente = ActividadNota::where('actividad_id', $actId)
                            ->where('estudiante_id', $primerMiembro->id)
                            ->exists();
                        if ($existente) continue;
                    }
                }

                if ($valor === '') {
                    ActividadNota::where('actividad_id', $actId)
                        ->whereIn('estudiante_id', $grupo->estudiantes->pluck('id'))
                        ->delete();
                    continue;
                }

                $nota = max(0, min((float) $valor, (float) $act->punteo_max));

                foreach ($grupo->estudiantes as $estudiante) {
                    ActividadNota::updateOrCreate(
                        ['actividad_id' => $actId, 'estudiante_id' => $estudiante->id],
                        ['nota' => round($nota, 2), 'grupo_id' => $grupoId]
                    );
                }
            }
        }

        $this->notasGruposGuardadas = true;
        $this->loadNotasActividades();
        $this->loadNotasGrupos();
        $this->dispatch('notify', message: 'Notas grupales guardadas y propagadas.');
    }

    /* ── Modal actividad grupal ─────────────────────────────────────── */
    public function abrirActGrupalModal(int $sesionId): void
    {
        $this->actGrupalSesionId = $sesionId;
        $this->actGrupalNombre   = 'Actividad Grupal';
        $this->actGrupalPunteo   = '100';
        $this->showActGrupalModal = true;
    }

    public function guardarActividadGrupal(): void
    {
        $this->validate([
            'actGrupalNombre'  => 'required|string|max:100',
            'actGrupalPunteo'  => 'required|numeric|min:0.01|max:9999',
            'actGrupalSesionId' => 'required|integer',
        ], [
            'actGrupalNombre.required' => 'El nombre es obligatorio.',
            'actGrupalPunteo.required' => 'El punteo es obligatorio.',
        ]);

        if (!$this->claseId) return;

        $orden = Actividad::where('clase_id', $this->claseId)->max('orden') + 1;

        Actividad::create([
            'clase_id'        => $this->claseId,
            'grupo_sesion_id' => $this->actGrupalSesionId,
            'nombre'          => $this->actGrupalNombre,
            'punteo_max'      => 100,
            'orden'           => $orden,
        ]);

        $this->showActGrupalModal = false;
        $this->actGrupalSesionId  = null;
        $this->loadNotasActividades();
        $this->loadNotasGrupos();

        $this->dispatch('notify', message: 'Actividad grupal creada.');
    }

    /* ── Wizard plantilla ───────────────────────────────────────────── */
    public function abrirPlantillaModal(): void
    {
        $definidas = Actividad::where('clase_id', $this->claseId)->orderBy('orden')->get();

        if ($definidas->isNotEmpty()) {
            $this->numActs    = $definidas->count();
            $this->actsWizard = $definidas->map(fn ($a) => [
                'nombre'     => $a->nombre,
                'punteo_max' => (string) $a->punteo_max,
            ])->toArray();
        } else {
            $this->numActs    = 3;
            $this->actsWizard = array_fill(0, 3, ['nombre' => '', 'punteo_max' => '100']);
        }

        $this->showPlantillaModal = true;
    }

    public function updatedNumActs(): void
    {
        $actual = count($this->actsWizard);
        if ($this->numActs > $actual) {
            for ($i = $actual; $i < $this->numActs; $i++) {
                $this->actsWizard[] = ['nombre' => '', 'punteo_max' => '100'];
            }
        } else {
            $this->actsWizard = array_slice($this->actsWizard, 0, $this->numActs);
        }
    }

    public function descargarPlantilla(): mixed
    {
        foreach ($this->actsWizard as $i => $act) {
            if (empty(trim($act['nombre'] ?? ''))) {
                $this->addError("actsWizard.{$i}.nombre", 'El nombre es obligatorio.');
                return null;
            }
        }

        $clase = $this->queryClaseAutorizada()->findOrFail($this->claseId);

        // Actividades individuales existentes indexadas por orden
        $existentes = Actividad::where('clase_id', $this->claseId)
            ->whereNull('grupo_sesion_id')
            ->orderBy('orden')
            ->get()
            ->keyBy('orden');

        foreach ($this->actsWizard as $i => $actData) {
            $orden = $i + 1;
            if (isset($existentes[$orden])) {
                // Actualiza solo el nombre — preserva el ID y sus notas
                $existentes[$orden]->update(['nombre' => trim($actData['nombre'])]);
            } else {
                Actividad::create([
                    'clase_id'   => $this->claseId,
                    'nombre'     => trim($actData['nombre']),
                    'punteo_max' => 100,
                    'orden'      => $orden,
                ]);
            }
        }

        // Eliminar solo las actividades sobrantes que NO tengan notas guardadas
        $wizardCount = count($this->actsWizard);
        Actividad::where('clase_id', $this->claseId)
            ->whereNull('grupo_sesion_id')
            ->where('orden', '>', $wizardCount)
            ->whereDoesntHave('notas')
            ->delete();

        $this->showPlantillaModal = false;

        $actividadesModelos = Actividad::where('clase_id', $this->claseId)
            ->whereNull('grupo_sesion_id')
            ->orderBy('orden')
            ->get();

        // Agregar slots vacíos solo para actividades nuevas, sin tocar las existentes en $notasActs
        foreach ($actividadesModelos as $act) {
            if (!isset($this->notasActs[$act->id])) {
                $this->notasActs[$act->id] = [];
            }
        }

        $nombre = 'actividades_' . \Illuminate\Support\Str::slug($clase->nombre) . '.xlsx';

        return Excel::download(
            new ActividadesPlantillaExport($clase, $actividadesModelos),
            $nombre
        );
    }

    /* ── Importar notas de actividades ─────────────────────────────── */
    public function importarActividades(): void
    {
        $this->validate([
            'archivoImport' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'archivoImport.required' => 'Selecciona un archivo.',
            'archivoImport.mimes'    => 'Formato no permitido. Usa .xlsx, .xls o .csv.',
        ]);

        try {
            $importer = new ActividadesImport($this->claseId);
            Excel::import($importer, $this->archivoImport->getRealPath());

            $this->importMsg  = "Importación completada: {$importer->importados} filas procesadas.";
            if ($importer->errores > 0) {
                $this->importMsg .= " ({$importer->errores} errores). ";
                $this->importMsg .= implode(' | ', array_slice($importer->mensajes, 0, 5));
            }
            $this->importType = $importer->errores > 0 ? 'warning' : 'success';
        } catch (\Throwable $e) {
            $this->importMsg  = 'Error al importar: ' . $e->getMessage();
            $this->importType = 'error';
        }

        $this->archivoImport = null;
        $this->loadNotasActividades();
        $this->showImportModal = false;

        $this->dispatch('notify', message: $this->importMsg);
    }

    /* ── Cálculo de resumen ─────────────────────────────────────────── */
    private function calcularResumen($clase, $tipos, $estudiantes, $actividades): \Illuminate\Support\Collection
    {
        if ($estudiantes->isEmpty()) return collect();

        $estudianteIds = $estudiantes->pluck('id');

        // Notas por tipo por estudiante
        $notasPorTipo = [];
        foreach ($tipos as $tipo) {
            $notasPorTipo[$tipo->id] = Calificacion::where('clase_id', $clase->id)
                ->where('tipo_calificacion_id', $tipo->id)
                ->whereIn('estudiante_id', $estudianteIds)
                ->pluck('nota', 'estudiante_id')
                ->toArray();
        }

        // Notas de actividades
        $notasActsPorEst = []; // [estudiante_id => {suma, max}]

        if ($actividades->isNotEmpty()) {
            foreach ($actividades as $act) {
                $notas = ActividadNota::where('actividad_id', $act->id)
                    ->whereIn('estudiante_id', $estudianteIds)
                    ->pluck('nota', 'estudiante_id')
                    ->toArray();

                foreach ($estudianteIds as $eid) {
                    if (!isset($notasActsPorEst[$eid])) {
                        $notasActsPorEst[$eid] = ['suma' => 0, 'max' => 0];
                    }
                    $notasActsPorEst[$eid]['max'] += (float) $act->punteo_max;
                    if (isset($notas[$eid])) {
                        $notasActsPorEst[$eid]['suma'] += (float) $notas[$eid];
                    }
                }
            }
        }

        // Puntos extra de participación (ruleta)
        $participaciones = Participacion::whereHas('sesion', fn ($q) =>
                $q->where('clase_id', $clase->id)
            )
            ->whereIn('estudiante_id', $estudianteIds)
            ->selectRaw('estudiante_id, SUM(calificacion) as total_extra')
            ->groupBy('estudiante_id')
            ->pluck('total_extra', 'estudiante_id')
            ->toArray();

        $maxExtra = 5.0; // puntos extra de ruleta (fijo)

        // Tipo Actividades
        $tipoActividades = $tipos->first(fn ($t) => $t->esActividades());

        return $estudiantes->map(function ($e) use (
            $tipos, $notasPorTipo, $notasActsPorEst,
            $tipoActividades, $participaciones, $maxExtra
        ) {
            $fila = [
                'id'           => $e->id,
                'carnet'       => $e->carnet,
                'nombre'       => $e->nombre,
                'tipos'        => [],
                'puntos_extra' => 0,
                'total'        => 0,
                'aprobado'     => null,
            ];

            $tieneTodasLasNotas = true;

            foreach ($tipos as $tipo) {
                if ($tipo->esActividades()) {
                    $data = $notasActsPorEst[$e->id] ?? null;
                    if ($data && $data['max'] > 0) {
                        // Siempre promedio: suma/max × punteo_tipo
                        $pts = round($data['suma'] / $data['max'] * (float) $tipo->punteo_max, 2);
                    } else {
                        $pts = null;
                        $tieneTodasLasNotas = false;
                    }
                    $fila['tipos'][$tipo->id] = ['pts' => $pts, 'max' => (float) $tipo->punteo_max];
                } else {
                    $pts = isset($notasPorTipo[$tipo->id][$e->id])
                        ? (float) $notasPorTipo[$tipo->id][$e->id]
                        : null;
                    if ($pts === null) $tieneTodasLasNotas = false;
                    $fila['tipos'][$tipo->id] = ['pts' => $pts, 'max' => (float) $tipo->punteo_max];
                }

                if ($pts !== null) {
                    $fila['total'] += $pts;
                }
            }

            $fila['total'] = round($fila['total'], 2);

            // Estado de aprobación
            if ($tieneTodasLasNotas) {
                $fila['aprobado'] = $fila['total'] >= 61;
            }

            return $fila;
        });
    }
}
