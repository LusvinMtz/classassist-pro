<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sede';

    protected $fillable = ['nombre', 'codigo', 'direccion'];

    public function carreras()
    {
        return $this->belongsToMany(Carrera::class, 'sede_carrera', 'sede_id', 'carrera_id');
    }
}
