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
            'precio_total' => 'required|numeric|min:0', // <-- Validamos el precio del combo
            'productos' => 'required|array', 
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            
            // 1. Creamos el Combo con su precio general
            $combo = Combo::create([
                'nombre' => $request->nombre,
                'precio_total' => $request->precio_total,
                'activo' => true
            ]);

            // 2. Preparamos los ingredientes para la tabla pivote (Solo necesitamos la cantidad)
            $ingredientes = [];
            foreach ($request->productos as $prod) {
                $ingredientes[$prod['id']] = [
                    'cantidad' => $prod['cantidad']
                ];
            }

            // 3. Los conectamos mágicamente
            $combo->productos()->sync($ingredientes);
        });

        return redirect()->route('combos.index')->with('success', '¡Combo creado con éxito!');
    }
    // Mostrar el formulario de edición con los datos cargados
    public function edit(Combo $combo)
    {
        // Cargamos el combo junto con los productos que ya tiene asignados
        $combo->load('productos');
        
        // Traemos todo el catálogo activo por si el dueño quiere agregar algo nuevo al paquete
        $productos = Producto::where('activo', true)->get();
        
        return view('combos.edit', compact('combo', 'productos'));
    }

    // Guardar los cambios del combo editado en la base de datos
    public function update(Request $request, Combo $combo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_total' => 'required|numeric|min:0',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $combo) {
            
            // 1. Actualizamos el nombre y el nuevo precio global del combo
            $combo->update([
                'nombre' => $request->nombre,
                'precio_total' => $request->precio_total,
            ]);

            // 2. Preparamos los ingredientes que vienen del formulario
            $ingredientes = [];
            foreach ($request->productos as $prod) {
                $ingredientes[$prod['id']] = [
                    'cantidad' => $prod['cantidad']
                ];
            }

            // 3. Sync reemplaza inteligentemente los ingredientes viejos por los nuevos
            $combo->productos()->sync($ingredientes);
        });

        return redirect()->route('combos.index')->with('success', '¡Combo actualizado con éxito!');
    }
    // Cambiar estado Activo/Inactivo
    public function toggleStatus(Combo $combo)
    {
        $combo->activo = !$combo->activo;
        $combo->save();

        $mensaje = $combo->activo ? 'Combo activado correctamente.' : 'Combo desactivado. Ya no aparecerá en el cajero.';
        return back()->with('success', $mensaje);
    }
}