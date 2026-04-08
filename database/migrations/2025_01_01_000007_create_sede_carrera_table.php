<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Reemplaza sede_facultad: ahora la relación es directamente Sede ↔ Carrera.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sede_carrera', function (Blueprint $table) {
            $table->foreignId('sede_id')->constrained('sede')->cascadeOnDelete();
            $table->foreignId('carrera_id')->constrained('carrera')->cascadeOnDelete();
            $table->primary(['sede_id', 'carrera_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sede_carrera');
    }
};
