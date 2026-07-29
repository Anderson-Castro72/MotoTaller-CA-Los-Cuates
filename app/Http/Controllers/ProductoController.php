<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'ilike', '%' . $request->buscar . '%')
                  ->orWhere('codigo', 'ilike', '%' . $request->buscar . '%');
            });
        }


        if ($request->filled('estado') && $request->estado != 'todos') {
            $estadoBool = $request->estado == 'activos' ? true : false;
            $query->where('activo', $estadoBool);
        }

        $query->orderBy('activo', 'desc')->orderBy('nombre', 'asc');

        $productos = $query->paginate(10)->withQueryString();

        return view('inventario.index', compact('productos'));
    }

    public function create()
    {
        $ultimoServicio = Producto::where('codigo', 'like', 'TMCA-%')
                                  ->orderBy('codigo', 'desc')
                                  ->first();
        
        $siguienteCodigo = 'TMCA-0001'; 
    
        if ($ultimoServicio) {

            $numero = intval(substr($ultimoServicio->codigo, 5)) + 1;
            $siguienteCodigo = 'TMCA-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
        }
    
        return view('inventario.create', compact('siguienteCodigo'));
    }
    public function store(Request $request)
    {
        $esServicio = $request->input('es_servicio') == '1';
        $reglas_stock = $esServicio ? 'nullable|integer|min:0' : 'required|integer|min:1';

        $request->validate([
            'codigo' => 'nullable|string|unique:productos,codigo', 
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => $reglas_stock,
            'stock_minimo' => $reglas_stock,
        ], [
            'codigo.unique' => 'Este código de barras ya está registrado en otro producto. Búscalo en la tabla para editarlo.'
        ]);

        Producto::create($request->all());
        return redirect()->route('inventario.index')->with('success', 'Producto registrado exitosamente.');
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        return view('inventario.edit', compact('producto'));
    }

public function update(Request $request, $id)
    {
        $esServicio = $request->input('es_servicio') == '1';
        $reglas_stock = $esServicio ? 'nullable|integer|min:0' : 'required|integer|min:1';

        $request->validate([
            // La magia está aquí: ignoramos el ID actual para que no dé error de duplicado consigo mismo
            'codigo' => 'nullable|string|unique:productos,codigo,' . $id, 
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock_actual' => $reglas_stock,
            'stock_minimo' => $reglas_stock,
        ], [
            'codigo.unique' => 'Este código de barras ya está registrado en otro producto.'
        ]);

        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        
        return redirect()->route('inventario.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        
        $producto->update(['activo' => !$producto->activo]);

        $mensaje = $producto->activo ? 'Producto reactivado exitosamente.' : 'Producto desactivado.';
        return redirect()->route('inventario.index')->with('success', $mensaje);
    }
    // Método para validar por AJAX si el código ya existe
    public function verificarCodigo($codigo)
    {
        $existe = Producto::where('codigo', $codigo)->exists();
        return response()->json(['existe' => $existe]);
    }
}