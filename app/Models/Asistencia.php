<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencia';

    public $timestamps = false;

    protected $fillable = [
        'sesion_id',
        'estudiante_id',
        'selfie',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
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
