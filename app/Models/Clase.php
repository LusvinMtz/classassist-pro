<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    protected $table = 'clase';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario_id',
    ];

    public function catedratico()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'clase_estudiante', 'clase_id', 'estudiante_id');
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class, 'clase_id');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'clase_id');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'clase_id')->orderBy('orden');
    }
}
