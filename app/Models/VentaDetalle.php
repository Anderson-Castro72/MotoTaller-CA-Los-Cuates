<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id', 'producto_id', 'cantidad', 
        'precio_unitario_sin_iva', 'monto_iva_unitario', 'subtotal_linea'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}