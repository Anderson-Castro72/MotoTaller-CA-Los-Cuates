<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenEntrada;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function pos($orden_id)
    {
        // Buscamos la orden con su cliente y motocicleta
        $orden = OrdenEntrada::with(['cliente', 'motocicleta'])->findOrFail($orden_id);
        
        // Traemos todos los servicios activos para el catálogo rápido
        $servicios = Producto::where('es_servicio', true)->where('activo', true)->get();
        
        return view('ventas.pos', compact('orden', 'servicios'));
    }

    // 2. Función AJAX para el Escáner de Código de Barras
    public function buscarProducto($codigo)
    {
        // Buscamos el producto por su código, asegurándonos de que esté activo
        $producto = Producto::where('codigo', $codigo)->where('activo', true)->first();
        
        if ($producto) {
            return response()->json(['existe' => true, 'producto' => $producto]);
        }
        
        return response()->json(['existe' => false]);
    }

    // (La función store para guardar la venta la agregaremos después de armar el carrito)
}