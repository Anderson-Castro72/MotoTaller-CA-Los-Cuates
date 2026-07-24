<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Motocicleta;
use App\Models\OrdenEntrada;
// use App\Models\OrdenEntrada; // Lo usaremos más adelante para el ticket

class RecepcionController extends Controller
{
// 1. Mostrar la tabla principal con Filtros
    public function index(Request $request)
    {
        // Iniciamos la consulta base con sus relaciones
        $query = OrdenEntrada::with(['cliente', 'motocicleta']);

        // Filtro por Nombre de Cliente (Busca en la tabla relacionada)
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nombre', 'ilike', '%' . $request->cliente . '%');
            });
        }

        // Filtro por Placa de Motocicleta (Busca en la tabla relacionada)
        if ($request->filled('placa')) {
            $query->whereHas('motocicleta', function($q) use ($request) {
                $q->where('placa', 'ilike', '%' . $request->placa . '%');
            });
        }

        // Filtro por Estado de la Orden
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

// Filtro por Fecha de Inicio
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        // Filtro por Fecha de Fin
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Ejecutamos la consulta ordenando por los más recientes y paginando
        $ordenes = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('recepcion.index', compact('ordenes'));
    }
    // 1. Mostrar la pantalla del formulario
    public function create()
    {
        return view('recepcion.create');
    }

    // 2. Función AJAX para buscar Cliente por DUI
    public function verificarCliente($dui)
    {
        $cliente = Cliente::where('dui', $dui)->first();
        
        if ($cliente) {
            return response()->json(['existe' => true, 'cliente' => $cliente]);
        }
        
        return response()->json(['existe' => false]);
    }

    // 3. Función AJAX para buscar Moto por Placa
    public function verificarMoto($placa)
    {
        $moto = Motocicleta::where('placa', $placa)->first();
        
        if ($moto) {
            return response()->json(['existe' => true, 'moto' => $moto]);
        }
        
        return response()->json(['existe' => false]);
    }

    // 4. Guardar todo el formulario de una sola vez
    public function store(Request $request)
    {
        // A. Lógica del Cliente (Crear o Actualizar)
        if ($request->filled('cliente_id')) {
            // El cliente ya existía, lo actualizamos por si habilitaron el "Switch" de edición
            $cliente = Cliente::findOrFail($request->cliente_id);
            $cliente->update($request->only(['nombre', 'telefono']));
        } else {
            // Es un cliente nuevo, lo creamos
            $cliente = Cliente::create($request->only(['nombre', 'dui', 'telefono']));
        }

        // B. Lógica de la Motocicleta (Crear o ignorar si ya existe)
        if ($request->filled('motocicleta_id')) {
            // La moto ya existía, solo la buscamos
            $moto = Motocicleta::findOrFail($request->motocicleta_id);
        } else {
            // Es una moto nueva, la creamos
            $moto = Motocicleta::create($request->only(['placa', 'marca', 'modelo', 'color', 'anio']));
        }

        // C. Unir Cliente y Moto (Tabla Pivote)
        // syncWithoutDetaching evita que se borren dueños anteriores si es que la trajo otra persona
        $cliente->motocicletas()->syncWithoutDetaching([$moto->id]);

       // ... (Aquí termina la línea de syncWithoutDetaching que ya tenías) ...

        // D. Procesar las fotografías (Si el usuario subió alguna desde la cámara/PC)
        $rutasFotos = [];
        for ($i = 1; $i <= 4; $i++) {
            $campoFoto = 'foto_' . $i;
            if ($request->hasFile($campoFoto)) {
                // Guarda la imagen físicamente en la carpeta storage/app/public/recepciones
                $rutasFotos[$campoFoto] = $request->file($campoFoto)->store('recepciones', 'public');
            } else {
                $rutasFotos[$campoFoto] = null;
            }
        }

        // E. Crear la Orden de Entrada con los datos dinámicos y las rutas de las fotos
        OrdenEntrada::create([
            'cliente_id' => $cliente->id,
            'motocicleta_id' => $moto->id,
            'kilometraje_entrada' => $request->kilometraje_entrada,
            'nivel_combustible' => $request->nivel_combustible,
            'falla_reportada' => $request->falla_reportada,
            'observaciones' => $request->observaciones,
            'estado' => 'Pendiente', // Inicia con este estado por defecto
            'foto_1' => $rutasFotos['foto_1'],
            'foto_2' => $rutasFotos['foto_2'],
            'foto_3' => $rutasFotos['foto_3'],
            'foto_4' => $rutasFotos['foto_4'],
        ]);

        return redirect()->back()->with('success', '¡Recepción y Orden de Entrada registradas exitosamente!');
    }
}