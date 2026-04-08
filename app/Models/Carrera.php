<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    protected $table = 'carrera';

    protected $fillable = ['nombre', 'codigo'];

    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'sede_carrera', 'carrera_id', 'sede_id');
    }

    public function clases()
    {
        return $this->hasMany(Clase::class, 'carrera_id');
    }
}
