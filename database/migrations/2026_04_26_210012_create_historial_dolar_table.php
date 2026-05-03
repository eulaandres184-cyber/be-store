<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('historial_dolar', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->decimal('valor_compra', 10, 2);
            $table->decimal('valor_venta', 10, 2);
            $table->string('fuente', 50)->default('bluelytics');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('historial_dolar');
    }
};