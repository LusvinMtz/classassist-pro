<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad_nota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_id')->constrained('actividad')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiante')->cascadeOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupo')->nullOnDelete();
            $table->decimal('nota', 5, 2)->nullable();
            $table->unique(['actividad_id', 'estudiante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_nota');
    }
};
