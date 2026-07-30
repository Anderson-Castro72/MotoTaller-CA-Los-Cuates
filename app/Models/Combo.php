<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'activo'];

    // Relación Many-to-Many con Productos
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'combo_producto')
                    ->withPivot('cantidad', 'precio_en_combo')
                    ->withTimestamps();
    }
}