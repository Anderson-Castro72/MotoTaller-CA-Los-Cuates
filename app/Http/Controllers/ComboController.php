<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Combo;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class ComboController extends Controller
{
    // Mostrar la lista de combos
    public function index()
    {
        $combos = Combo::with('productos')->get();
        return view('combos.index', compact('combos'));
    }

    // Mostrar el formulario para crear un combo
    public function create()
    {
        // Traemos todos los productos/servicios activos para que el dueño elija
        $productos = Producto::where('activo', true)->get();
        return view('combos.create', compact('productos'));
    }

    // Guardar el combo en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'productos' => 'required|array', // Debe tener al menos un producto
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_en_combo' => 'nullable|numeric|min:0',
        ]);

        // Usamos DB::transaction para asegurar que se guarde el combo Y sus detalles
        DB::transaction(function () use ($request) {
            
            // 1. Creamos el Combo (El "Paquete")
            $combo = Combo::create([
                'nombre' => $request->nombre,
                'activo' => true
            ]);

            // 2. Preparamos los ingredientes para la tabla pivote
            $ingredientes = [];
            foreach ($request->productos as $prod) {
                $ingredientes[$prod['id']] = [
                    'cantidad' => $prod['cantidad'],
                    // Si el dueño lo deja vacío, guardamos null (usará precio normal)
                    'precio_en_combo' => $prod['precio_en_combo'] ?: null, 
                ];
            }

            // 3. Los conectamos mágicamente
            $combo->productos()->sync($ingredientes);
        });

        return redirect()->route('combos.index')->with('success', '¡Combo creado con éxito!');
    }
}