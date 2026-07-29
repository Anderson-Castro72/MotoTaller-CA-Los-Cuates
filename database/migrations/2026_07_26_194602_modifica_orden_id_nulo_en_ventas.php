<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Hacemos que la orden de entrada sea opcional (nullable)
            $table->unsignedBigInteger('orden_entrada_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_entrada_id')->nullable(false)->change();
        });
    }
};
