<?php

namespace App\Livewire\Asistencia;

use App\Models\Asistencia;
use App\Models\Sesion;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Registrar extends Component
{
    public string $token       = '';
    public string $carnet      = '';
    public string $claseNombre = '';
    public string $fecha       = '';
    public bool   $invalido    = false;
    public bool   $registrado  = false;
    public string $mensaje     = '';
    public string $tipo        = ''; // success | error | warning
    public string $selfieData  = ''; // base64 image from camera

    public function mount(string $token): void
    {
        $this->token = $token;

        $sesion = Sesion::where('token', $token)->with('clase')->first();

        if (!$sesion) {
            $this->invalido = true;
            $this->tipo     = 'error';
            $this->mensaje  = 'El código QR no es válido.';
            return;
        }

        if ($sesion->finalizada) {
            $this->invalido    = true;
            $this->tipo        = 'error';
            $this->mensaje     = 'Esta sesión ha sido finalizada. No se puede registrar asistencia.';
            $this->claseNombre = $sesion->clase->nombre;
            $this->fecha       = $sesion->fecha->translatedFormat('d/m/Y');
            return;
        }

        if ($sesion->expiracion <= now()) {
            $this->invalido    = true;
            $this->tipo        = 'warning';
            $this->mensaje     = 'El código QR ha expirado. Solicita al catedrático que genere uno nuevo.';
            $this->claseNombre = $sesion->clase->nombre;
            $this->fecha       = $sesion->fecha->translatedFormat('d/m/Y');
            return;
        }

        $this->claseNombre = $sesion->clase->nombre;
        $this->fecha       = $sesion->fecha->translatedFormat('d/m/Y');
    }

    public function registrar(): void
    {
        if ($this->invalido || $this->registrado) return;

        $this->validate(['carnet' => 'required|string']);

        $sesion = Sesion::where('token', $this->token)
            ->where('expiracion', '>', now())
            ->where('finalizada', false)
            ->with('clase')
            ->first();

        if (!$sesion) {
            $this->tipo    = 'error';
            $this->mensaje = 'El código QR ya no es válido, expiró o la sesión fue finalizada.';
            return;
        }

        $estudiante = $sesion->clase->estudiantes()
            ->where('carnet', trim($this->carnet))
            ->first();

        if (!$estudiante) {
            $this->tipo    = 'error';
            $this->mensaje = 'Carné no encontrado en esta clase. Verifica e intenta de nuevo.';
            return;
        }

        $yaRegistrado = Asistencia::where('sesion_id', $sesion->id)
            ->where('estudiante_id', $estudiante->id)
            ->exists();

        if ($yaRegistrado) {
            $this->tipo    = 'warning';
            $this->mensaje = 'Tu asistencia ya fue registrada anteriormente.';
            return;
        }

        $selfiePath = null;
        if ($this->selfieData && str_starts_with($this->selfieData, 'data:image/')) {
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $this->selfieData);
            $selfiePath = 'selfies/' . uniqid('s_', true) . '.jpg';
            Storage::disk('public')->put($selfiePath, base64_decode($base64));
        }

        Asistencia::create([
            'sesion_id'     => $sesion->id,
            'estudiante_id' => $estudiante->id,
            'selfie'        => $selfiePath,
        ]);

        $this->registrado = true;
        $this->tipo       = 'success';
        $this->mensaje    = "¡Asistencia registrada correctamente, {$estudiante->nombre}!";
        $this->carnet     = '';
    }

    public function render()
    {
        return view('livewire.asistencia.registrar');
    }
}
