<?php

namespace App\Livewire\Admin;

use App\Models\Rol;
use App\Models\User;
use Livewire\Component;

class Usuarios extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $nombre    = '';
    public string $email     = '';
    public string $password  = '';
    public int    $rolId     = 0;
    public bool   $estado    = true;

    public string $buscar = '';

    protected function rules(): array
    {
        $uniqueEmail = $this->editingId
            ? 'unique:users,email,' . $this->editingId
            : 'unique:users,email';

        return [
            'nombre'   => 'required|string|max:100',
            'email'    => "required|email|max:100|{$uniqueEmail}",
            'password' => $this->editingId ? 'nullable|min:6' : 'required|min:6',
            'rolId'    => 'required|exists:rol,id',
            'estado'   => 'boolean',
        ];
    }

    protected $messages = [
        'nombre.required'   => 'El nombre es obligatorio.',
        'email.required'    => 'El correo es obligatorio.',
        'email.unique'      => 'Ese correo ya está en uso.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        'rolId.required'    => 'Selecciona un rol.',
        'rolId.exists'      => 'El rol seleccionado no es válido.',
    ];

    public function openCreate(): void
    {
        $this->reset(['nombre', 'email', 'password', 'estado', 'editingId']);
        $this->rolId     = Rol::where('nombre', 'catedratico')->value('id') ?? 0;
        $this->estado    = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);
        $this->editingId = $id;
        $this->nombre    = $user->nombre;
        $this->email     = $user->email;
        $this->password  = '';
        $this->rolId     = $user->roles->first()?->id ?? 0;
        $this->estado    = $user->estado;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $data = ['nombre' => $this->nombre, 'email' => $this->email, 'estado' => $this->estado];
            if ($this->password) {
                $data['password'] = $this->password;
            }
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

        $this->showModal = false;
        $this->reset(['nombre', 'email', 'password', 'editingId']);
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            return; // No puede eliminarse a sí mismo
        }
        User::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        $usuarios = User::with('roles')
            ->when($this->buscar, fn($q) => $q->where('nombre', 'like', "%{$this->buscar}%")
                ->orWhere('email', 'like', "%{$this->buscar}%"))
            ->orderBy('nombre')
            ->get();

        return view('livewire.admin.usuarios', [
            'usuarios' => $usuarios,
            'roles'    => Rol::orderBy('nombre')->get(),
        ]);
    }
}
