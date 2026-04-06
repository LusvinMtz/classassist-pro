<?php

namespace App\Imports;

use App\Models\Actividad;
use App\Models\ActividadNota;
use App\Models\Estudiante;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ActividadesImport implements ToCollection, WithHeadingRow
{
    private int   $claseId;
    public  int   $importados  = 0;
    public  int   $errores     = 0;
    public  array $mensajes    = [];

    public function __construct(int $claseId)
    {
        $this->claseId = $claseId;
    }

    public function collection(Collection $rows): void
    {
        // Cargar actividades de esta clase indexadas por nombre normalizado
        $actividades = Actividad::where('clase_id', $this->claseId)
            ->orderBy('orden')
            ->get()
            ->keyBy(fn ($a) => $this->normalizar($a->nombre));

        if ($actividades->isEmpty()) {
            $this->mensajes[] = 'No hay actividades configuradas para esta clase.';
            return;
        }

        foreach ($rows as $rowNum => $row) {
            $carne = trim((string) ($row['carne'] ?? $row['carné'] ?? $row['carnet'] ?? ''));

            if ($carne === '') continue;

            $estudiante = Estudiante::whereHas('clases', fn ($q) => $q->where('clase_id', $this->claseId))
                ->where('carnet', $carne)
                ->first();

            if (!$estudiante) {
                $this->errores++;
                $this->mensajes[] = "Fila " . ($rowNum + 2) . ": carné '{$carne}' no encontrado en la clase.";
                continue;
            }

            // Recorrer columnas de actividades
            foreach ($row as $header => $valor) {
                if (in_array($header, ['carne', 'carné', 'carnet', 'nombre'], true)) continue;
                if ($valor === null || $valor === '') continue;

                // Normalizar header para buscar actividad
                // El header puede ser "tarea_1_max_10" o "tarea 1 (max: 10)"
                $headerNorm = $this->normalizar($header);

                // Buscar match por inicio del nombre de actividad
                $actividad = null;
                foreach ($actividades as $nombreNorm => $act) {
                    if (str_starts_with($headerNorm, $nombreNorm)) {
                        $actividad = $act;
                        break;
                    }
                }

                if (!$actividad) continue;

                $nota = (float) $valor;
                if ($nota < 0)                         $nota = 0;
                if ($nota > $actividad->punteo_max)    $nota = (float) $actividad->punteo_max;

                ActividadNota::updateOrCreate(
                    ['actividad_id'  => $actividad->id, 'estudiante_id' => $estudiante->id],
                    ['nota'          => round($nota, 2)]
                );
            }

            $this->importados++;
        }
    }

    private function normalizar(string $texto): string
    {
        // Quitar "(Max: X)", pasar a minúsculas, quitar caracteres especiales
        $texto = preg_replace('/\(max[^)]*\)/i', '', $texto);
        $texto = strtolower(trim($texto));
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
        return trim($texto, '_');
    }
}
