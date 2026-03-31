<?php

namespace App\Exports\Sheets;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Sesion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetalleAsistenciaSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private int $claseId) {}

    public function title(): string { return 'Detalle Asistencia'; }

    public function headings(): array
    {
        return ['Fecha', 'Carné', 'Estudiante', 'Hora Registro', 'Sesión Estado'];
    }

    public function collection(): Collection
    {
        $sesionIds = Sesion::where('clase_id', $this->claseId)->pluck('id');

        return Asistencia::whereIn('sesion_id', $sesionIds)
            ->with(['estudiante', 'sesion'])
            ->orderBy('sesion_id')
            ->get()
            ->map(fn ($a) => [
                $a->sesion->fecha->format('d/m/Y'),
                $a->estudiante->carnet,
                $a->estudiante->nombre,
                \Carbon\Carbon::parse($a->fecha_hora)->format('H:i:s'),
                $a->sesion->finalizada ? 'Finalizada' : 'Activa',
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000B60']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
