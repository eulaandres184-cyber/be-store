<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('tipo', ['ticket','factura','recibo','presupuesto']);
            $table->string('numero', 20); // ej: 0001-00000001
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('moneda', 10)->default('ARS');
            $table->decimal('dolar_blue', 10, 2)->nullable();
            $table->json('items'); // snapshot de los ítems al momento
            $table->json('pagos')->nullable(); // para recibos con múltiples métodos
            $table->text('observaciones')->nullable();
            $table->boolean('anulado')->default(false);
            $table->timestamp('emitido_en');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('documentos'); }
};