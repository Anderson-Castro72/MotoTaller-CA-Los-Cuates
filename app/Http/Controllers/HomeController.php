<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
// Importaciones de tus modelos
use App\Models\Producto;
use App\Models\OrdenEntrada;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

public function index()
    {
        // 1. Alertas de stock mínimo (Solo repuestos físicos y ACTIVOS)
        $productosBajoStock = Producto::where('es_servicio', false)
                                      ->where('activo', true) // <-- Nueva condición
                                      ->whereColumn('stock_actual', '<=', 'stock_minimo')
                                      ->take(5)
                                      ->get();

        // 2. Motos en reparación actualmente (Estados activos)
        $motosEnTaller = OrdenEntrada::whereIn('estado', ['Pendiente', 'En Reparación'])
                                     ->count();

        // 3. Ventas del día (Usando tu columna total_pagar)
        $ventasHoy = DB::table('ventas')
                       ->whereDate('created_at', Carbon::today())
                       ->sum('total_pagar'); 

        return view('home', compact('productosBajoStock', 'motosEnTaller', 'ventasHoy'));
    }
}   