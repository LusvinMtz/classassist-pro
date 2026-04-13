<?php

use App\Models\Sesion;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Auto-finalizar sesiones que quedaron abiertas al terminar el día.
 * Se ejecuta diariamente a las 23:59.
 * Garantiza que ninguna sesión permanezca activa después de su fecha.
 */
Schedule::call(function () {
    Sesion::where('finalizada', false)
        ->whereDate('fecha', '<', today())
        ->update([
            'finalizada' => true,
            'token'      => null,
            'expiracion' => null,
        ]);
})->dailyAt('23:59')->name('sesiones:auto-finalizar')->withoutOverlapping();
