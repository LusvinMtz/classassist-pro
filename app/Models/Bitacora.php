<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';

    protected $fillable = [
        'usuario_id',
        'accion',
        'modulo',
        'entidad_id',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'user_agent',
        'nivel',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos'     => 'array',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id')->withTrashed();
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeDeModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeDeNivel($query, string $nivel)
    {
        return $query->where('nivel', $nivel);
    }

    public function scopeDeAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    // ── Helpers de presentación ──────────────────────────────────────────────

    public function nivelColor(): string
    {
        return match ($this->nivel) {
            'advertencia' => 'amber',
            'error'       => 'red',
            default       => 'blue',
        };
    }

    public function accionIcono(): string
    {
        return match ($this->accion) {
            'login'      => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9',
            'logout'     => 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75',
            'crear'      => 'M12 4.5v15m7.5-7.5h-15',
            'editar'     => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z',
            'eliminar'   => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',
            'restaurar'  => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
            'exportar'   => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3',
            default      => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
        };
    }
}
