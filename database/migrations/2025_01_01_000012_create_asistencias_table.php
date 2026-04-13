<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesion')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->timestamp('fecha_hora')->useCurrent();
            $table->string('selfie', 255)->nullable();
            $table->decimal('latitud',  10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->unique(['sesion_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia');
    }
};
