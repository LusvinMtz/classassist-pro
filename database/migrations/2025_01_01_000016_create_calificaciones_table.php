<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->foreignId('clase_id')->constrained('clase')->cascadeOnDelete();
            $table->foreignId('tipo_calificacion_id')->constrained('tipo_calificacion');
            $table->decimal('nota', 5, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};
