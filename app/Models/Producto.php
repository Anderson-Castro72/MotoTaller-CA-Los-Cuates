<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Importante para MH

class Producto extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'productos';

    // Declaramos qué campos se pueden llenar desde el formulario web
    protected $fillable = [
        'codigo', 
        'nombre', 
        'categoria_id', 
        'marca_id', 
        'precio_sin_iva', 
        'stock_actual', 
        'stock_minimo', 
        'es_servicio',
        'activo'
    ];
}