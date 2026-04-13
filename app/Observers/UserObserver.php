<?php

namespace App\Observers;

use App\Models\User;
use App\Services\BitacoraService;

class UserObserver
{
    public function created(User $user): void
    {
        BitacoraService::registrarCreacion($user, 'Usuario', "Usuario creado: \"{$user->nombre}\" ({$user->email})");
    }

    public function updated(User $user): void
    {
        BitacoraService::registrarEdicion($user, 'Usuario', "Usuario editado: \"{$user->nombre}\" ({$user->email})");
    }

    public function deleted(User $user): void
    {
        BitacoraService::registrarEliminacion($user, 'Usuario', "Usuario eliminado: \"{$user->nombre}\" ({$user->email})");
    }

    public function restored(User $user): void
    {
        BitacoraService::registrarRestauracion($user, 'Usuario', "Usuario restaurado: \"{$user->nombre}\" ({$user->email})");
    }
}
