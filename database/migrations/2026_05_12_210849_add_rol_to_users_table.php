<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'vendedor'])->default('vendedor')->after('email');
            $table->foreignId('comercio_id')->nullable()->after('rol')
                ->constrained('comercios')->onDelete('set null');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'comercio_id']);
        });
    }
};
