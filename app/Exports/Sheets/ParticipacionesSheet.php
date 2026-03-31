<?php

namespace App\Exports\Sheets;

use App\Models\Participacion;
use App\Models\Sesion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipacionesSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private int $claseId) {}

    public function title(): string { return 'Participaciones'; }

    public function headings(): array
    {
        return ['Fecha', 'Carné', 'Estudiante', 'Calificación', 'Comentario'];
    }

    public function collection(): Collection
    {
        $sesionIds = Sesion::where('clase_id', $this->claseId)->pluck('id');

        return Participacion::whereIn('sesion_id', $sesionIds)
            ->with(['estudiante', 'sesion'])
            ->orderBy('sesion_id')
            ->get()
            ->map(fn ($p) => [
                $p->sesion->fecha->format('d/m/Y'),
                $p->estudiante->carnet,
                $p->estudiante->nombre,
                $p->calificacion !== null ? number_format($p->calificacion, 1) : '—',
                $p->comentario ?? '—',
            ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000B60']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
