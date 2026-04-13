<?php

namespace App\Observers;

use App\Models\Estudiante;
use App\Services\BitacoraService;

class EstudianteObserver
{
    public function created(Estudiante $estudiante): void
    {
        BitacoraService::registrarCreacion($estudiante, 'Estudiante', "Estudiante registrado: \"{$estudiante->nombre}\" (carnet: {$estudiante->carnet})");
    }

    public function updated(Estudiante $estudiante): void
    {
        BitacoraService::registrarEdicion($estudiante, 'Estudiante', "Estudiante editado: \"{$estudiante->nombre}\" (carnet: {$estudiante->carnet})");
    }

    public function deleted(Estudiante $estudiante): void
    {
        BitacoraService::registrarEliminacion($estudiante, 'Estudiante', "Estudiante eliminado: \"{$estudiante->nombre}\" (carnet: {$estudiante->carnet})");
    }

    public function restored(Estudiante $estudiante): void
    {
        BitacoraService::registrarRestauracion($estudiante, 'Estudiante', "Estudiante restaurado: \"{$estudiante->nombre}\" (carnet: {$estudiante->carnet})");
    }
}
