<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('comercios', function (Blueprint $table) {
            $table->string('cuit', 20)->nullable()->after('nombre');
            $table->string('direccion', 200)->nullable()->after('cuit');
            $table->string('telefono', 30)->nullable()->after('direccion');
            $table->string('email', 150)->nullable()->after('telefono');
            $table->string('condicion_iva', 50)->default('Monotributista')->after('email');
            $table->string('punto_venta', 5)->default('0001')->after('condicion_iva');
            $table->unsignedInteger('ultimo_nro_ticket')->default(0)->after('punto_venta');
            $table->unsignedInteger('ultimo_nro_factura')->default(0)->after('ultimo_nro_ticket');
            $table->unsignedInteger('ultimo_nro_recibo')->default(0)->after('ultimo_nro_factura');
            $table->unsignedInteger('ultimo_nro_presupuesto')->default(0)->after('ultimo_nro_recibo');
        });
    }
    public function down(): void {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn([
                'cuit','direccion','telefono','email','condicion_iva',
                'punto_venta','ultimo_nro_ticket','ultimo_nro_factura',
                'ultimo_nro_recibo','ultimo_nro_presupuesto'
            ]);
        });
    }
};