<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clase extends Model
{
    use SoftDeletes;
    protected $table = 'clase';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario_id',
        'carrera_id',
        'codigo',
        'ciclo',
        'token_inscripcion',
        'expiracion_inscripcion',
    ];

    protected $casts = [
        'ciclo'                  => 'integer',
        'expiracion_inscripcion' => 'datetime',
    ];

    /** Semestre según ciclo: impares = Ene-Jun, pares = Jul-Dic */
    public function getSemestreAttribute(): ?string
    {
        if (!$this->ciclo) return null;
        return $this->ciclo % 2 !== 0 ? 'Enero – Junio' : 'Julio – Diciembre';
    }

    public function catedratico()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function catedraticos()
    {
        return $this->belongsToMany(User::class, 'clase_catedratico', 'clase_id', 'usuario_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'clase_id');
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
