<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';

    protected $fillable = [
        'carnet',
        'nombre',
        'correo',
        'usuario_id'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function clases()
    {
        return $this->belongsToMany(Clase::class, 'clase_estudiante', 'estudiante_id', 'clase_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'estudiante_id');
    }

    public function participaciones()
    {
        return $this->hasMany(Participacion::class, 'estudiante_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_estudiante', 'estudiante_id', 'grupo_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'estudiante_id');
    }
}