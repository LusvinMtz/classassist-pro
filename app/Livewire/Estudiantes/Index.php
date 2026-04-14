<?php

namespace App\Livewire\Estudiantes;

use App\Imports\EstudiantesImport;
use App\Models\Clase;
use App\Models\Estudiante;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Index extends Component
{
    use WithFileUploads;

    // ── Filtros ──────────────────────────────────────────────────────────────
    public ?int  $claseId = null;   // catedrático: selector de clase
    public string $search = '';     // admin: búsqueda global

    // ── Modal individual ─────────────────────────────────────────────────────
    public bool  $showModal = false;
    public ?int  $editingId = null;

    #[Validate('required|string|max:50|regex:/^\d{4}-\d{2}-\d+$/')]
    public string $carnet = '';

    #[Validate('required|string|max:100')]
    public string $nombre = '';

    #[Validate('nullable|email|max:100|regex:/@miumg\.edu\.gt$/')]
    public string $correo = '';

    // ── Modal QR inscripción ─────────────────────────────────────────────────
    public bool   $showQrModal = false;
    public string $qrSvg       = '';
    public string $qrUrl       = '';

    // ── Modal importar ───────────────────────────────────────────────────────
    public bool  $showImportModal = false;
    public       $archivo         = null;
    public array $erroresImport   = [];
    public int   $importados      = 0;

    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin: todos los estudiantes con sus clases
            $estudiantes = Estudiante::with('clases')
                ->when($this->search, function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%')
                           ->orWhere('carnet', 'like', '%' . $this->search . '%')
                           ->orWhere('correo', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('nombre')
                ->get();

            return view('livewire.estudiantes.index', [
                'esAdmin'     => true,
                'clases'      => collect(),
                'estudiantes' => $estudiantes,
            ]);
        }

        // Catedrático: selector de clase
        $clases      = $this->queryClases()->orderBy('nombre')->get();
        $estudiantes = collect();

        if ($this->claseId) {
            $clase = $this->queryClases()->find($this->claseId);
            if ($clase) {
                $estudiantes = $clase->estudiantes()->orderBy('nombre')->get();
            }
        }

        return view('livewire.estudiantes.index', [
            'esAdmin'     => false,
            'clases'      => $clases,
            'estudiantes' => $estudiantes,
        ]);
    }

    private function queryClases(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $porUsuarioId = Clase::where('usuario_id', $user->id)->pluck('id');
        $porPivot     = $user->clasesImpartidas()->pluck('clase.id');
        $ids          = $porUsuarioId->merge($porPivot)->unique();

        return Clase::whereIn('id', $ids);
    }

    // ── CRUD individual ──────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->reset(['carnet', 'nombre', 'correo', 'editingId']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Estudiante::findOrFail($id);
        $this->editingId = $id;
        $this->carnet    = $e->carnet;
        $this->nombre    = $e->nombre;
        $this->correo    = $e->correo ?? '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([], [
            'carnet.regex'  => 'El carné debe tener el formato: 0000-00-0000 (ej. 8590-21-16653).',
            'correo.regex'  => 'El correo debe ser institucional (@miumg.edu.gt).',
        ]);

        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin: edición global (solo editar, no crear sin clase)
            if (!$this->editingId) return;

            Estudiante::findOrFail($this->editingId)->update([
                'carnet'  => $this->carnet,
                'nombre'  => $this->nombre,
                'correo'  => $this->correo ?: null,
            ]);
        } else {
            // Catedrático: requiere clase seleccionada
            if (!$this->claseId) return;

            $clase = $this->queryClases()->findOrFail($this->claseId);

            $carnetDuplicado = $clase->estudiantes()
                ->where('carnet', $this->carnet)
                ->when($this->editingId, fn ($q) => $q->where('estudiante.id', '!=', $this->editingId))
                ->exists();

            if ($carnetDuplicado) {
                $this->addError('carnet', 'Ya existe un estudiante con este carné en la clase.');
                return;
            }

            if ($this->correo) {
                $correoDuplicado = $clase->estudiantes()
                    ->where('correo', $this->correo)
                    ->when($this->editingId, fn ($q) => $q->where('estudiante.id', '!=', $this->editingId))
                    ->exists();

                if ($correoDuplicado) {
                    $this->addError('correo', 'Ya existe un estudiante con este correo en la clase.');
                    return;
                }
            }

            $data = [
                'carnet' => $this->carnet,
                'nombre' => $this->nombre,
                'correo' => $this->correo ?: null,
            ];

            if ($this->editingId) {
                Estudiante::findOrFail($this->editingId)->update($data);
            } else {
                $estudiante = Estudiante::create($data);
                $clase->estudiantes()->attach($estudiante->id);
            }
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            Estudiante::findOrFail($id)->delete();
        } else {
            if (!$this->claseId) return;
            $this->queryClases()
                ->findOrFail($this->claseId)
                ->estudiantes()
                ->detach($id);
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['carnet', 'nombre', 'correo', 'editingId']);
        $this->resetValidation();
    }

    // ── Importar ─────────────────────────────────────────────────────────────

    public function openImport(): void
    {
        $this->reset(['archivo', 'erroresImport', 'importados']);
        $this->showImportModal = true;
    }

    public function importar(): void
    {
        $this->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        if (!$this->claseId) return;

        $clase  = $this->queryClases()->findOrFail($this->claseId);
        $import = new EstudiantesImport($clase);

        Excel::import($import, $this->archivo->getRealPath());

        $this->importados    = $import->importados;
        $this->erroresImport = $import->errores;
        $this->archivo       = null;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['archivo', 'erroresImport', 'importados']);
    }

    // ── QR de inscripción ────────────────────────────────────────────────────

    public function openQrInscripcion(): void
    {
        if (!$this->claseId) return;

        $clase = $this->queryClases()->findOrFail($this->claseId);

        // Generar token si no tiene uno vigente
        if (!$clase->token_inscripcion || !$clase->expiracion_inscripcion || $clase->expiracion_inscripcion <= now()) {
            $clase->update([
                'token_inscripcion'     => Str::random(40),
                'expiracion_inscripcion' => now()->addHours(24),
            ]);
        }

        $this->qrUrl = route('inscribirse', $clase->token_inscripcion);
        $this->qrSvg = (string) QrCode::format('svg')
            ->size(260)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($this->qrUrl);

        $this->showQrModal = true;
    }

    public function regenerarQr(): void
    {
        if (!$this->claseId) return;

        $clase = $this->queryClases()->findOrFail($this->claseId);
        $clase->update([
            'token_inscripcion'     => Str::random(40),
            'expiracion_inscripcion' => now()->addHours(24),
        ]);

        $this->qrUrl = route('inscribirse', $clase->token_inscripcion);
        $this->qrSvg = (string) QrCode::format('svg')
            ->size(260)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($this->qrUrl);
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
        $this->qrSvg       = '';
        $this->qrUrl       = '';
    }
}
