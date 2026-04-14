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
            // QR de inscripción de estudiantes
            $table->string('token_inscripcion', 255)->nullable();
            $table->timestamp('expiracion_inscripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase');
    }
};
