<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesion')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participacion');
    }
};
