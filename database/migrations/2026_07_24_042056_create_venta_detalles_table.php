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
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            
            // Relacionado a la venta (UUID) y al producto
            $table->foreignUuid('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignUuid('producto_id')->constrained('productos');
            
            $table->integer('cantidad');
            
            // Cálculo exacto a nivel de ítem (Exigencia MH)
            $table->decimal('precio_unitario_sin_iva', 10, 2);
            $table->decimal('monto_iva_unitario', 10, 2);
            $table->decimal('subtotal_linea', 10, 2); // cantidad * precio_unitario_sin_iva
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
    }
};
