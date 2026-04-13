<?php

namespace App\Livewire\Admin;

use App\Models\Bitacora as BitacoraModel;
use Livewire\Component;
use Livewire\WithPagination;

class Bitacora extends Component
{
    use WithPagination;

    public string $busqueda   = '';
    public string $modulo     = '';
    public string $accion     = '';
    public string $nivel      = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    protected $queryString = [
        'busqueda'   => ['except' => ''],
        'modulo'     => ['except' => ''],
        'accion'     => ['except' => ''],
        'nivel'      => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
    ];

    public function updatingBusqueda(): void  { $this->resetPage(); }
    public function updatingModulo(): void    { $this->resetPage(); }
    public function updatingAccion(): void    { $this->resetPage(); }
    public function updatingNivel(): void     { $this->resetPage(); }
    public function updatingFechaDesde(): void { $this->resetPage(); }
    public function updatingFechaHasta(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'modulo', 'accion', 'nivel', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function render()
    {
        $query = BitacoraModel::with('usuario')
            ->orderByDesc('created_at');

        if ($this->busqueda) {
            $q = '%' . $this->busqueda . '%';
            $query->where(function ($qb) use ($q) {
                $qb->where('descripcion', 'like', $q)
                   ->orWhere('ip', 'like', $q)
                   ->orWhereHas('usuario', fn($u) => $u->where('nombre', 'like', $q)->orWhere('email', 'like', $q));
            });
        }

        if ($this->modulo)     $query->where('modulo', $this->modulo);
        if ($this->accion)     $query->where('accion', $this->accion);
        if ($this->nivel)      $query->where('nivel',  $this->nivel);
        if ($this->fechaDesde) $query->whereDate('created_at', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $query->whereDate('created_at', '<=', $this->fechaHasta);

        $registros = $query->paginate(30);

        $modulos = BitacoraModel::distinct()->orderBy('modulo')->pluck('modulo');
        $acciones = BitacoraModel::distinct()->orderBy('accion')->pluck('accion');

        return view('livewire.admin.bitacora', compact('registros', 'modulos', 'acciones'));
    }
}
