<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('fecha');
            $table->enum('medio_pago', ['efectivo', 'transferencia', 'tarjeta', 'cuotas_4', 'cuotas_20']);
            $table->decimal('subtotal_ars', 14, 2);
            $table->decimal('recargo_aplicado', 5, 2)->default(0);
            $table->decimal('total_ars', 14, 2);
            $table->decimal('dolar_blue_usado', 10, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ventas');
    }
};