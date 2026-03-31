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
            ['202300001', 'Juan Pérez García', 'juan@uni.edu'],
            ['202300002', 'María López', 'maria@uni.edu'],
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
