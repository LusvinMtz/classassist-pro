<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sesion extends Model
{
    use SoftDeletes;
    protected $table = 'sesion';

    protected $fillable = [
        'clase_id',
        'fecha',
        'token',
        'expiracion',
        'finalizada',
    ];

    protected function casts(): array
    {
        return [
            'fecha'      => 'date',
            'expiracion' => 'datetime',
            'finalizada' => 'boolean',
        ];
    }

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'clase_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'sesion_id');
    }

    public function participaciones()
    {
        return $this->hasMany(Participacion::class, 'sesion_id');
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'sesion_id');
    }

    public function estadisticasRuido()
    {
        return $this->hasMany(EstadisticaRuido::class, 'sesion_id');
    }

    /**
     * Una sesión es operativa solo si pertenece al día de hoy y no está finalizada.
     * Las sesiones de días anteriores se tratan como cerradas aunque el scheduler
     * no haya corrido todavía.
     */
    public function esOperativa(): bool
    {
        return !$this->finalizada && $this->fecha->isToday();
    }
}
