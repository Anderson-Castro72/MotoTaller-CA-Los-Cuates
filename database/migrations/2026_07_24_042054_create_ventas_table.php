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
        Schema::create('ventas', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Exigencia del MH para UUIDs
            
            // Relaciones
            $table->foreignId('orden_entrada_id')->nullable()->constrained('ordenes_entrada');
            $table->foreignId('cliente_id')->constrained('clientes');
            
            // Totales Matemáticos
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_iva', 10, 2);
            $table->decimal('total_pagar', 10, 2);
            
            // Tipo y Estado
            $table->enum('tipo_documento', ['Ticket', 'FCF', 'CCF'])->default('Ticket');
            $table->enum('estado', ['Pagado', 'Anulado'])->default('Pagado');
            
            // Preparación Facturación Electrónica (MH)
            $table->string('uuid_generacion')->nullable();
            $table->string('sello_recepcion')->nullable();
            $table->string('codigo_generacion')->nullable();
            $table->string('estado_dte')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
