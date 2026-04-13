<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();

            // Quién realizó la acción (null = sistema o invitado)
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();

            // Tipo de acción
            $table->string('accion', 50);          // login, logout, crear, editar, eliminar, restaurar, exportar, etc.
            $table->string('modulo', 100);          // Clase, Estudiante, Asistencia, Calificacion, etc.
            $table->unsignedBigInteger('entidad_id')->nullable(); // ID del registro afectado

            // Descripción legible
            $table->text('descripcion');

            // Datos antes y después del cambio (para auditoría)
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();

            // Contexto de red
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Nivel de severidad
            $table->enum('nivel', ['info', 'advertencia', 'error'])->default('info');

            $table->timestamps();

            $table->index(['modulo', 'entidad_id']);
            $table->index('accion');
            $table->index('usuario_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
