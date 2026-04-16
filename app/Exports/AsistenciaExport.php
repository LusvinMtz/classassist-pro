<?php

namespace App\Exports;

use App\Models\Sesion;
use App\Models\Estudiante;
use App\Models\Asistencia;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AsistenciaExport implements WithMultipleSheets
{
    public function __construct(
        private int $claseId,
        private string $claseNombre
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\ResumenSheet($this->claseId, $this->claseNombre),
            new Sheets\DetalleAsistenciaSheet($this->claseId),
            new Sheets\ParticipacionesSheet($this->claseId),
            new Sheets\CalificacionesSheet($this->claseId),
        ];
    }
}
