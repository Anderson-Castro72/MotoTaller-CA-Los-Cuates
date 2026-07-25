<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Diseño específico para rollo térmico de 80mm */
        @page { 
            size: 80mm auto; /* Le dice al navegador que es papel de 80mm y alto infinito */
            margin: 0; 
        }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            width: 75mm; /* Ajustado para márgenes de impresora */
            margin: 0 auto; 
            padding: 10px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .divider { border-bottom: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; vertical-align: top; }
        
        /* Ocultar el botón al imprimir */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()"> <div class="text-center">
        <h3 class="bold" style="margin: 0;">TALLER "LOS CUATES"</h3>
        <p style="margin: 2px 0;">De: Chito Aparicio</p>
        <p style="margin: 2px 0;">Fecha: {{ $venta->created_at->format('d/m/Y H:i') }}</p>
        <p style="margin: 2px 0;">Ticket Interno #{{ strtoupper(substr($venta->id, 0, 8)) }}</p>
    </div>

    <div class="divider"></div>

    <div class="text-left">
        <p style="margin: 2px 0;"><strong>Cliente:</strong> {{ $venta->orden->cliente->nombre }}</p>
        <p style="margin: 2px 0;"><strong>Vehículo:</strong> {{ $venta->orden->motocicleta->placa }} - {{ $venta->orden->motocicleta->marca }}</p>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr class="divider">
                <th class="text-left">CANT</th>
                <th class="text-left">DESCRIPCIÓN</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td class="text-left">{{ $detalle->producto->nombre }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal_linea + $detalle->monto_iva_unitario, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="text-left bold">SUBTOTAL:</td>
            <td class="text-right">${{ number_format($venta->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left bold">IVA (13%):</td>
            <td class="text-right">${{ number_format($venta->total_iva, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left bold" style="font-size: 14px;">TOTAL:</td>
            <td class="text-right bold" style="font-size: 14px;">${{ number_format($venta->total_pagar, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div class="text-center">
        <p>¡Gracias por su preferencia!</p>
        <p>Revise su motocicleta antes de salir.</p>
    </div>

    <div class="text-center no-print" style="margin-top: 20px;">
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Cerrar Ventana</button>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Reimprimir</button>
    </div>

</body>
</html>