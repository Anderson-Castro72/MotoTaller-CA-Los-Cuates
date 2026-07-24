<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importante para el MH

class Venta extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ventas';

    protected $fillable = [
        'orden_entrada_id', 'cliente_id', 'subtotal', 'total_iva', 
        'total_pagar', 'tipo_documento', 'estado', 
        'uuid_generacion', 'sello_recepcion', 'codigo_generacion', 'estado_dte'
    ];

    // Relaciones
    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function orden()
    {
        return $this->belongsTo(OrdenEntrada::class, 'orden_entrada_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}