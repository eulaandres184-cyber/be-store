<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documentos', function (Blueprint $table) {
            // CAE = Código de Autorización Electrónico emitido por AFIP-ARCA
            $table->string('cae', 20)->nullable()->after('anulado');
            $table->date('cae_vto')->nullable()->after('cae');
            $table->text('afip_respuesta')->nullable()->after('cae_vto'); // JSON con respuesta completa de AFIP
        });
    }

    public function down(): void {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn(['cae', 'cae_vto', 'afip_respuesta']);
        });
    }
};
