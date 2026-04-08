<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignacion';

    protected $fillable = ['estudiante_id', 'clase_id', 'anio'];

    protected $casts = ['anio' => 'integer'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'clase_id');
    }
}
