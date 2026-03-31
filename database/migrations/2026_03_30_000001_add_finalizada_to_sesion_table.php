<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesion', function (Blueprint $table) {
            $table->boolean('finalizada')->default(false)->after('expiracion');
        });
    }

    public function down(): void
    {
        Schema::table('sesion', function (Blueprint $table) {
            $table->dropColumn('finalizada');
        });
    }
};
