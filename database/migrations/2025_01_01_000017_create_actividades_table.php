<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase_id')->constrained('clase')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->decimal('punteo_max', 5, 2)->default(100.00);
            $table->tinyInteger('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad');
    }
};
