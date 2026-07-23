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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            // Requeridos día a día
            $table->string('nombre');
            $table->string('dui')->nullable();
            $table->string('telefono');
            
            // Opcionales (Pero necesarios para FE a futuro)
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('nit')->nullable();
            $table->string('nrc')->nullable();
            $table->string('actividad_economica')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
