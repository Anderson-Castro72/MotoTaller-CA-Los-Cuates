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
        Schema::table('productos', function (Blueprint $table) {
            // Cambiamos el nombre de la columna de 'precio_sin_iva' a 'precio'
            $table->renameColumn('precio_sin_iva', 'precio');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Por si necesitamos revertir el cambio en el futuro
            $table->renameColumn('precio', 'precio_sin_iva');
        });
    }
};
