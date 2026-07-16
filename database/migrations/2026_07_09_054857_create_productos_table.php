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
        Schema::create('productos', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID para FE
            $table->string('codigo')->nullable(); 
            $table->string('nombre');
            
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->foreignId('marca_id')->nullable()->constrained('marcas');
            
            $table->decimal('precio_sin_iva', 10, 4); // MH exige cálculo exacto a nivel de ítem
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->boolean('es_servicio')->default(false); // true = no descuenta stock
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
