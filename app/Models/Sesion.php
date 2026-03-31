<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
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
}
