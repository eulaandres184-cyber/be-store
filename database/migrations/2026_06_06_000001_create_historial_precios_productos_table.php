<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precios_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->decimal('precio_anterior', 10, 2);
            $table->decimal('precio_nuevo', 10, 2);
            $table->string('moneda_anterior', 3);
            $table->string('moneda_nueva', 3);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('razon_cambio')->nullable()->comment('Motivo del cambio');
            $table->timestamp('cambio_en')->useCurrent();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('producto_id');
            $table->index('comercio_id');
            $table->index('usuario_id');
            $table->index('cambio_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios_productos');
    }
};
