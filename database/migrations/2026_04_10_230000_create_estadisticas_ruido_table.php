<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estadistica_ruido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesion')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();

            // Métricas de la sesión de medición
            $table->decimal('db_minimo',  5, 1)->default(0);
            $table->decimal('db_maximo',  5, 1)->default(0);
            $table->decimal('db_promedio', 5, 1)->default(0);
            $table->unsignedSmallInteger('total_alertas')->default(0);
            $table->unsignedSmallInteger('umbral_db')->default(65);

            // Duración de la medición en segundos
            $table->unsignedInteger('duracion_segundos')->default(0);

            // Nivel predominante: silencio | bajo | moderado | alto | muy_alto
            $table->string('nivel_predominante', 20)->nullable();

            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estadistica_ruido');
    }
};
