<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenEntrada;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Combo; 
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
        
        // Traemos todos los servicios, productos y combos activos
        $servicios = Producto::where('es_servicio', true)->where('activo', true)->get();
        $productos = Producto::where('es_servicio', false)->where('activo', true)->get();
        $combos = Combo::with('productos')->where('activo', true)->get(); // <-- NUEVO

        return view('ventas.pos', compact('orden', 'servicios', 'productos', 'combos'));
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
            'descuento_final' => 'required|numeric|min:0', // <-- NUEVO: Validamos el descuento
            'iva_final' => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'tipo_documento' => 'required|in:Ticket,FCF,CCF',
            'productos' => 'required|array|min:1', 
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia',
            'monto_recibido' => 'nullable|numeric|min:0',
            'cambio' => 'nullable|numeric|min:0',
        ]);

        try {
            // Iniciamos la transacción de seguridad
            DB::beginTransaction();

            // 2. Crear el encabezado de la Venta (Genera el UUID automáticamente)
            $venta = Venta::create([
                'orden_entrada_id' => $request->orden_entrada_id,
                'cliente_id' => $request->cliente_id,
                'subtotal' => $request->subtotal_final,
                'descuento' => $request->descuento_final, // <-- NUEVO: Guardamos el descuento
                'total_iva' => $request->iva_final,
                'total_pagar' => $request->total_final,
                'tipo_documento' => $request->tipo_documento,
                'estado' => 'Pagado',
                'metodo_pago' => $request->metodo_pago,
                // Si es tarjeta o transferencia, guardamos el total cobrado, si es efectivo guardamos el billete recibido
                'monto_recibido' => $request->metodo_pago == 'Efectivo' ? $request->monto_recibido : $request->total_final,
                'cambio' => $request->metodo_pago == 'Efectivo' ? $request->cambio : 0,
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
            return redirect()->route('ventas.directa')
                ->with('success', '¡Cobro procesado exitosamente! El inventario ha sido descontado.')
                ->with('ticket_id', $venta->id)
                ->with('tipo_documento', $venta->tipo_documento);

        } catch (\Exception $e) {
            // Si algo falla (ej. error de base de datos), revertimos todo
            DB::rollBack();
            return redirect()->back()->withErrors('Ocurrió un error al procesar el cobro: ' . $e->getMessage());
        }
    }

    // Cargar POS para Venta Directa
    public function posDirecto()
    {
        // Traemos los clientes para que el cajero elija a quién le vende
        $clientes = \App\Models\Cliente::all();
        $servicios = Producto::where('es_servicio', true)->where('activo', true)->get();
        $productos = Producto::where('es_servicio', false)->where('activo', true)->get();
        $combos = Combo::with('productos')->where('activo', true)->get(); // <-- NUEVO

        // Mandamos 'orden' como null para que la vista sepa que es Venta Directa
        return view('ventas.pos', compact('clientes', 'servicios', 'productos', 'combos'))->with('orden', null);
    }

    // Generar e imprimir el ticket directamente
    public function imprimirTicketRed($id)
    {
        $venta = Venta::with(['detalles.producto', 'cliente', 'orden.motocicleta'])->findOrFail($id);
        
        try {
            $connector = new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector("192.168.1.200", 9100); 
            $printer = new \Mike42\Escpos\Printer($connector);

            $ancho = 42;
            $lineaSimple = str_repeat("-", $ancho) . "\n";
            $lineaGruesa = str_repeat("=", $ancho) . "\n";

            // 1. ENCABEZADO TRIBUTARIO
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("TALLER DE MOTOS\n");
            $printer->text("CHITO APARICIO\n");
            
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("Los Cuates\n");
            $printer->text("Giro: Mantenimiento y reparacion de motocicletas\n");
            $printer->text("Av. Principal, Col. El Palmar, Carr. Ruta Militar, San Miguel, San Miguel\n");
            $printer->text("NIT: 04826278-5 | NRC: 371951-6\n");
            $printer->text("Tel: 2624-6277 | WhatsApp: 7945-1882\n");
            $printer->text($lineaGruesa);

            // Título temporal hasta implementar DTE
            $printer->setEmphasis(true);
            $printer->text("DOCUMENTO DE CONTROL INTERNO\n");
            $printer->setEmphasis(false);
            $printer->text($lineaGruesa);

            // 2. DATOS DE LA OPERACIÓN
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text("TICKET NO:   #" . strtoupper(substr($venta->id, -8)) . "\n");
            $printer->text("FECHA/HORA:  " . $venta->created_at->format('d/m/Y H:i:s') . "\n");
            $printer->text("CAJERO:      Administrador\n");
            $printer->text($lineaSimple);

            // 3. DATOS DEL CLIENTE
            $printer->text("CLIENTE: " . \Illuminate\Support\Str::ascii($venta->cliente->nombre) . "\n");
            if ($venta->cliente->dui) {
                $printer->text("DUI:     " . $venta->cliente->dui . "\n");
            }
            
            if ($venta->orden_entrada_id && $venta->orden) {
                $printer->text("VEHICULO: " . $venta->orden->motocicleta->placa . " - " . \Illuminate\Support\Str::ascii($venta->orden->motocicleta->marca) . "\n");
            } else {
                $printer->text("TIPO:    Venta de Mostrador\n");
            }
            $printer->text($lineaGruesa);

            // 4. DETALLE DE PRODUCTOS
            $printer->setEmphasis(true);
            $printer->text("DESCRIPCION" . str_repeat(" ", $ancho - mb_strlen("DESCRIPCION") - mb_strlen("TOTAL")) . "TOTAL\n");
            $printer->setEmphasis(false);
            $printer->text($lineaSimple);

            foreach($venta->detalles as $detalle) {
                $linea1 = $detalle->cantidad . "x " . \Illuminate\Support\Str::ascii($detalle->producto->nombre);
                $printer->text($linea1 . "\n");

                $precioUnitario = "$" . number_format($detalle->precio_unitario_sin_iva + $detalle->monto_iva_unitario, 2);
                $totalFila = "$" . number_format(($detalle->precio_unitario_sin_iva + $detalle->monto_iva_unitario) * $detalle->cantidad, 2);
                
                $izq2 = "   (" . $precioUnitario . " c/u)";
                $espacios2 = $ancho - mb_strlen($izq2) - mb_strlen($totalFila);
                $printer->text($izq2 . str_repeat(" ", $espacios2 > 0 ? $espacios2 : 1) . $totalFila . "\n");
            }
            $printer->text($lineaGruesa);

            // 5. TOTALES
            $subtotal = "$" . number_format($venta->subtotal, 2);
            $esp = $ancho - mb_strlen("SUBTOTAL (SIN IVA):") - mb_strlen($subtotal);
            $printer->text("SUBTOTAL (SIN IVA):" . str_repeat(" ", $esp > 0 ? $esp : 1) . $subtotal . "\n");

            if ($venta->descuento > 0) {
                $textoAhorro = "AHORRO POR PROMOCION:";
                $montoAhorro = "-$" . number_format($venta->descuento, 2);
                $esp = $ancho - mb_strlen($textoAhorro) - mb_strlen($montoAhorro);
                $printer->text($textoAhorro . str_repeat(" ", $esp > 0 ? $esp : 1) . $montoAhorro . "\n");
            }

            $iva = "$" . number_format($venta->total_iva, 2);
            $esp = $ancho - mb_strlen("IVA (13%):") - mb_strlen($iva);
            $printer->text("IVA (13%):" . str_repeat(" ", $esp > 0 ? $esp : 1) . $iva . "\n");
            
            $printer->text("\n"); 
            
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_RIGHT);
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true);
            $printer->text("TOTAL A PAGAR: $" . number_format($venta->total_pagar, 2) . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text($lineaGruesa);

            // 6. PIE DE PÁGINA
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->text("GRACIAS POR SU PREFERENCIA\n\n");
            $printer->text("Este documento no sustituye\n");
            $printer->text("su comprobante fiscal.\n");
            $printer->text("Revise su motocicleta y repuestos\n");
            $printer->text("antes de retirarse del taller.\n");
            $printer->text("\n\n\n\n"); 
            $printer->pulse();
            $printer->cut();
            $printer->close();

            return redirect()->back()->with('success', 'Ticket enviado a la impresora exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Error de impresora: ' . $e->getMessage());
        }
    }

    // Mostrar el listado del Historial de Ventas
    public function index()
    {
        // Agregamos 'detalles.producto' para que los modales tengan la información pre-cargada
        $ventas = Venta::with(['cliente', 'orden.motocicleta', 'detalles.producto'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        
        $totalHoy = Venta::whereDate('created_at', \Carbon\Carbon::today())->sum('total_pagar');
        $totalMes = Venta::whereMonth('created_at', \Carbon\Carbon::now()->month)
                         ->whereYear('created_at', \Carbon\Carbon::now()->year)
                         ->sum('total_pagar');
                    
        return view('ventas.index', compact('ventas', 'totalHoy', 'totalMes'));
    }

    // Mostrar el detalle de una venta específica 
    public function show($id)
    {
        $venta = Venta::with(['cliente', 'orden.motocicleta', 'detalles.producto'])->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }
}