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

   // 3. Procesar el cobro y descontar inventario
    public function store(Request $request)
    {
        // 1. Validar que la información enviada sea correcta y no venga vacía
        $request->validate([
            'orden_entrada_id' => 'required|exists:ordenes_entrada,id',
            'cliente_id' => 'required|exists:clientes,id',
            'subtotal_final' => 'required|numeric|min:0',
            'iva_final' => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'tipo_documento' => 'required|in:Ticket,FCF,CCF',
            'productos' => 'required|array|min:1', // El carrito no puede estar vacío
        ]);

        try {
            // Iniciamos la transacción de seguridad
            DB::beginTransaction();

            // 2. Crear el encabezado de la Venta (Genera el UUID automáticamente)
            $venta = Venta::create([
                'orden_entrada_id' => $request->orden_entrada_id,
                'cliente_id' => $request->cliente_id,
                'subtotal' => $request->subtotal_final,
                'total_iva' => $request->iva_final,
                'total_pagar' => $request->total_final,
                'tipo_documento' => $request->tipo_documento,
                'estado' => 'Pagado',
            ]);

            // 3. Guardar el detalle línea por línea y descontar inventario
            foreach ($request->productos as $item) {
                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario_sin_iva' => $item['precio_unitario_sin_iva'],
                    'monto_iva_unitario' => $item['monto_iva_unitario'],
                    'subtotal_linea' => $item['subtotal_linea'],
                ]);

                // Buscamos el producto en la base de datos
                $producto = Producto::find($item['id']);
                
                // REGLA CLAVE: Solo descontamos stock si NO es un servicio
                if (!$producto->es_servicio) {
                    $producto->stock_actual -= $item['cantidad'];
                    $producto->save();
                }
            }

            // 4. Cambiar el estado de la Orden de Recepción a "Facturado"
            $orden = OrdenEntrada::find($request->orden_entrada_id);
            $orden->estado = 'Facturado';
            $orden->save();

            // Si todo salió bien, guardamos permanentemente en la base de datos
            DB::commit();

            // Redirigimos al inicio con el mensaje, el ID del ticket y el tipo de documento
            return redirect()->route('recepcion.index')
                ->with('success', '¡Cobro procesado exitosamente! El inventario ha sido descontado.')
                ->with('ticket_id', $venta->id)
                ->with('tipo_documento', $venta->tipo_documento);

        } catch (\Exception $e) {
            // Si algo falla (ej. error de base de datos), revertimos todo
            DB::rollBack();
            return redirect()->back()->withErrors('Ocurrió un error al procesar el cobro: ' . $e->getMessage());
        }
    }
    // Generar la vista de impresión del ticket térmico
    public function imprimirTicket($id)
    {
        $venta = Venta::with(['detalles.producto', 'orden.cliente', 'orden.motocicleta'])->findOrFail($id);
        return view('ventas.ticket', compact('venta'));
    }
}