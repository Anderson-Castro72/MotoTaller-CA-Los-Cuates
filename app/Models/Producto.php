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
        'precio', 
        'stock_actual', 
        'stock_minimo', 
        'es_servicio',
        'activo'
    ];
    // Relación Many-to-Many con Combos
    public function combos()
    {
        return $this->belongsToMany(Combo::class, 'combo_producto')
                    ->withPivot('cantidad', 'precio_en_combo')
                    ->withTimestamps();
    }
}