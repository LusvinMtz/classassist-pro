<?php

namespace App\Observers;

use App\Models\Calificacion;
use App\Services\BitacoraService;

class CalificacionObserver
{
    public function created(Calificacion $cal): void
    {
        $nombre = $cal->estudiante?->nombre ?? "ID {$cal->estudiante_id}";
        $tipo   = $cal->tipoCalificacion?->nombre ?? "tipo ID {$cal->tipo_calificacion_id}";
        $clase  = $cal->clase?->nombre ?? "clase ID {$cal->clase_id}";

        BitacoraService::registrarCreacion(
            $cal, 'Calificacion',
            "Calificación registrada: \"{$nombre}\" — {$tipo}: {$cal->nota} en \"{$clase}\""
        );
    }

    public function updated(Calificacion $cal): void
    {
        $nombre = $cal->estudiante?->nombre ?? "ID {$cal->estudiante_id}";
        $tipo   = $cal->tipoCalificacion?->nombre ?? "tipo ID {$cal->tipo_calificacion_id}";

        BitacoraService::registrarEdicion(
            $cal, 'Calificacion',
            "Calificación editada: \"{$nombre}\" — {$tipo}: {$cal->nota}"
        );
    }

    public function deleted(Calificacion $cal): void
    {
        $nombre = $cal->estudiante?->nombre ?? "ID {$cal->estudiante_id}";

        BitacoraService::registrarEliminacion(
            $cal, 'Calificacion',
            "Calificación eliminada: estudiante \"{$nombre}\""
        );
    }
}
