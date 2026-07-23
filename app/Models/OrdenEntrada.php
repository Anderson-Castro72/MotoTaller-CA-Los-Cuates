<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenEntrada extends Model
{
    use HasFactory;

    // Le indicamos exactamente el nombre de la tabla
    protected $table = 'ordenes_entrada';

    // Todos los campos dinámicos que guardaremos del formulario
    protected $fillable = [
        'motocicleta_id', 'cliente_id', 'kilometraje_entrada', 
        'nivel_combustible', 'falla_reportada', 'observaciones', 
        'estado', 'foto_1', 'foto_2', 'foto_3', 'foto_4'
    ];

    // Relaciones (A quién pertenece esta orden)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function motocicleta()
    {
        return $this->belongsTo(Motocicleta::class);
    }
}