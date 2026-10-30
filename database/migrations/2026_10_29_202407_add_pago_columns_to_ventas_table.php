<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('metodo_pago')->default('Efectivo')->after('estado');
            $table->decimal('monto_recibido', 10, 2)->nullable()->after('metodo_pago');
            $table->decimal('cambio', 10, 2)->nullable()->after('monto_recibido');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'monto_recibido', 'cambio']);
        });
    }
};
