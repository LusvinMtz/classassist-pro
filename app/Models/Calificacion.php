<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificacion';

    public $timestamps = false;

    protected $fillable = [
        'estudiante_id',
        'clase_id',
        'tipo_calificacion_id',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'nota'       => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'clase_id');
    }

    public function tipoCalificacion()
    {
        return $this->belongsTo(TipoCalificacion::class, 'tipo_calificacion_id');
    }
}
