<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clase', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            // Campos UMG (opcionales, para vincular a malla curricular)
            $table->foreignId('carrera_id')->nullable()->constrained('carrera')->nullOnDelete();
            $table->string('codigo', 20)->nullable();
            $table->tinyInteger('ciclo')->nullable(); // 1-10
            // Evaluación de actividades
            $table->enum('metodo_actividades', ['porcentaje', 'puntos'])->default('porcentaje');
            $table->decimal('max_puntos_extra', 4, 1)->default(5.0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase');
    }
};
