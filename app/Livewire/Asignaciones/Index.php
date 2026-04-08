<?php

namespace App\Livewire\Asignaciones;

use App\Models\Asignacion;
use App\Models\Carrera;
use App\Models\Clase;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int   $filtroCarrera = null;
    public ?int   $filtroClase   = null;
    public string $filtroAnio    = '';
    public string $busqueda      = '';

    public function updatingBusqueda(): void      { $this->resetPage(); }
    public function updatingFiltroCarrera(): void { $this->filtroClase = null; $this->resetPage(); }
    public function updatingFiltroClase(): void   { $this->resetPage(); }
    public function updatingFiltroAnio(): void    { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $query = Asignacion::with(['estudiante', 'clase.carrera'])
            ->orderBy('anio', 'desc');

        if ($this->filtroCarrera) {
            $query->whereHas('clase', fn($q) => $q->where('carrera_id', $this->filtroCarrera));
        }
        if ($this->filtroClase) {
            $query->where('clase_id', $this->filtroClase);
        }
        if ($this->filtroAnio) {
            $query->where('anio', (int) $this->filtroAnio);
        }
        if ($this->busqueda) {
            $query->whereHas('estudiante', fn($q) =>
                $q->where('nombre', 'like', '%' . $this->busqueda . '%')
                  ->orWhere('carnet', 'like', '%' . $this->busqueda . '%')
            );
        }

        $clasesDeFiltro = $this->filtroCarrera
            ? Clase::where('carrera_id', $this->filtroCarrera)->orderBy('ciclo')->orderBy('nombre')->get()
            : Clase::orderBy('nombre')->get();

        return view('livewire.asignaciones.index', [
            'asignaciones'   => $query->paginate(20),
            'carreras'       => Carrera::orderBy('nombre')->get(),
            'clasesDeFiltro' => $clasesDeFiltro,
        ]);
    }
}
