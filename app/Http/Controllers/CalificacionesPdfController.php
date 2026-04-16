<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\ActividadNota;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\Participacion;
use App\Models\TipoCalificacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CalificacionesPdfController extends Controller
{
    public function download(int $claseId): Response
    {
        $user  = auth()->user();
        $query = $user->isAdmin()
            ? Clase::query()
            : Clase::whereIn('id',
                Clase::where('usuario_id', $user->id)->pluck('id')
                    ->merge($user->clasesImpartidas()->pluck('clase.id'))
                    ->unique()
              );

        $clase       = $query->with(['estudiantes', 'actividades', 'carrera', 'catedratico'])->findOrFail($claseId);
        $tipos       = TipoCalificacion::orderBy('orden')->get();
        $estudiantes = $clase->estudiantes()->orderBy('nombre')->get();
        $actividades = $clase->actividades;

        $resumen = $this->calcularResumen($clase, $tipos, $estudiantes, $actividades);

        $pdf = Pdf::loadView('pdf.acta-calificaciones', compact('clase', 'tipos', 'resumen'))
            ->setPaper('letter', 'landscape');

        $nombre = 'acta_' . \Illuminate\Support\Str::slug($clase->nombre) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($nombre);
    }

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
        $notasActsPorEst = [];
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

        $tipoActividades = $tipos->first(fn ($t) => $t->esActividades());

        return $estudiantes->map(function ($e) use ($tipos, $notasPorTipo, $notasActsPorEst, $tipoActividades) {
            $fila = [
                'id'       => $e->id,
                'carnet'   => $e->carnet,
                'nombre'   => $e->nombre,
                'tipos'    => [],
                'total'    => 0,
                'aprobado' => null,
            ];

            $tieneTodasLasNotas = true;

            foreach ($tipos as $tipo) {
                if ($tipo->esActividades()) {
                    $data = $notasActsPorEst[$e->id] ?? null;
                    if ($data && $data['max'] > 0) {
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

            if ($tieneTodasLasNotas) {
                $fila['aprobado'] = $fila['total'] >= 61;
            }

            return $fila;
        });
    }
}
