<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->foreignId('clase_id')->constrained('clase')->cascadeOnDelete();
            $table->smallInteger('anio'); // año de la asignación
            $table->timestamps();
            // Permite repetir la clase en distinto año; no permite duplicado mismo clase+año
            $table->unique(['estudiante_id', 'clase_id', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion');
    }
};
