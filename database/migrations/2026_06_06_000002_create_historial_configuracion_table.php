<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_configuracion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->string('campo', 100)->comment('Campo modificado: recargo_tarjeta, cuotas_4_recargo, etc');
            $table->string('valor_anterior')->nullable();
            $table->string('valor_nuevo');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('descripcion')->nullable()->comment('Descripción del cambio');
            $table->timestamp('cambio_en')->useCurrent();
            $table->timestamps();

            // Índices para búsquedas
            $table->index('comercio_id');
            $table->index('campo');
            $table->index('usuario_id');
            $table->index('cambio_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_configuracion');
    }
};
