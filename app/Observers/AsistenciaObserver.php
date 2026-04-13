<?php

namespace App\Observers;

use App\Models\Asistencia;
use App\Services\BitacoraService;

class AsistenciaObserver
{
    public function created(Asistencia $asistencia): void
    {
        $nombre = $asistencia->estudiante?->nombre ?? "ID {$asistencia->estudiante_id}";
        $clase  = $asistencia->sesion?->clase?->nombre ?? "sesión ID {$asistencia->sesion_id}";

        BitacoraService::registrarCreacion(
            $asistencia, 'Asistencia',
            "Asistencia registrada: \"{$nombre}\" en \"{$clase}\""
        );
    }

    public function deleted(Asistencia $asistencia): void
    {
        $nombre = $asistencia->estudiante?->nombre ?? "ID {$asistencia->estudiante_id}";

        BitacoraService::registrarEliminacion(
            $asistencia, 'Asistencia',
            "Asistencia eliminada: estudiante \"{$nombre}\""
        );
    }
}
