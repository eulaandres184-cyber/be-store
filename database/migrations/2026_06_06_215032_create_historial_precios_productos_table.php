<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('historial_precios_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->decimal('precio_anterior', 14, 2)->default(0);
            $table->decimal('precio_nuevo', 14, 2);
            $table->string('moneda_anterior', 10)->default('ARS');
            $table->string('moneda_nueva', 10)->default('ARS');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->string('razon_cambio', 200)->nullable();
            $table->timestamp('cambio_en');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('historial_precios_productos');
    }
};