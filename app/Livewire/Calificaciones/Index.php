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

    /* ── Configuración de evaluación ───────────────────────────────── */
    public string $metodoActividades = 'porcentaje'; // 'porcentaje' | 'puntos'
    public string $maxPuntosExtra    = '5';

    /* ── Render ────────────────────────────────────────────────────── */
    public function render()
    {
        $clases     = Clase::where('usuario_id', auth()->id())->get();
        $tipos      = TipoCalificacion::orderBy('orden')->get();
        $clase      = $this->claseId
            ? Clase::where('usuario_id', auth()->id())->with('estudiantes', 'actividades')->find($this->claseId)
            : null;

        $estudiantes  = $clase?->estudiantes()->orderBy('nombre')->get() ?? collect();
        $actividades  = $clase?->actividades ?? collect();
        $tipoActivo   = $this->tab !== 'resumen' && $this->tab !== 'actividades'
            ? $tipos->firstWhere('id', (int) $this->tab)
            : null;

        // Grupos de la sesión activa (para vista grupal en actividades)
        $gruposPorActividad = collect();
        if ($this->tab === 'actividades' && $clase) {
            $gruposPorActividad = $this->cargarGruposPorActividad($actividades);
        }

        // Resumen: calcular notas finales
        $resumen = collect();
        if ($this->tab === 'resumen' && $clase) {
            $resumen = $this->calcularResumen($clase, $tipos, $estudiantes, $actividades);
        }

        return view('livewire.calificaciones.index', compact(
            'clases', 'tipos', 'clase', 'estudiantes',
            'actividades', 'tipoActivo', 'resumen', 'gruposPorActividad'
        ));
    }

    /* ── Selección de clase ─────────────────────────────────────────── */
    public function updatedClaseId(): void
    {
        if ($this->claseId) {
            $clase = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
            $this->metodoActividades = $clase->metodo_actividades ?? 'porcentaje';
            $this->maxPuntosExtra    = (string) ($clase->max_puntos_extra ?? '5');
        }
        $this->tab        = 'resumen';
        $this->notas      = [];
        $this->notasGrupos = [];
    }

    /* ── Cambio de tab ──────────────────────────────────────────────── */
    public function updatedTab(): void
    {
        $this->notas      = [];
        $this->notasActs  = [];
        $this->notasGrupos = [];

        if (!$this->claseId) return;

        if ($this->tab !== 'resumen' && $this->tab !== 'actividades') {
            $this->loadNotas((int) $this->tab);
        }

        if ($this->tab === 'actividades') {
            $this->loadNotasActividades();
            $this->loadNotasGrupos();
        }
    }

    /* ── Guardar configuración de evaluación ────────────────────────── */
    public function guardarConfigEvaluacion(): void
    {
        if (!$this->claseId) return;

        $this->validate([
            'metodoActividades' => 'required|in:porcentaje,puntos',
            'maxPuntosExtra'    => 'required|numeric|min:0|max:20',
        ]);

        $clase = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        $clase->update([
            'metodo_actividades' => $this->metodoActividades,
            'max_puntos_extra'   => (float) $this->maxPuntosExtra,
        ]);

        $this->dispatch('notify', message: 'Configuración de evaluación guardada.');
    }

    private function loadNotas(int $tipoId): void
    {
        $this->notas = Calificacion::where('clase_id', $this->claseId)
            ->where('tipo_calificacion_id', $tipoId)
            ->pluck('nota', 'estudiante_id')
            ->map(fn ($v) => $v !== null ? (string) $v : '')
            ->toArray();
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

        $tipoId = (int) $this->tab;
        $tipo   = TipoCalificacion::findOrFail($tipoId);

        foreach ($this->notas as $estudianteId => $valor) {
            $valor = trim((string) $valor);
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

        $this->dispatch('notify', message: 'Notas guardadas correctamente.');
    }

    /* ── Actividades: CRUD ──────────────────────────────────────────── */
    public function abrirNuevaActividad(): void
    {
        $this->actNombre = '';
        $this->actPunteo = $this->metodoActividades === 'puntos' ? '5' : '100';
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
            'actPunteo' => 'required|numeric|min:0.01|max:9999',
        ], [
            'actNombre.required' => 'El nombre es obligatorio.',
            'actPunteo.required' => 'El punteo máximo es obligatorio.',
            'actPunteo.min'      => 'El punteo debe ser mayor a 0.',
        ]);

        if ($this->actEditId) {
            $act = Actividad::where('clase_id', $this->claseId)->findOrFail($this->actEditId);
            $act->update(['nombre' => $this->actNombre, 'punteo_max' => $this->actPunteo]);
        } else {
            $orden = Actividad::where('clase_id', $this->claseId)->max('orden') + 1;
            Actividad::create([
                'clase_id'   => $this->claseId,
                'nombre'     => $this->actNombre,
                'punteo_max' => $this->actPunteo,
                'orden'      => $orden,
            ]);
        }

        $this->showActModal  = false;
        $this->actEditId     = null;
        $this->actNombre     = '';
        $this->actPunteo     = '100';
        $this->loadNotasActividades();
    }

    public function eliminarActividad(int $id): void
    {
        Actividad::where('clase_id', $this->claseId)->findOrFail($id)->delete();
        $this->loadNotasActividades();
        $this->loadNotasGrupos();
    }

    /* ── Guardar notas de actividades individuales ─────────────────── */
    public function guardarNotasActividades(): void
    {
        if (!$this->claseId) return;

        $clase       = Clase::findOrFail($this->claseId);
        $actividades = Actividad::where('clase_id', $this->claseId)->get()->keyBy('id');

        foreach ($this->notasActs as $actId => $porEstudiante) {
            $act = $actividades[$actId] ?? null;
            if (!$act || $act->esGrupal()) continue; // las grupales se guardan aparte

            foreach ($porEstudiante as $estudianteId => $valor) {
                $valor = trim((string) $valor);
                if ($valor === '') {
                    ActividadNota::where('actividad_id', $actId)
                        ->where('estudiante_id', $estudianteId)
                        ->delete();
                    continue;
                }

                $notaInput = (float) $valor;

                // Validación estricta para método por puntos
                if ($clase->metodo_actividades === 'puntos' && $notaInput > (float) $act->punteo_max) {
                    $this->addError(
                        "notaActs_{$actId}_{$estudianteId}",
                        "Máximo: {$act->punteo_max} pts"
                    );
                    continue;
                }

                $nota = max(0, min($notaInput, (float) $act->punteo_max));
                ActividadNota::updateOrCreate(
                    ['actividad_id' => $actId, 'estudiante_id' => $estudianteId],
                    ['nota' => round($nota, 2), 'grupo_id' => null]
                );
            }
        }

        $this->dispatch('notify', message: 'Notas de actividades guardadas.');
    }

    /* ── Guardar notas grupales ─────────────────────────────────────── */
    public function guardarNotasGrupos(): void
    {
        if (!$this->claseId) return;

        $clase       = Clase::findOrFail($this->claseId);
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

                if ($valor === '') {
                    // Limpiar notas del grupo en esta actividad
                    ActividadNota::where('actividad_id', $actId)
                        ->whereIn('estudiante_id', $grupo->estudiantes->pluck('id'))
                        ->delete();
                    continue;
                }

                $notaInput = (float) $valor;

                if ($clase->metodo_actividades === 'puntos' && $notaInput > (float) $act->punteo_max) {
                    $this->addError(
                        "notaGrupo_{$actId}_{$grupoId}",
                        "Máximo: {$act->punteo_max} pts"
                    );
                    continue;
                }

                $nota = max(0, min($notaInput, (float) $act->punteo_max));

                // Propagar la misma nota a todos los miembros del grupo
                foreach ($grupo->estudiantes as $estudiante) {
                    ActividadNota::updateOrCreate(
                        ['actividad_id' => $actId, 'estudiante_id' => $estudiante->id],
                        ['nota' => round($nota, 2), 'grupo_id' => $grupoId]
                    );
                }
            }
        }

        $this->dispatch('notify', message: 'Notas grupales guardadas y propagadas.');
    }

    /* ── Modal actividad grupal ─────────────────────────────────────── */
    public function abrirActGrupalModal(int $sesionId): void
    {
        $this->actGrupalSesionId = $sesionId;
        $this->actGrupalNombre   = 'Actividad Grupal';
        $this->actGrupalPunteo   = $this->metodoActividades === 'puntos' ? '10' : '100';
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
            'punteo_max'      => $this->actGrupalPunteo,
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

        $clase = Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);
        Actividad::where('clase_id', $this->claseId)->whereNull('grupo_sesion_id')->delete();

        foreach ($this->actsWizard as $i => $actData) {
            Actividad::create([
                'clase_id'   => $this->claseId,
                'nombre'     => trim($actData['nombre']),
                'punteo_max' => max(0.01, (float) ($actData['punteo_max'] ?? 100)),
                'orden'      => $i + 1,
            ]);
        }

        $this->showPlantillaModal = false;
        $this->loadNotasActividades();

        $nombre = 'actividades_' . \Illuminate\Support\Str::slug($clase->nombre) . '.xlsx';

        return Excel::download(
            new ActividadesPlantillaExport($clase, $this->actsWizard),
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

        $maxExtra = (float) ($clase->max_puntos_extra ?? 5);

        // Tipo Actividades
        $tipoActividades = $tipos->first(fn ($t) => $t->esActividades());

        return $estudiantes->map(function ($e) use (
            $tipos, $notasPorTipo, $notasActsPorEst,
            $tipoActividades, $participaciones, $maxExtra, $clase
        ) {
            $fila = [
                'id'           => $e->id,
                'carnet'       => $e->carnet,
                'nombre'       => $e->nombre,
                'tipos'        => [],
                'puntos_extra' => 0,
                'total'        => 0,
                'aprobado'     => null, // null = pendiente, true = aprobado, false = reprobado
            ];

            $tieneTodasLasNotas = true;

            foreach ($tipos as $tipo) {
                if ($tipo->esActividades()) {
                    $data = $notasActsPorEst[$e->id] ?? null;
                    if ($data && $data['max'] > 0) {
                        if ($clase->metodo_actividades === 'puntos') {
                            // Suma directa capada en punteo_max del tipo
                            $pts = min(
                                round($data['suma'], 2),
                                (float) $tipo->punteo_max
                            );
                        } else {
                            // Porcentaje (comportamiento original)
                            $pts = round($data['suma'] / $data['max'] * (float) $tipo->punteo_max, 2);
                        }
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

            // Puntos extra (capped)
            $extra = min((float) ($participaciones[$e->id] ?? 0), $maxExtra);
            $fila['puntos_extra'] = round($extra, 2);
            $fila['total']        = round($fila['total'] + $extra, 2);

            // Estado de aprobación
            if ($tieneTodasLasNotas) {
                $fila['aprobado'] = $fila['total'] >= 61;
            }

            return $fila;
        });
    }
}
