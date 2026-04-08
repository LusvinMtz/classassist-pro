<?php

use Illuminate\Database\Migrations\Migration;

// Facultades eliminadas del diseño — la relación es directamente Sede ↔ Carrera.
return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
