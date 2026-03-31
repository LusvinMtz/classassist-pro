<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupo';

    protected $fillable = [
        'sesion_id',
        'nombre',
    ];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'grupo_estudiante', 'grupo_id', 'estudiante_id');
    }
}
