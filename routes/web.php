<?php

use Illuminate\Support\Facades\Route;

// ─── Páginas públicas ─────────────────────────────────────────────────────────
Route::get('/', fn() => view('welcome'))->name('welcome');
Route::get('/portal', fn() => view('portal.index'))->name('portal.index');

// ─── Módulo Admin (solo administradores) ─────────────────────────────────────
Route::middleware(['auth', 'verified', 'role.admin'])->prefix('admin')->group(function () {

    Route::view('',                   'admin.index')              ->name('admin.index');
    Route::view('usuarios',           'admin.usuarios')           ->name('admin.usuarios');
    Route::view('tipos-calificacion', 'admin.tipos-calificacion') ->name('admin.tipos-calificacion');
    Route::view('bitacora',           'admin.bitacora')           ->name('admin.bitacora');

});

// ─── Aplicación principal (administradores y catedráticos) ───────────────────
Route::middleware(['auth', 'verified', 'role.catedratico'])->group(function () {

    Route::view('dashboard',         'dashboard')              ->name('dashboard');
    Route::view('clases',            'clases.index')           ->name('clases.index');
    Route::view('estudiantes',       'estudiantes.index')      ->name('estudiantes.index');
    Route::view('sesiones',          'sesiones.index')           ->name('sesiones.index');
    Route::get('sesiones/{sesionId}/detalle', fn(int $sesionId) =>
        view('sesiones.detalle', ['sesionId' => $sesionId])
    )->name('sesiones.detalle');
    Route::view('asistencia',        'asistencia.index')       ->name('asistencia.index');
    Route::view('ruleta',            'ruleta.index')           ->name('ruleta.index');
    Route::view('grupos',            'grupos.index')           ->name('grupos.index');
    Route::view('temporizador',      'temporizador.index')     ->name('temporizador.index');
    Route::view('desempeno',         'desempeno.index')        ->name('desempeno.index');
    Route::view('historial-grupos',  'historial-grupos.index') ->name('historial-grupos.index');
    Route::view('medidor',           'medidor.index')          ->name('medidor.index');
    Route::view('exportacion',       'exportacion.index')      ->name('exportacion.index');
    Route::view('pantalla-clase',    'pantalla-clase.index')   ->name('pantalla-clase.index');
    Route::view('calificaciones',    'calificaciones.index')   ->name('calificaciones.index');
    Route::view('sedes',             'sedes.index')            ->name('sedes.index');
    Route::view('asignaciones',      'asignaciones.index')     ->name('asignaciones.index');

    // Plantilla de importación de estudiantes
    Route::get('estudiantes/plantilla', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\EstudiantesPlantillaExport(),
            'plantilla_estudiantes.xlsx'
        );
    })->name('estudiantes.plantilla');

    // Exportación Excel
    Route::get('exportacion/{claseId}/download', function (int $claseId) {
        $user  = auth()->user();
        $query = \App\Models\Clase::query();
        if (!$user->isAdmin()) {
            $query->where('usuario_id', $user->id);
        }
        $clase  = $query->findOrFail($claseId);
        $nombre = 'classassist_' . \Illuminate\Support\Str::slug($clase->nombre) . '_' . now()->format('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AsistenciaExport($clase->id, $clase->nombre), $nombre
        );
    })->name('exportacion.download');

});

// ─── Registro de asistencia (público — estudiantes sin cuenta) ────────────────
Route::get('/asistir/{token}', function (string $token) {
    return view('asistencia.registrar', ['token' => $token]);
})->name('asistir');

require __DIR__.'/auth.php';
