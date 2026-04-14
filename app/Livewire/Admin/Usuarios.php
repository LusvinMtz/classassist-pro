<?php

namespace App\Livewire\Admin;

use App\Models\Clase;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    // Selección directa de clases (catedrático)
    public array  $clasesSeleccionadas = [];
    public string $buscarClase        = '';

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

    /** Clases ya tomadas por OTROS catedráticos: [clase_id => nombre_catedratico] */
    private function clasesOcupadas(): array
    {
        $query = DB::table('clase_catedratico')
            ->join('users', 'users.id', '=', 'clase_catedratico.usuario_id')
            ->select('clase_catedratico.clase_id', 'users.nombre');

        if ($this->editingId) {
            $query->where('clase_catedratico.usuario_id', '!=', $this->editingId);
        }

        return $query->pluck('users.nombre', 'clase_catedratico.clase_id')->toArray();
    }

    private function clasesDisponibles(): \Illuminate\Support\Collection
    {
        return Clase::when($this->buscarClase, fn($q) => $q->where('nombre', 'like', '%' . $this->buscarClase . '%'))
            ->orderBy('ciclo')
            ->orderBy('nombre')
            ->get();
    }

    public function openCreate(): void
    {
        $this->reset(['nombre', 'email', 'password', 'estado', 'editingId',
                      'clasesSeleccionadas', 'buscarClase']);
        $this->rolId  = Rol::where('nombre', 'catedratico')->value('id') ?? 0;
        $this->estado = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::with(['roles', 'clasesImpartidas'])->findOrFail($id);
        $this->editingId = $id;
        $this->nombre    = $user->nombre;
        $this->email     = $user->email;
        $this->password  = '';
        $this->rolId     = $user->roles->first()?->id ?? 0;
        $this->estado    = $user->estado;

        $this->clasesSeleccionadas = $user->clasesImpartidas->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $this->buscarClase = '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if (count($this->clasesSeleccionadas) > 6) {
            $this->addError('clasesSeleccionadas', 'Un catedrático puede impartir máximo 6 clases.');
            return;
        }

        // Guard: ninguna clase seleccionada puede estar ya asignada a otro catedrático
        $ocupadas = array_keys($this->clasesOcupadas());
        $conflicto = array_intersect(array_map('intval', $this->clasesSeleccionadas), $ocupadas);
        if (!empty($conflicto)) {
            $this->addError('clasesSeleccionadas', 'Una o más clases ya están asignadas a otro catedrático.');
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
                      'clasesSeleccionadas', 'buscarClase']);
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
        $clasesDisponibles = $this->clasesDisponibles();
        $clasesOcupadas    = $this->clasesOcupadas();

        return view('livewire.admin.usuarios', [
            'usuarios' => User::with(['roles', 'clasesImpartidas'])
                ->when($this->buscar, fn($q) => $q->where('nombre', 'like', "%{$this->buscar}%")
                    ->orWhere('email', 'like', "%{$this->buscar}%"))
                ->orderBy('nombre')
                ->get(),
            'roles'            => Rol::whereNotIn('nombre', ['estudiante'])->orderBy('nombre')->get(),
            'clasesDisponibles' => $clasesDisponibles,
            'clasesOcupadas'    => $clasesOcupadas,
            'rolCatedraticoId'  => $rolCatedraticoId,
        ]);
    }
}
