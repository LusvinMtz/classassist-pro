<?php

namespace App\Exports\Sheets;

use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\TipoCalificacion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CalificacionesSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private ?Collection $tiposCache = null;

    public function __construct(private int $claseId) {}

    public function title(): string { return 'Calificaciones'; }

    private function tipos(): Collection
    {
        if ($this->tiposCache === null) {
            $this->tiposCache = TipoCalificacion::whereHas('calificaciones', function ($q) {
                $q->where('clase_id', $this->claseId);
            })->orderBy('orden')->get();
        }

        return $this->tiposCache;
    }

    public function headings(): array
    {
        $headers = ['Carné', 'Estudiante'];

        foreach ($this->tipos() as $tipo) {
            $headers[] = $tipo->nombre . ' (' . number_format($tipo->punteo_max, 0) . ' pts)';
        }

        $headers[] = 'Total';

        return $headers;
    }

    public function collection(): Collection
    {
        $clase  = Clase::findOrFail($this->claseId);
        $tipos  = $this->tipos();

        $calsPorEstudiante = Calificacion::where('clase_id', $this->claseId)
            ->get()
            ->groupBy('estudiante_id')
            ->map(fn ($cals) => $cals->keyBy('tipo_calificacion_id'));

        return $clase->estudiantes()->orderBy('nombre')->get()->map(function ($e) use ($tipos, $calsPorEstudiante) {
            $row        = [$e->carnet, $e->nombre];
            $total      = 0;
            $tieneNotas = false;
            $estudianteCals = $calsPorEstudiante[$e->id] ?? collect();

            foreach ($tipos as $tipo) {
                $cal  = $estudianteCals[$tipo->id] ?? null;
                $nota = $cal ? (float) $cal->nota : null;

                if ($nota !== null) {
                    $row[]      = number_format($nota, 2);
                    $total     += $nota;
                    $tieneNotas = true;
                } else {
                    $row[] = '—';
                }
            }

            $row[] = $tieneNotas ? number_format($total, 2) : '—';

            return $row;
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000B60']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
