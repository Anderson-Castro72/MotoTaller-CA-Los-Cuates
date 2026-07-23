<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Motocicleta extends Model
{
    use HasFactory;

    protected $fillable = [
        'placa', 'marca', 'modelo', 'color', 'anio'
    ];

    // Relación: Una moto puede tener varios dueños/encargados a lo largo del tiempo
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_motocicleta');
    }
}