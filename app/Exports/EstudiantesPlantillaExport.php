<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstudiantesPlantillaExport implements FromArray, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            ['Carné', 'Estudiante', 'Correo Electrónico'],
            ['8590-21-16653', 'Juan Pérez García', 'jperez@miumg.edu.gt'],
            ['8590-20-15473', 'María López García', 'mlopez@miumg.edu.gt'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF000B60']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 35,
            'C' => 35,
        ];
    }
}
