<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clase_catedratico', function (Blueprint $table) {
            $table->foreignId('clase_id')->constrained('clase')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['clase_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clase_catedratico');
    }
};
