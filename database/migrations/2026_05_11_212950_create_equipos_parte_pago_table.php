<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipos_parte_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('marca', 60);
            $table->string('modelo', 100);
            $table->string('imei', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->integer('capacidad_gb')->nullable();
            $table->enum('condicion', ['bueno', 'regular', 'malo'])->default('bueno');
            $table->integer('bateria_pct')->nullable();
            $table->decimal('cotizacion_usd', 10, 2);
            $table->decimal('dolar_blue_usado', 10, 2);
            $table->decimal('valor_ars', 12, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('equipos_parte_pago'); }
};