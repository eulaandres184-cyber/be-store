<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->decimal('recargo_tarjeta', 5, 2)->default(15.00);
            $table->decimal('cuotas_4_recargo', 5, 2)->default(2.00);
            $table->decimal('cuotas_20_recargo', 5, 2)->default(20.00);
            $table->decimal('dolar_blue_hoy', 10, 2)->default(0);
            $table->timestamp('dolar_actualizado_en')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('configuracion');
    }
};