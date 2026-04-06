<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividad';

    public $timestamps = false;

    protected $fillable = [
        'clase_id',
        'nombre',
        'punteo_max',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'punteo_max' => 'decimal:2',
            'orden'      => 'integer',
        ];
    }

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'clase_id');
    }

    public function notas()
    {
        return $this->hasMany(ActividadNota::class, 'actividad_id');
    }
}
