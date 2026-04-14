<?php

namespace App\Livewire\Estudiantes;

use App\Models\Clase;
use App\Models\Estudiante;
use Livewire\Component;

class Inscribirse extends Component
{
    public string $token      = '';
    public string $carnet     = '';
    public string $nombre     = '';
    public string $correo     = '';
    public string $claseNombre = '';
    public bool   $invalido   = false;
    public bool   $registrado = false;
    public string $mensaje    = '';
    public string $tipo       = ''; // success | error | warning

    public function mount(string $token): void
    {
        $this->token = $token;

        $clase = Clase::where('token_inscripcion', $token)->first();

        if (!$clase) {
            $this->invalido = true;
            $this->tipo     = 'error';
            $this->mensaje  = 'El código QR no es válido.';
            return;
        }

        if (!$clase->expiracion_inscripcion || $clase->expiracion_inscripcion <= now()) {
            $this->invalido    = true;
            $this->tipo        = 'warning';
            $this->mensaje     = 'El código QR ha expirado. Solicita al catedrático que genere uno nuevo.';
            $this->claseNombre = $clase->nombre;
            return;
        }

        $this->claseNombre = $clase->nombre;
    }

    public function inscribirse(): void
    {
        if ($this->invalido || $this->registrado) return;

        $this->validate([
            'carnet' => ['required', 'string', 'max:50', 'regex:/^\d{4}-\d{2}-\d+$/'],
            'nombre' => 'required|string|max:100',
            'correo' => ['required', 'email', 'max:100', 'regex:/@miumg\.edu\.gt$/'],
        ], [
            'carnet.required' => 'El carné es obligatorio.',
            'carnet.regex'    => 'El carné debe tener el formato: 0000-00-0000 (ej. 8590-21-16653).',
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo institucional es obligatorio.',
            'correo.email'    => 'Ingresa un correo electrónico válido.',
            'correo.regex'    => 'El correo debe ser institucional (@miumg.edu.gt).',
        ]);

        $clase = Clase::where('token_inscripcion', $this->token)
            ->where('expiracion_inscripcion', '>', now())
            ->first();

        if (!$clase) {
            $this->invalido = true;
            $this->tipo     = 'error';
            $this->mensaje  = 'El código QR ya no es válido o expiró.';
            return;
        }

        $carnet = trim($this->carnet);
        $correo = strtolower(trim($this->correo));

        // Carnet duplicado en clase
        if ($clase->estudiantes()->where('carnet', $carnet)->exists()) {
            $this->addError('carnet', 'Ya estás registrado en esta clase con este carné.');
            return;
        }

        // Correo duplicado en clase
        if ($clase->estudiantes()->where('correo', $correo)->exists()) {
            $this->addError('correo', 'Ya existe un estudiante con este correo en esta clase.');
            return;
        }

        $estudiante = Estudiante::firstOrCreate(
            ['carnet' => $carnet],
            ['nombre' => trim($this->nombre), 'correo' => $correo]
        );

        $clase->estudiantes()->syncWithoutDetaching([$estudiante->id]);

        $this->registrado  = true;
        $this->tipo        = 'success';
        $this->mensaje     = "¡Te has inscrito exitosamente en {$clase->nombre}, {$estudiante->nombre}!";
        $this->carnet      = '';
        $this->nombre      = '';
        $this->correo      = '';
    }

    public function render()
    {
        return view('livewire.estudiantes.inscribirse');
    }
}
