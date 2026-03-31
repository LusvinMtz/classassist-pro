<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'estado'            => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('nombre', $role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCatedratico(): bool
    {
        return $this->hasRole('catedratico');
    }

    public function isEstudiante(): bool
    {
        return $this->hasRole('estudiante');
    }

    public function clases()
    {
        return $this->hasMany(Clase::class, 'usuario_id');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'usuario_id');
    }
}
