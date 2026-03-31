<?php

namespace App\Livewire\Grupos;

use App\Models\Clase;
use App\Models\Estudiante;
use App\Models\Grupo;
use App\Models\Sesion;
use Livewire\Component;

class Index extends Component
{
    public ?int  $claseId  = null;
    public ?int  $sesionId = null;
    public string $modo    = 'grupos';   // 'grupos' | 'tamano'
    public int   $cantidad = 4;
    public array $preview  = [];         // grupos generados pero no guardados aún
    public bool  $generado = false;

    public function render()
    {
        $clases  = Clase::where('usuario_id', auth()->id())->get();
        $sesion  = $this->sesionId ? Sesion::find($this->sesionId) : null;
        $presentes = collect();
        $guardados = collect();

        if ($sesion) {
            $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
                $q->where('sesion_id', $this->sesionId)
            )->orderBy('nombre')->get();

            $guardados = Grupo::where('sesion_id', $this->sesionId)
                ->with('estudiantes')
                ->orderBy('id')
                ->get();
        }

        return view('livewire.grupos.index', compact(
            'clases', 'sesion', 'presentes', 'guardados'
        ));
    }

    public function updatedClaseId(): void
    {
        if (!$this->claseId) {
            $this->sesionId = null;
            $this->resetGenerado();
            return;
        }

        Clase::where('usuario_id', auth()->id())->findOrFail($this->claseId);

        $sesion = Sesion::where('clase_id', $this->claseId)
            ->whereDate('fecha', today())
            ->where('finalizada', false)
            ->first();

        $this->sesionId = $sesion?->id;
        $this->resetGenerado();
    }

    public function generar(): void
    {
        $this->validate([
            'cantidad' => 'required|integer|min:2|max:50',
        ], [
            'cantidad.min' => 'El valor mínimo es 2.',
            'cantidad.max' => 'El valor máximo es 50.',
        ]);

        if (!$this->sesionId) return;

        $presentes = Estudiante::whereHas('asistencias', fn ($q) =>
            $q->where('sesion_id', $this->sesionId)
        )->get()->shuffle();

        if ($presentes->isEmpty()) return;

        $total = $presentes->count();

        if ($this->modo === 'grupos') {
            $numGrupos = min($this->cantidad, $total);
        } else {
            $numGrupos = (int) ceil($total / max(1, $this->cantidad));
        }

        $chunks = $presentes->chunk((int) ceil($total / $numGrupos));

        $this->preview = [];
        foreach ($chunks as $i => $chunk) {
            $this->preview[] = [
                'nombre'    => 'Grupo ' . ($i + 1),
                'miembros'  => $chunk->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()->toArray(),
            ];
        }

        $this->generado = true;
    }

    public function guardar(): void
    {
        if (!$this->sesionId || empty($this->preview)) return;

        // Elimina grupos anteriores de esta sesión
        Grupo::where('sesion_id', $this->sesionId)->delete();

        foreach ($this->preview as $g) {
            $grupo = Grupo::create([
                'sesion_id' => $this->sesionId,
                'nombre'    => $g['nombre'],
            ]);

            $grupo->estudiantes()->attach(
                collect($g['miembros'])->pluck('id')->toArray()
            );
        }

        $this->resetGenerado();
    }

    public function eliminarGrupos(): void
    {
        if (!$this->sesionId) return;
        Grupo::where('sesion_id', $this->sesionId)->delete();
        $this->resetGenerado();
    }

    private function resetGenerado(): void
    {
        $this->preview  = [];
        $this->generado = false;
    }
}
