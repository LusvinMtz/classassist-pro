<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participacion extends Model
{
    protected $table = 'participacion';

    protected $fillable = [
        'sesion_id',
        'estudiante_id',
        'calificacion',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:2',
        ];
    }

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
