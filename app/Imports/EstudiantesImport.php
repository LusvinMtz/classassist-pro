<?php

namespace App\Imports;

use App\Models\Clase;
use App\Models\Estudiante;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EstudiantesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int   $importados = 0;
    public array $errores    = [];
    private int  $fila       = 1;

    public function __construct(private Clase $clase) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->fila++;

            // WithHeadingRow normaliza: "Carné" → "carne", "Correo Electrónico" → "correo_electronico"
            $carnet = trim((string) ($row['carne'] ?? $row['carnet'] ?? ''));
            $nombre = trim((string) ($row['estudiante'] ?? $row['nombre'] ?? ''));
            $correo = trim((string) ($row['correo_electronico'] ?? $row['correo'] ?? ''));

            if (!$carnet && !$nombre) {
                continue;
            }

            if (!$carnet) {
                $this->errores[] = "Fila {$this->fila}: carné vacío.";
                continue;
            }

            // Validar formato de carné: 0000-00-0000
            if (!preg_match('/^\d{4}-\d{2}-\d+$/', $carnet)) {
                $this->errores[] = "Fila {$this->fila}: el carné '{$carnet}' no tiene el formato correcto (ej. 8590-21-16653).";
                continue;
            }

            if (!$nombre) {
                $this->errores[] = "Fila {$this->fila}: nombre vacío.";
                continue;
            }

            // Validar formato de correo
            if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $this->errores[] = "Fila {$this->fila}: el correo '{$correo}' no es válido.";
                continue;
            }

            // Validar dominio institucional
            if ($correo && !str_ends_with(strtolower($correo), '@miumg.edu.gt')) {
                $this->errores[] = "Fila {$this->fila}: el correo '{$correo}' debe ser institucional (@miumg.edu.gt).";
                continue;
            }

            // Carnet único por clase
            if ($this->clase->estudiantes()->where('carnet', $carnet)->exists()) {
                $this->errores[] = "Fila {$this->fila}: el carné '{$carnet}' ya está registrado en esta clase.";
                continue;
            }

            // Correo único por clase (si se proporcionó)
            if ($correo && $this->clase->estudiantes()->where('correo', $correo)->exists()) {
                $this->errores[] = "Fila {$this->fila}: el correo '{$correo}' ya está registrado en esta clase.";
                continue;
            }

            $estudiante = Estudiante::firstOrCreate(
                ['carnet' => $carnet],
                ['nombre' => $nombre, 'correo' => $correo ?: null]
            );

            $this->clase->estudiantes()->attach($estudiante->id, ['anio' => now()->year]);
            $this->importados++;
        }
    }
}
