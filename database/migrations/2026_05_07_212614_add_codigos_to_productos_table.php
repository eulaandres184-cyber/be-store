<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('codigo_interno', 50)->nullable()->unique()->after('id');
            $table->string('codigo_barras', 50)->nullable()->unique()->after('codigo_interno');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['codigo_interno', 'codigo_barras']);
        });
    }
};