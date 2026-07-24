<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta - MotoTaller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .tabla-carrito th, .tabla-carrito td { vertical-align: middle; }
        .resumen-card { position: sticky; top: 20px; }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="text-primary fw-bold mb-0">🛒 Punto de Venta (POS)</h3>
                <p class="text-muted mb-0">Orden #{{ str_pad($orden->id, 5, '0', STR_PAD_LEFT) }} | Cliente: <strong>{{ $orden->cliente->nombre }}</strong> | Moto: <strong>{{ $orden->motocicleta->placa }}</strong></p>
            </div>
            <a href="{{ route('recepcion.index') }}" class="btn btn-outline-secondary">⬅️ Volver a Recepciones</a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-3 border-success">
                    <div class="card-body">
                        <label class="form-label fw-bold text-success">🔍 Escanear o Buscar Código de Repuesto/Servicio</label>
                        <div class="input-group mb-2">
                            <input type="text" id="codigo_producto" class="form-control form-control-lg border-success" placeholder="Pista: Usa la pistola láser o escribe y presiona Enter..." autofocus>
                            <button class="btn btn-success" type="button" id="btnBuscar">Agregar ➕</button>
                        </div>
                        <button type="button" id="btnEscanerCamara" class="btn btn-sm btn-outline-dark">📸 Usar Cámara del Celular</button>
                        <div id="reader" width="100%" style="display: none;" class="mt-3"></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped tabla-carrito mb-0 text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th class="text-start">Descripción</th>
                                        <th>Cant.</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                        <th>Quitar</th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoCarrito">
                                    <tr id="filaVacia">
                                        <td colspan="6" class="text-muted py-4">El carrito está vacío. Escanea un repuesto para empezar.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-primary resumen-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 fw-bold">🧾 Resumen de Venta</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal (Sin IVA):</span>
                            <span class="fw-bold">$<span id="txtSubtotal">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">IVA (13%):</span>
                            <span class="fw-bold">$<span id="txtIva">0.00</span></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h4 class="text-dark fw-bold">Total a Pagar:</h4>
                            <h4 class="text-success fw-bold">$<span id="txtTotal">0.00</span></h4>
                        </div>

                        <form action="#" method="POST" id="formVenta">
                            @csrf
                            <input type="hidden" name="orden_entrada_id" value="{{ $orden->id }}">
                            <input type="hidden" name="cliente_id" value="{{ $orden->cliente_id }}">
                            <input type="hidden" name="subtotal_final" id="inputSubtotal">
                            <input type="hidden" name="iva_final" id="inputIva">
                            <input type="hidden" name="total_final" id="inputTotal">
                            
                            <div id="inputsCarritoOcultos"></div>

                            <label class="form-label fw-bold small">Tipo de Documento</label>
                            <select name="tipo_documento" class="form-select mb-4">
                                <option value="Ticket">Ticket Interno</option>
                                <option value="FCF">Factura Consumidor Final (FCF)</option>
                                <option value="CCF">Comprobante de Crédito Fiscal (CCF)</option>
                            </select>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" id="btnCobrar" disabled>
                                💳 Procesar Cobro
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let carrito = []; // Memoria del carrito
            const tasaIVA = 0.13; // 13% de IVA de El Salvador
            const inputCodigo = document.getElementById('codigo_producto');
            
            // --- 1. Lógica Matemática del Carrito ---
            function renderizarCarrito() {
                const cuerpo = document.getElementById('cuerpoCarrito');
                const inputsOcultos = document.getElementById('inputsCarritoOcultos');
                cuerpo.innerHTML = '';
                inputsOcultos.innerHTML = '';
                
                let subtotalGlobal = 0;
                let ivaGlobal = 0;

                if (carrito.length === 0) {
                    cuerpo.innerHTML = '<tr id="filaVacia"><td colspan="6" class="text-muted py-4">El carrito está vacío.</td></tr>';
                    document.getElementById('btnCobrar').disabled = true;
                } else {
                    document.getElementById('btnCobrar').disabled = false;
                    
                    carrito.forEach((item, index) => {
                        // Cálculos por línea exigidos por MH
                        let precioUnitarioSinIva = parseFloat(item.precio_sin_iva);
                        let subtotalLinea = precioUnitarioSinIva * item.cantidad;
                        let ivaLinea = subtotalLinea * tasaIVA;
                        
                        subtotalGlobal += subtotalLinea;
                        ivaGlobal += ivaLinea;

                        // Dibujar tabla
                        cuerpo.innerHTML += `
                            <tr>
                                <td class="fw-bold">${item.codigo}</td>
                                <td class="text-start">${item.nombre}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 70px;" value="${item.cantidad}" min="1" onchange="cambiarCantidad(${index}, this.value)">
                                </td>
                                <td>$${precioUnitarioSinIva.toFixed(2)}</td>
                                <td class="fw-bold">$${(subtotalLinea + ivaLinea).toFixed(2)}</td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="quitarDelCarrito(${index})">❌</button></td>
                            </tr>
                        `;

                        // Crear inputs ocultos para que el formulario se envíe a Laravel
                        inputsOcultos.innerHTML += `
                            <input type="hidden" name="productos[${index}][id]" value="${item.id}">
                            <input type="hidden" name="productos[${index}][cantidad]" value="${item.cantidad}">
                            <input type="hidden" name="productos[${index}][precio_unitario_sin_iva]" value="${precioUnitarioSinIva.toFixed(2)}">
                            <input type="hidden" name="productos[${index}][monto_iva_unitario]" value="${(precioUnitarioSinIva * tasaIVA).toFixed(2)}">
                            <input type="hidden" name="productos[${index}][subtotal_linea]" value="${subtotalLinea.toFixed(2)}">
                        `;
                    });
                }

                // Actualizar totales en pantalla
                let totalPagar = subtotalGlobal + ivaGlobal;
                document.getElementById('txtSubtotal').innerText = subtotalGlobal.toFixed(2);
                document.getElementById('txtIva').innerText = ivaGlobal.toFixed(2);
                document.getElementById('txtTotal').innerText = totalPagar.toFixed(2);

                // Guardar totales ocultos para el formulario
                document.getElementById('inputSubtotal').value = subtotalGlobal.toFixed(2);
                document.getElementById('inputIva').value = ivaGlobal.toFixed(2);
                document.getElementById('inputTotal').value = totalPagar.toFixed(2);
            }

            // --- 2. Búsqueda por AJAX (Lector de barras o Enter) ---
            function buscarProducto(codigo) {
                if(!codigo) return;
                
                fetch(`/ventas/buscar-producto/${codigo}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.existe) {
                            let prod = data.producto;
                            
                            // Revisar si ya está en el carrito para sumar +1
                            let existeEnCarrito = carrito.findIndex(p => p.id === prod.id);
                            if (existeEnCarrito !== -1) {
                                carrito[existeEnCarrito].cantidad += 1;
                            } else {
                                prod.cantidad = 1;
                                carrito.push(prod);
                            }
                            
                            renderizarCarrito();
                            inputCodigo.value = ''; // Limpiar casilla
                            inputCodigo.focus();
                        } else {
                            Swal.fire('No encontrado', 'El código no existe o el producto está inactivo.', 'error');
                            inputCodigo.select();
                        }
                    });
            }

            // Eventos del input buscar
            document.getElementById('btnBuscar').addEventListener('click', () => buscarProducto(inputCodigo.value));
            inputCodigo.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Evita enviar el formulario por error
                    buscarProducto(this.value);
                }
            });

            // Funciones globales para que la tabla las llame
            window.cambiarCantidad = function(index, nuevaCantidad) {
                if (nuevaCantidad > 0) {
                    carrito[index].cantidad = parseInt(nuevaCantidad);
                    renderizarCarrito();
                }
            }
            window.quitarDelCarrito = function(index) {
                carrito.splice(index, 1);
                renderizarCarrito();
            }

            // --- 3. Escáner con la Cámara (Celular) ---
            const btnCamara = document.getElementById('btnEscanerCamara');
            const readerDiv = document.getElementById('reader');
            let html5QrcodeScanner = null;

            btnCamara.addEventListener('click', function() {
                readerDiv.style.display = 'block';
                html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 100} }, false);
                html5QrcodeScanner.render(onScanSuccess);
            });

            function onScanSuccess(decodedText) {
                html5QrcodeScanner.clear();
                readerDiv.style.display = 'none';
                inputCodigo.value = decodedText;
                buscarProducto(decodedText); // Busca y agrega de inmediato
            }
        });
    </script>
</body>
</html>