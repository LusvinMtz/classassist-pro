<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadNota extends Model
{
    protected $table = 'actividad_nota';

    public $timestamps = false;

    protected $fillable = [
        'actividad_id',
        'estudiante_id',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
        ];
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'actividad_id');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
}
