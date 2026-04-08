<?php

namespace App\Livewire\PantallaClase;

use App\Models\Asistencia;
use App\Models\Clase;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Participacion;
use App\Models\Sesion;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Index extends Component
{
    public ?int   $claseId  = null;
    public ?int   $sesionId = null;
    public string $tab      = 'qr';

    public ?int   $ganadorId     = null;
    public string $ganadorNombre = '';
    public bool   $showModal     = false;

    #[Validate('nullable|numeric|min:0|max:10')]
    public ?string $calificacion = null;

    #[Validate('nullable|string|max:500')]
    public string $comentario = '';

    public function mount(): void
    {
        // Sesión pasada por query string desde el listado de sesiones
        if (request()->filled('sesionId')) {
            $sesion = Sesion::find((int) request()->query('sesionId'));
            if ($sesion) {
                $this->sesionId = $sesion->id;
                $this->claseId  = $sesion->clase_id;
                return;
            }
        }

        $user = auth()->user();
        if (!$user->isAdmin()) {
            $sesionActiva = $this->sesionActivaCatedratico();
            if ($sesionActiva) {
                $this->sesionId = $sesionActiva->id;
                $this->claseId  = $sesionActiva->clase_id;
            }
        }
    }

    private function sesionActivaCatedratico(): ?Sesion
    {
        $user = auth()->user();
        if ($user->isAdmin()) return null;
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Sesion::whereIn('clase_id', $ids)
            ->where('finalizada', false)
            ->latest()
            ->first();
    }

    private function queryClases(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return Clase::query();
        }
        $ids = $user->clasesImpartidas()->pluck('clase.id');
        return Clase::whereIn('id', $ids);
    }

    public function render()
    {
        $user            = auth()->user();
        $esCatedratico   = !$user->isAdmin();
        $sinSesionActiva = $esCatedratico && !$this->sesionId;
        $clases = $this->queryClases()->orderBy('nombre')->get();
        $sesion = $this->sesionId ? Sesion::with('clase')->find($this->sesionId) : null;

        $qrSvg            = null;
        $qrUrl            = null;
        $asistentes       = collect();
        $totalEstudiantes = 0;
        $presentes        = collect();
        $historial        = collect();
        $grupos           = collect();

        if ($sesion) {
            if ($sesion->token && $sesion->expiracion > now()) {
                $qrUrl = route('asistir', $sesion->token);
                $qrSvg = QrCode::format('svg')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($qrUrl);
            }

            $asistentes = Asistencia::where('sesion_id', $sesion->id)
                ->with('estudiante')
                ->orderBy('fecha_hora')
                ->get();

            $totalEstudiantes = $sesion->clase->estudiantes()->count();

            $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->orderBy('nombre')->get();

            $historial = Participacion::where('sesion_id', $this->sesionId)
                ->with('estudiante')
                ->latest()
                ->get();

            $grupos = Grupo::where('sesion_id', $this->sesionId)
                ->with('estudiantes')
                ->orderBy('id')
                ->get();
        }

        return view('livewire.pantalla-clase.index', compact(
            'clases', 'sesion',
            'qrSvg', 'qrUrl', 'asistentes', 'totalEstudiantes',
            'presentes', 'historial', 'grupos',
            'esCatedratico', 'sinSesionActiva'
        ));
    }

    public function updatedClaseId(): void
    {
        if (!$this->claseId) {
            $this->sesionId = null;
            return;
        }

        $this->queryClases()->findOrFail($this->claseId);

        $sesion = Sesion::where('clase_id', $this->claseId)
            ->whereDate('fecha', today())
            ->first();

        $this->sesionId = $sesion?->id;
        $this->tab      = 'qr';
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    /* ─── QR ─────────────────────────────────────────────────────────── */

    public function generarQR(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if ($sesion->finalizada) return;

        $sesion->update([
            'token'      => Str::random(40),
            'expiracion' => now()->addMinutes(5),
        ]);
    }

    /* ─── Ruleta ─────────────────────────────────────────────────────── */

    public function girar(): void
    {
        if (!$this->sesionId) return;

        $sesion = Sesion::findOrFail($this->sesionId);
        if ($sesion->finalizada) return;

        $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
            $q->where('sesion_id', $this->sesionId)
        )->get();

        if ($presentes->isEmpty()) return;

        $ganador = $presentes->random();

        $this->ganadorId     = $ganador->id;
        $this->ganadorNombre = $ganador->nombre;

        $this->dispatch('iniciar-ruleta-pantalla',
            ganadorNombre: $ganador->nombre,
            nombres: $presentes->pluck('nombre')->shuffle()->values()->toArray(),
        );
    }

    public function seleccionarGanador(): void
    {
        $this->showModal = true;
    }

    public function guardarParticipacion(): void
    {
        $this->validate();

        if (!$this->ganadorId || !$this->sesionId) return;

        Participacion::create([
            'sesion_id'     => $this->sesionId,
            'estudiante_id' => $this->ganadorId,
            'calificacion'  => $this->calificacion !== '' ? $this->calificacion : null,
            'comentario'    => $this->comentario ?: null,
        ]);

        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function omitir(): void
    {
        $this->cerrarModal();
        $this->reset(['ganadorId', 'ganadorNombre']);
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->reset(['calificacion', 'comentario']);
        $this->resetValidation();
    }
}