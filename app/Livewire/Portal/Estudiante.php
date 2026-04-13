<?php

namespace App\Livewire\Portal;

use Livewire\Component;
use App\Models\Estudiante as EstudianteModel;

class Estudiante extends Component
{
    public string $carnet = '';
    public string $correo = '';
    public bool $buscado  = false;
    public string $error  = '';

    public ?EstudianteModel $estudiante = null;

    public function buscar(): void
    {
        $this->validate([
            'carnet' => 'required|string',
            'correo' => 'required|email',
        ], [
            'carnet.required' => 'El carnet es requerido.',
            'correo.required' => 'El correo es requerido.',
            'correo.email'    => 'Ingresa un correo válido.',
        ]);

        $this->error     = '';
        $this->buscado   = true;
        $this->estudiante = null;

        $found = EstudianteModel::where('carnet', trim($this->carnet))
            ->whereRaw('LOWER(correo) = ?', [strtolower(trim($this->correo))])
            ->first();

        if (! $found) {
            $this->error = 'No se encontró ningún estudiante con ese carnet y correo.';
            return;
        }

        $this->estudiante = $found->load([
            'clases.catedratico',
            'clases.sesiones',
            'asistencias.sesion.clase',
            'calificaciones.tipoCalificacion',
            'calificaciones.clase',
        ]);
    }

    public function limpiar(): void
    {
        $this->reset(['carnet', 'correo', 'buscado', 'error', 'estudiante']);
    }

    public function render()
    {
        return view('livewire.portal.estudiante');
    }
}
