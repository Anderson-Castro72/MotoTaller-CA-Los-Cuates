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
        Schema::create('ordenes_entrada', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motocicleta_id')->constrained('motocicletas');
            $table->foreignId('cliente_id')->constrained('clientes'); 
            
            $table->integer('kilometraje_entrada');
            $table->enum('nivel_combustible', ['E', '1/4', '1/2', '3/4', 'F']);
            $table->text('falla_reportada')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['Pendiente', 'En Reparación', 'Listo', 'Facturado'])->default('Pendiente');
            
            // Rutas para las 4 fotos
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->string('foto_4')->nullable();
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_entrada');
    }
};
