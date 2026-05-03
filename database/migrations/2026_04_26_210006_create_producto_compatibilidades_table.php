<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('producto_compatibilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('modelo_id')->constrained('modelos_celular')->onDelete('cascade');
            $table->decimal('precio_efectivo_override', 12, 2)->nullable();
            $table->integer('stock_modelo')->default(0);
            $table->timestamps();
            $table->unique(['producto_id', 'modelo_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('producto_compatibilidades');
    }
};