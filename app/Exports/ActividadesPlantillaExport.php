<?php

namespace App\Exports;

use App\Models\Actividad;
use App\Models\Clase;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActividadesPlantillaExport implements FromArray, WithStyles, WithColumnWidths
{
    private Clase $clase;
    /** @var Actividad[] */
    private array $actividades;

    public function __construct(Clase $clase, array $actividades)
    {
        $this->clase       = $clase;
        $this->actividades = $actividades;
    }

    public function array(): array
    {
        // Fila de encabezado
        $headers = ['Carné', 'Nombre'];
        foreach ($this->actividades as $act) {
            $headers[] = $act['nombre'] . ' (Max: ' . number_format($act['punteo_max'], 0) . ')';
        }

        // Filas de estudiantes
        $rows   = [$headers];
        $estudiantes = $this->clase->estudiantes()->orderBy('nombre')->get();

        foreach ($estudiantes as $e) {
            $row = [$e->carnet, $e->nombre];
            foreach ($this->actividades as $_) {
                $row[] = '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 15, 'B' => 35];
        $letters = range('C', 'Z');
        foreach ($this->actividades as $i => $_) {
            $widths[$letters[$i] ?? 'Z'] = 22;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        // Encabezado en negrita con fondo azul
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->actividades) + 2);

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF000B60']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
