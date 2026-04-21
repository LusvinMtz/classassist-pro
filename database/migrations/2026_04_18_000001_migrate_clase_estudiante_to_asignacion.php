<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Copia los registros de clase_estudiante a asignacion usando el año de created_at.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clase_estudiante')) {
            return;
        }

        DB::statement("
            INSERT INTO asignacion (estudiante_id, clase_id, anio, created_at, updated_at)
            SELECT
                ce.estudiante_id,
                ce.clase_id,
                YEAR(COALESCE(ce.created_at, NOW())),
                NOW(),
                NOW()
            FROM clase_estudiante ce
            WHERE NOT EXISTS (
                SELECT 1 FROM asignacion a
                WHERE a.estudiante_id = ce.estudiante_id
                  AND a.clase_id      = ce.clase_id
                  AND a.anio          = YEAR(COALESCE(ce.created_at, NOW()))
            )
        ");
    }

    public function down(): void {}
};
