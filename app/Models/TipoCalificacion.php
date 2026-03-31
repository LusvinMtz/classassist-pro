<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCalificacion extends Model
{
    protected $table = 'tipo_calificacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'tipo_calificacion_id');
    }
}
