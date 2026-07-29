<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenEntrada;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Str;

class VentaController extends Controller
{
    public function pos($orden_id)
    {
        // Buscamos la orden con su cliente y motocicleta
        $orden = OrdenEntrada::with(['cliente', 'motocicleta'])->findOrFail($orden_id);
        
        // Traemos todos los servicios activos para el catálogo rápido
        $servicios = Producto::where('es_servicio', true)->where('activo', true)->get();
        $productos = Producto::where('es_servicio', false)->where('activo', true)->get();
        return view('ventas.pos', compact('orden', 'servicios', 'productos'));
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
            'orden_entrada_id' => 'nullable|exists:ordenes_entrada,id',
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
            if ($request->orden_entrada_id) {
                $orden = OrdenEntrada::find($request->orden_entrada_id);
                $orden->estado = 'Facturado';
                $orden->save();
            }
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
    // Cargar POS para Venta Directa (Sin moto)
    public function posDirecto()
    {
        // Traemos los clientes para que el cajero elija a quién le vende
        $clientes = \App\Models\Cliente::all();
        $servicios = Producto::where('es_servicio', true)->where('activo', true)->get();
        $productos = Producto::where('es_servicio', false)->where('activo', true)->get();
        // Mandamos 'orden' como null para que la vista sepa que es Venta Directa
        return view('ventas.pos', compact('clientes', 'servicios', 'productos'))->with('orden', null);
    }

    // Generar e imprimir el ticket directamente por Red (LAN)
    public function imprimirTicketRed($id)
    {
        $venta = Venta::with(['detalles.producto', 'cliente', 'orden.motocicleta'])->findOrFail($id);
        try {
            // Conectamos directo a la IP de la ticketera (Puerto 9100 por defecto)
            $connector = new NetworkPrintConnector("192.168.1.200", 9100); 
            $printer = new Printer($connector);

            // --- DISEÑO DEL TICKET (Comandos ESC/POS Puros) ---
            
            // Las impresoras de 80mm (como la RPT006) tienen un ancho estándar de 42 caracteres.
            $ancho = 42;
            $linea = str_repeat("-", $ancho) . "\n";

            // 1. Encabezado Centrado
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true); // Negrita
            $printer->text("TALLER \"LOS CUATES\"\n");
            $printer->setEmphasis(false);
            $printer->text("De: Chito Aparicio\n");
            $printer->text("Fecha: " . $venta->created_at->format('d/m/Y H:i') . "\n");
            $printer->text("Ticket Interno #" . strtoupper(substr($venta->id, -8)) . "\n");
            $printer->text($linea);

            // 2. Datos del Cliente (Izquierda)
            // 2. Datos del Cliente (Izquierda)
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            
            // Obtenemos el cliente directamente de la venta y lo limpiamos de tildes
            $printer->text("Cliente: " . Str::ascii($venta->cliente->nombre) . "\n");
            
            // Validamos si hay una moto en el taller, o si es un cliente de mostrador
            if ($venta->orden_entrada_id && $venta->orden) {
                $printer->text("Vehiculo: " . $venta->orden->motocicleta->placa . " - " . Str::ascii($venta->orden->motocicleta->marca) . "\n");
            } else {
                $printer->text("Tipo: Venta de Mostrador\n");
            }
            
            $printer->text($linea);

            // 3. Cabecera de Productos (Matemática para empujar "TOTAL" a la derecha)
            $cabeceraIzq = "CANT DESCRIPCION";
            $cabeceraDer = "TOTAL";
            $espacios = $ancho - mb_strlen($cabeceraIzq) - mb_strlen($cabeceraDer);
            $printer->text($cabeceraIzq . str_repeat(" ", $espacios > 0 ? $espacios : 1) . $cabeceraDer . "\n");
            $printer->text($linea);

            // 4. Detalle de productos
            foreach($venta->detalles as $detalle) {
                $totalFila = "$" . number_format($detalle->subtotal_linea + $detalle->monto_iva_unitario, 2);
                $izq = $detalle->cantidad . "  " . Str::ascii($detalle->producto->nombre);

                // Si el nombre es muy largo, lo cortamos sutilmente para que no arruine la matemática
                $maxIzq = $ancho - mb_strlen($totalFila) - 1;
                if (mb_strlen($izq) > $maxIzq) {
                    $izq = mb_substr($izq, 0, $maxIzq);
                }

                // Calculamos espacios blancos para rellenar el centro
                $espacios = $ancho - mb_strlen($izq) - mb_strlen($totalFila);
                $printer->text($izq . str_repeat(" ", $espacios > 0 ? $espacios : 1) . $totalFila . "\n");
            }
            $printer->text($linea);

            // 5. Totales (Alineados matemáticamente a la derecha)
            $subtotal = "$" . number_format($venta->subtotal, 2);
            $esp = $ancho - mb_strlen("SUBTOTAL:") - mb_strlen($subtotal);
            $printer->text("SUBTOTAL:" . str_repeat(" ", $esp > 0 ? $esp : 1) . $subtotal . "\n");

            $iva = "$" . number_format($venta->total_iva, 2);
            $esp = $ancho - mb_strlen("IVA (13%):") - mb_strlen($iva);
            $printer->text("IVA (13%):" . str_repeat(" ", $esp > 0 ? $esp : 1) . $iva . "\n");

            $printer->text("\n"); // Pequeño salto para destacar el total
            
            $printer->setEmphasis(true);
            $total = "$" . number_format($venta->total_pagar, 2);
            $esp = $ancho - mb_strlen("TOTAL A PAGAR:") - mb_strlen($total);
            $printer->text("TOTAL A PAGAR:" . str_repeat(" ", $esp > 0 ? $esp : 1) . $total . "\n");
            $printer->setEmphasis(false);

            $printer->text($linea);

            // 6. Pie de página
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su preferencia\n");
            $printer->text("Revise su motocicleta antes\n");
            $printer->text("de salir.\n");
            $printer->text("\n\n"); // Espacio para que la cuchilla corte bien

            // ¡EL COMANDO MÁGICO PARA LA CUCHILLA!
            $printer->cut();
            
            // Cerramos la conexión
            $printer->close();

            return redirect()->back()->with('success', 'Ticket enviado a la impresora exitosamente.');

        } catch (\Exception $e) {
            // Si la impresora está apagada o sin papel, Laravel no se cae, solo te avisa.
            return redirect()->back()->withErrors('Error de impresora: Revise si está encendida y conectada a la red. Detalle: ' . $e->getMessage());
        }
    }
}