<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente; 
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
public function storeRapido(Request $request)
    {
        // 1. Usamos el Validator manual, ideal para peticiones AJAX
        $validador = Validator::make($request->all(), [
            'nombre'   => 'required|string|min:6|max:255',
            'telefono' => 'nullable|string|size:9',
            'dui'      => 'nullable|string|size:10|unique:clientes,dui',
        ], [
            // Mensajes amigables y específicos para cada error
            'nombre.min'    => 'Error de longitud de nombre. Debe tener al menos 6 caracteres.',
            'telefono.size' => 'Error de longitud de teléfono.',
            'dui.size'      => 'Error de longitu de DUI.',
            'dui.unique'    => 'Numero de DUI ya registrado.',
        ]);

        // 2. Si la validación falla, detenemos todo y enviamos el mensaje amigable
        if ($validador->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validador->errors()->first() // Tomamos nuestro mensaje en español
            ], 422);
        }

        try {
            // 3. Si pasa la validación, procedemos a guardar con confianza
            $cliente = Cliente::create([
                'nombre'    => $request->nombre,
                'telefono'  => $request->telefono,
                'dui'       => $request->dui,
                // 'direccion' => 'No proporcionada', 
            ]);
            
            return response()->json(['success' => true, 'cliente' => $cliente]);

        } catch (\Exception $e) {
            // 4. Si falla por otra cosa (ej. se cayó el servidor)
            return response()->json([
                'success' => false, 
                'message' => 'Error de BD: ' . $e->getMessage()
            ], 500);
        }
    }
}