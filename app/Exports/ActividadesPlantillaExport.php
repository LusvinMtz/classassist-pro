<?php

namespace App\Exports;

use App\Models\ActividadNota;
use App\Models\Clase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActividadesPlantillaExport implements FromArray, WithStyles, WithColumnWidths
{
    public function __construct(
        private Clase      $clase,
        private Collection $actividades  // Collection de modelos Actividad
    ) {}

    public function array(): array
    {
        $estudiantes = $this->clase->estudiantes()->orderBy('nombre')->get();

        // Cargar todas las notas de estas actividades en una sola consulta
        $actIds = $this->actividades->pluck('id');
        $notas  = ActividadNota::whereIn('actividad_id', $actIds)
            ->get()
            ->groupBy('actividad_id')
            ->map(fn ($rows) => $rows->pluck('nota', 'estudiante_id'));

        // Fila de encabezado
        $headers = ['Carné', 'Nombre'];
        foreach ($this->actividades as $act) {
            $headers[] = $act->nombre . ' (Max: ' . number_format($act->punteo_max, 0) . ')';
        }

        $rows = [$headers];

        foreach ($estudiantes as $e) {
            $row = [$e->carnet, $e->nombre];
            foreach ($this->actividades as $act) {
                $nota  = $notas[$act->id][$e->id] ?? null;
                $row[] = $nota !== null ? (float) $nota : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths  = ['A' => 15, 'B' => 35];
        $letters = range('C', 'Z');
        foreach ($this->actividades as $i => $_) {
            $widths[$letters[$i] ?? 'Z'] = 22;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF000B60']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
