<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('equipos_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('imei', 20)->unique();
            $table->string('marca', 60);
            $table->string('modelo', 100);
            $table->integer('capacidad_gb')->nullable();
            $table->string('color', 50)->nullable();
            $table->enum('condicion', ['nuevo', 'usado', 'reacondicionado'])->default('nuevo');
            $table->decimal('precio_usd', 10, 2);
            $table->integer('bateria_pct')->nullable();
            $table->enum('estado', ['disponible', 'vendido', 'reservado'])->default('disponible');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('equipos_detalle');
    }
};