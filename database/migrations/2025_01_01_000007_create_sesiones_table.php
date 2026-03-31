<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clase_id')->constrained('clase')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('token', 255)->nullable();
            $table->dateTime('expiracion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesion');
    }
};
