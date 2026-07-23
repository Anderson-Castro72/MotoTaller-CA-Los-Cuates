<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'dui', 'telefono', 'direccion', 'email', 'nit', 'nrc', 'actividad_economica'
    ];

    // Relación: Un cliente puede tener muchas motos
    public function motocicletas()
    {
        return $this->belongsToMany(Motocicleta::class, 'cliente_motocicleta');
    }
}