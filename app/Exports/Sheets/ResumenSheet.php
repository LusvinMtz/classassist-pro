<?php

namespace App\Exports\Sheets;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Participacion;
use App\Models\Sesion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private int $claseId, private string $claseNombre) {}

    public function title(): string { return 'Resumen'; }

    public function headings(): array
    {
        return ['Carné', 'Estudiante', 'Sesiones totales', 'Sesiones asistidas', '% Asistencia', 'Participaciones', 'Calif. Promedio'];
    }

    public function collection()
    {
        $clase         = Clase::findOrFail($this->claseId);
        $sesionIds     = Sesion::where('clase_id', $this->claseId)->pluck('id');
        $totalSesiones = $sesionIds->count();

        $asistenciasPor = Asistencia::whereIn('sesion_id', $sesionIds)
            ->selectRaw('estudiante_id, COUNT(*) as total')
            ->groupBy('estudiante_id')
            ->pluck('total', 'estudiante_id');

        $participacionesPor = Participacion::whereIn('sesion_id', $sesionIds)
            ->selectRaw('estudiante_id, COUNT(*) as total, AVG(calificacion) as promedio')
            ->groupBy('estudiante_id')
            ->get()
            ->keyBy('estudiante_id');

        return $clase->estudiantes()->wherePivot('anio', now()->year)->orderBy('nombre')->get()->map(function ($e) use (
            $totalSesiones, $asistenciasPor, $participacionesPor
        ) {
            $asist  = $asistenciasPor[$e->id] ?? 0;
            $pct    = $totalSesiones > 0 ? round($asist / $totalSesiones * 100, 1) : 0;
            $part   = $participacionesPor[$e->id] ?? null;
            return [
                $e->carnet,
                $e->nombre,
                $totalSesiones,
                $asist,
                $pct . '%',
                $part ? $part->total : 0,
                $part && $part->promedio !== null ? number_format($part->promedio, 2) : '—',
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000B60']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
