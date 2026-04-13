<?php

namespace App\Providers;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Clase;
use App\Models\Estudiante;
use App\Models\User;
use App\Observers\AsistenciaObserver;
use App\Observers\CalificacionObserver;
use App\Observers\ClaseObserver;
use App\Observers\EstudianteObserver;
use App\Observers\UserObserver;
use App\Services\BitacoraService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Observers ────────────────────────────────────────────────────────
        User::observe(UserObserver::class);
        Clase::observe(ClaseObserver::class);
        Estudiante::observe(EstudianteObserver::class);
        Asistencia::observe(AsistenciaObserver::class);
        Calificacion::observe(CalificacionObserver::class);

        // ── Eventos de autenticación ─────────────────────────────────────────
        Event::listen(Login::class, function (Login $event) {
            BitacoraService::registrar(
                accion:      'login',
                modulo:      'Autenticacion',
                descripcion: "Inicio de sesión: \"{$event->user->nombre}\" ({$event->user->email})",
                entidadId:   $event->user->id,
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                BitacoraService::registrar(
                    accion:      'logout',
                    modulo:      'Autenticacion',
                    descripcion: "Cierre de sesión: \"{$event->user->nombre}\" ({$event->user->email})",
                    entidadId:   $event->user->id,
                );
            }
        });
    }
}
