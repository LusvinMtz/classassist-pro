<?php

namespace App\Observers;

use App\Models\Clase;
use App\Services\BitacoraService;

class ClaseObserver
{
    public function created(Clase $clase): void
    {
        BitacoraService::registrarCreacion($clase, 'Clase', "Clase creada: \"{$clase->nombre}\"");
    }

    public function updated(Clase $clase): void
    {
        BitacoraService::registrarEdicion($clase, 'Clase', "Clase editada: \"{$clase->nombre}\"");
    }

    public function deleted(Clase $clase): void
    {
        BitacoraService::registrarEliminacion($clase, 'Clase', "Clase eliminada: \"{$clase->nombre}\"");
    }

    public function restored(Clase $clase): void
    {
        BitacoraService::registrarRestauracion($clase, 'Clase', "Clase restaurada: \"{$clase->nombre}\"");
    }
}
