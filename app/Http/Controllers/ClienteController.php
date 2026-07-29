<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente; // ¡Súper importante para que reconozca la base de datos!

class ClienteController extends Controller
{
    public function storeRapido(Request $request)
    {
        // 1. Validamos lo básico
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            // 2. Guardamos. Ojo: si tu tabla exige dirección, aquí le ponemos un comodín
            $cliente = Cliente::create([
                'nombre'    => $request->nombre,
                'telefono'  => $request->telefono,
                'dui'       => $request->dui,
                // 'direccion' => 'No proporcionada', // Descomenta esta línea si tu BD exige dirección obligatoria
            ]);
            
            // 3. Enviamos el OK a JavaScript
            return response()->json(['success' => true, 'cliente' => $cliente]);

        } catch (\Exception $e) {
            // 4. Si la base de datos lo rechaza, capturamos el error
            return response()->json([
                'success' => false, 
                'message' => 'Error de BD: ' . $e->getMessage()
            ], 500);
        }
    }
}