<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadisticaRuido extends Model
{
    protected $table = 'estadistica_ruido';

    protected $fillable = [
        'sesion_id', 'usuario_id',
        'db_minimo', 'db_maximo', 'db_promedio',
        'total_alertas', 'umbral_db', 'duracion_segundos',
        'nivel_predominante', 'iniciado_en', 'finalizado_en',
    ];

    protected function casts(): array
    {
        return [
            'db_minimo'      => 'decimal:1',
            'db_maximo'      => 'decimal:1',
            'db_promedio'    => 'decimal:1',
            'iniciado_en'    => 'datetime',
            'finalizado_en'  => 'datetime',
        ];
    }

    public function sesion()
    {
        return $this->belongsTo(Sesion::class, 'sesion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
