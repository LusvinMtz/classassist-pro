<?php

namespace App\Livewire\Admin;

use App\Models\Carrera;
use App\Models\Clase;
use App\Models\Rol;
use App\Models\Sede;
use App\Models\User;
use Livewire\Component;

class Usuarios extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $nombre   = '';
    public string $email    = '';
    public string $password = '';
    public int    $rolId    = 0;
    public bool   $estado   = true;

    // Filtros en cascada (catedrático)
    public array  $sedesSeleccionadas    = [];
    public array  $carrerasSeleccionadas = [];
    public array  $clasesSeleccionadas   = [];
    public string $buscarClase           = '';

    public string $buscar = '';

    protected function rules(): array
    {
        $uniqueEmail = $this->editingId
            ? 'unique:users,email,' . $this->editingId
            : 'unique:users,email';

        return [
            'nombre'               => 'required|string|max:100',
            'email'                => "required|email|max:100|{$uniqueEmail}",
            'password'             => $this->editingId ? 'nullable|min:6' : 'required|min:6',
            'rolId'                => 'required|exists:rol,id',
            'estado'               => 'boolean',
            'clasesSeleccionadas'  => 'array|max:6',
            'clasesSeleccionadas.*'=> 'exists:clase,id',
        ];
    }

    protected $messages = [
        'nombre.required'         => 'El nombre es obligatorio.',
        'email.required'          => 'El correo es obligatorio.',
        'email.unique'            => 'Ese correo ya está en uso.',
        'password.required'       => 'La contraseña es obligatoria.',
        'password.min'            => 'La contraseña debe tener al menos 6 caracteres.',
        'rolId.required'          => 'Selecciona un rol.',
        'clasesSeleccionadas.max' => 'Un catedrático puede impartir máximo 6 clases.',
    ];

    // Cuando cambian sedes: limpiar carreras y clases que ya no apliquen
    public function updatedSedesSeleccionadas(): void
    {
        $carrerasValidas = $this->carrerasDisponibles()->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->carrerasSeleccionadas = array_values(array_intersect($this->carrerasSeleccionadas, $carrerasValidas));

        $clasesValidas = $this->clasesDisponibles()->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->clasesSeleccionadas = array_values(array_intersect($this->clasesSeleccionadas, $clasesValidas));
    }

    // Cuando cambian carreras: limpiar clases que ya no apliquen
    public function updatedCarrerasSeleccionadas(): void
    {
        $clasesValidas = $this->clasesDisponibles()->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->clasesSeleccionadas = array_values(array_intersect($this->clasesSeleccionadas, $clasesValidas));
    }

    // Todas las carreras disponibles (el filtro de sede no restringe carreras)
    private function carrerasDisponibles(): \Illuminate\Support\Collection
    {
        if (empty($this->sedesSeleccionadas)) {
            return collect();
        }
        return Carrera::orderBy('nombre')->get();
    }

    // Clases disponibles según carreras seleccionadas + búsqueda
    private function clasesDisponibles(): \Illuminate\Support\Collection
    {
        if (empty($this->carrerasSeleccionadas)) {
            return collect();
        }
        return Clase::whereIn('carrera_id', $this->carrerasSeleccionadas)
            ->when($this->buscarClase, fn($q) => $q->where('nombre', 'like', '%' . $this->buscarClase . '%'))
            ->orderBy('ciclo')
            ->orderBy('nombre')
            ->get();
    }

    public function openCreate(): void
    {
        $this->reset(['nombre', 'email', 'password', 'estado', 'editingId',
                      'sedesSeleccionadas', 'carrerasSeleccionadas', 'clasesSeleccionadas', 'buscarClase']);
        $this->rolId  = Rol::where('nombre', 'catedratico')->value('id') ?? 0;
        $this->estado = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::with(['roles', 'clasesImpartidas.carrera.sedes'])->findOrFail($id);
        $this->editingId = $id;
        $this->nombre    = $user->nombre;
        $this->email     = $user->email;
        $this->password  = '';
        $this->rolId     = $user->roles->first()?->id ?? 0;
        $this->estado    = $user->estado;

        // Reconstruir sedes y carreras a partir de las clases asignadas
        $this->clasesSeleccionadas   = $user->clasesImpartidas->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->carrerasSeleccionadas = $user->clasesImpartidas->pluck('carrera_id')->filter()->unique()->map(fn($v) => (string)$v)->toArray();
        $sedeIds = $user->clasesImpartidas->flatMap(fn($c) => $c->carrera?->sedes->pluck('id') ?? collect())->unique()->map(fn($v) => (string)$v)->toArray();
        $this->sedesSeleccionadas = $sedeIds;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if (count($this->clasesSeleccionadas) > 6) {
            $this->addError('clasesSeleccionadas', 'Un catedrático puede impartir máximo 6 clases.');
            return;
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $data = ['nombre' => $this->nombre, 'email' => $this->email, 'estado' => $this->estado];
            if ($this->password) $data['password'] = $this->password;
            $user->update($data);
            $user->roles()->sync([$this->rolId]);
        } else {
            $user = User::create([
                'nombre'   => $this->nombre,
                'email'    => $this->email,
                'password' => $this->password,
                'estado'   => $this->estado,
            ]);
            $user->roles()->attach($this->rolId);
        }

        $rolNombre = Rol::find($this->rolId)?->nombre;
        if ($rolNombre === 'catedratico') {
            $user->clasesImpartidas()->sync($this->clasesSeleccionadas);
        } else {
            $user->clasesImpartidas()->detach();
        }

        $this->showModal = false;
        $this->reset(['nombre', 'email', 'password', 'editingId',
                      'sedesSeleccionadas', 'carrerasSeleccionadas', 'clasesSeleccionadas', 'buscarClase']);
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) return;
        User::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        $rolCatedraticoId = Rol::where('nombre', 'catedratico')->value('id');

        $carrerasDisponibles = $this->carrerasDisponibles();
        $clasesDisponibles   = $this->clasesDisponibles();

        return view('livewire.admin.usuarios', [
            'usuarios'            => User::with(['roles', 'clasesImpartidas'])
                ->when($this->buscar, fn($q) => $q->where('nombre', 'like', "%{$this->buscar}%")
                    ->orWhere('email', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->get(),
            'roles'               => Rol::whereNotIn('nombre', ['estudiante'])->orderBy('nombre')->get(),
            'sedes'               => Sede::orderBy('nombre')->get(),
            'carrerasDisponibles' => $carrerasDisponibles,
            'clasesDisponibles'   => $clasesDisponibles,
            'rolCatedraticoId'    => $rolCatedraticoId,
        ]);
    }
}
