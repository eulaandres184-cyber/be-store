<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->enum('tipo', ['falla', 'cambio', 'devolucion_dinero']);
            $table->text('motivo');
            $table->decimal('monto_devuelto', 14, 2)->default(0);
            $table->string('medio_devolucion', 50)->nullable();
            // Si es cambio, referencia al nuevo producto
            $table->foreignId('producto_nuevo_id')->nullable()->constrained('productos')->onDelete('set null');
            $table->integer('cantidad')->default(1);
            $table->boolean('stock_repuesto')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
