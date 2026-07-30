@extends('layouts.app')

@section('title', 'Punto de Venta - MotoTaller')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos específicos solo para esta pantalla */
        .tabla-carrito th, .tabla-carrito td { vertical-align: middle; }
        .resumen-card { position: sticky; top: 20px; }
        
        /* Ajuste para que Select2 se vea bien con Bootstrap */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-2">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="text-primary fw-bold mb-0">🛒 Punto de Venta (POS)</h3>
            </div>
            <a href="{{ route('recepcion.index') }}" class="btn btn-outline-secondary">⬅️ Volver</a>
        </div>
        
        @if($orden)
            <h5 class="card-title">Orden #{{ $orden->id }}</h5>
            <p class="mb-1"><strong>Cliente:</strong> {{ $orden->cliente->nombre }}</p>
            <p class="mb-0"><strong>Vehículo:</strong> {{ $orden->motocicleta->placa }} - {{ $orden->motocicleta->marca }}</p>
            
            <input type="hidden" name="orden_entrada_id" form="formVenta" value="{{ $orden->id }}">
            <input type="hidden" name="cliente_id" form="formVenta" value="{{ $orden->cliente_id }}">
        @else
            <h5 class="card-title text-success mb-3"> Venta Directa (Mostrador)</h5>
            
            <label class="form-label"><strong>Seleccionar Cliente:</strong></label>
            <div class="d-flex gap-2 mb-4">
                <div class="col-lg-7">
                    <select name="cliente_id" id="clienteSelect" form="formVenta" class="form-select select2" required>
                        <option value="1">VENTA DE MOSTRADOR (Consumidor Final)</option>
                        
                        @foreach($clientes as $cliente)
                            @if($cliente->id != 1)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->nombre }} 
                                    {{ $cliente->telefono ? ' | Tel: '.$cliente->telefono : '' }} 
                                    {{ $cliente->dui ? ' | DUI: '.$cliente->dui : '' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="button" class="btn btn-primary h-100" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                </div>
            </div>
        @endif    


        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-3 border-success">
                    <div class="card-body">
                        <label class="form-label fw-bold text-success"> Buscar Productos</label>
                        <div class="input-group mb-2">
                            <input type="text" id="codigo_producto" class="form-control form-control-lg border-success" placeholder="Código de producto" autofocus>
                            <button class="btn btn-success" type="button" id="btnBuscar">Agregar ➕</button>
                        </div>
                        <button type="button" id="btnEscanerCamara" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-camera"></i> Escanear
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalServicios">
                            📦 Ver Catálogo
                        </button>
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

                        <form action="{{ route('ventas.store') }}" method="POST" id="formVenta">
                            @csrf
                            @if($orden)
                                <input type="hidden" name="orden_entrada_id" value="{{ $orden->id }}">
                                <input type="hidden" name="cliente_id" value="{{ $orden->cliente_id }}">
                            @endif
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

    <div class="modal fade" id="modalServicios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg"> 
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">📦 Catálogo de Productos y Servicios</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light">🔍</span>
                        <input type="text" id="buscadorServicios" class="form-control" placeholder="Buscar por nombre o código..." autocomplete="off">
                    </div>

                    <div class="list-group list-group-flush" id="listaServicios">
                        
                        @foreach($servicios as $servicio)
                            <div class="list-group-item d-flex justify-content-between align-items-center p-2 item-servicio">
                                <div>
                                    <h6 class="mb-0 fw-bold nombre-servicio">{{ $servicio->nombre }}</h6>
                                    <small class="text-muted">{{ $servicio->codigo }}</small>
                                    <span class="badge bg-info text-dark ms-2">Servicio</span>
                                </div>
                                <div class="text-end">
                                    <span class="d-block text-success fw-bold mb-1">${{ number_format($servicio->precio, 2) }}</span>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal" onclick="buscarProducto('{{ $servicio->codigo }}')">
                                        Agregar ➕
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if(isset($productos))
                            @foreach($productos as $producto)
                                <div class="list-group-item d-flex justify-content-between align-items-center p-2 item-servicio">
                                    <div>
                                        <h6 class="mb-0 fw-bold nombre-servicio">{{ $producto->nombre }}</h6>
                                        <small class="text-muted">{{ $producto->codigo }}</small>
                                        <span class="badge bg-secondary ms-2">Producto</span>
                                        
                                        @if($producto->stock_actual > 0)
                                            <span class="badge bg-success ms-1">Stock: {{ $producto->stock_actual }}</span>
                                        @else
                                            <span class="badge bg-danger ms-1">Sin Stock</span>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="d-block text-success fw-bold mb-1">${{ number_format($producto->precio, 2) }}</span>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal" 
                                            onclick="buscarProducto('{{ $producto->codigo }}')" 
                                            {{ $producto->stock_actual <= 0 ? 'disabled' : '' }}>
                                            Agregar ➕
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if($servicios->isEmpty() && (!isset($productos) || $productos->isEmpty()))
                            <div class="p-4 text-center text-muted">
                                No hay productos ni servicios en el catálogo.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Cliente Rápido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formClienteRapido" onsubmit="event.preventDefault(); guardarClienteRapido();">
                        @csrf
                        <div class="mb-2">
                            <label>Nombre Completo *</label>
                            <input type="text" name="nombre" id="nuevoNombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono *</label>
                            <input required type="text" name="telefono" id="nuevoTelefono" class="form-control" placeholder="0000-0000" inputmode="numeric" maxlength="9" oninput="formatoTel(this)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DUI *</label>
                            <input required type="text" name="dui" id="nuevoDui" class="form-control" placeholder="00000000-0" inputmode="numeric" maxlength="10" oninput="formatoDui(this)">
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3">Guardar y Seleccionar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

<script>
        function formatoTel(input) {
        let valor = input.value.replace(/\D/g, '');
        if (valor.length > 4) {
            valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
        }
        input.value = valor;
    }

    function formatoDui(input) {
        let valor = input.value.replace(/\D/g, '');
        if (valor.length > 8) {
            valor = valor.substring(0, 8) + '-' + valor.substring(8, 10);
        }
        input.value = valor;
    }

    // --- FUNCIÓN PARA GUARDAR EL CLIENTE POR AJAX ---
    function guardarClienteRapido() {
        let form = document.getElementById('formClienteRapido');
        let formData = new FormData(form);

        fetch("{{ route('clientes.rapido') }}", {
            method: 'POST',
            body: formData,
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                // Formateamos cómo se verá en el buscador
                let texto = data.cliente.nombre;
                if(data.cliente.telefono) texto += ' | Tel: ' + data.cliente.telefono;
                if(data.cliente.dui) texto += ' | DUI: ' + data.cliente.dui;
                
                // Lo agregamos a Select2 y lo seleccionamos
                let nuevaOpcion = new Option(texto, data.cliente.id, true, true);
                $('#clienteSelect').append(nuevaOpcion).trigger('change');
                
                // Cerramos el modal y limpiamos las cajas de texto
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
                modal.hide();
                form.reset();
                
                // Alerta de éxito
                Swal.fire('¡Éxito!', 'Cliente registrado y seleccionado.', 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error capturado:', error);
            let mensaje = "Error desconocido al intentar conectar con el servidor.";
            
            // Si la base de datos o Laravel devuelven un error específico
            if (error.errors) {
                mensaje = Object.values(error.errors).flat().join("\n");
            } else if (error.message) {
                mensaje = error.message;
            }
            
            Swal.fire('Error al guardar', mensaje, 'error');
        });
    }

        document.addEventListener('DOMContentLoaded', function() {
            let carrito = []; // Memoria del carrito
            const tasaIVA = 0.13; // 13% de IVA de El Salvador
            const inputCodigo = document.getElementById('codigo_producto');
            $('.select2').select2({
                placeholder: "Escriba para buscar un cliente...",
                allowClear: true,
                width: '100%' // Para que no rompa tu diseño de Bootstrap
            });
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
                        // Cálculos a la inversa (El precio del producto YA incluye IVA)
                        let precioFinalUnitario = parseFloat(item.precio); 

                        // 1. Extraemos la base dividiendo entre 1.13
                        let precioUnitarioSinIva = precioFinalUnitario / 1.13; 

                        // 2. Obtenemos cuánto de ese precio era puro IVA
                        let montoIvaUnitario = precioFinalUnitario - precioUnitarioSinIva; 

                        // 3. Multiplicamos por la cantidad que lleva el cliente
                        let subtotalLinea = precioUnitarioSinIva * item.cantidad;
                        let ivaLinea = montoIvaUnitario * item.cantidad;
                        
                        subtotalGlobal += subtotalLinea;
                        ivaGlobal += ivaLinea;

                        // --- AQUÍ ESTÁ EL BLINDAJE VISUAL DEL STOCK ---
                        // Si es un servicio no le ponemos límite, si es producto le ponemos límite maximo = stock_actual
                        let esServicio = (item.es_servicio == 1 || item.es_servicio === true);
                        let maxAtributo = esServicio ? "" : `max="${item.stock_actual}"`;

                        // Dibujar tabla
                        cuerpo.innerHTML += `
                            <tr>
                                <td class="fw-bold">${item.codigo}</td>
                                <td class="text-start">${item.nombre}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center mx-auto" style="width: 70px;" value="${item.cantidad}" min="1" ${maxAtributo} onchange="cambiarCantidad(${index}, this.value)">
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
            // --- 2. Búsqueda por AJAX (Lector de barras, Código o Nombre) ---

            window.buscarProducto = function(termino) {
                if(!termino) return;
                
                fetch(`/ventas/buscar-producto/${encodeURIComponent(termino)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.existe) {
                            let prod = data.producto;
                            
                            // Determinamos si es un servicio (para ignorar el stock)
                            let esServicio = (prod.es_servicio == 1 || prod.es_servicio === true);
                            
                            let existeEnCarrito = carrito.findIndex(p => p.id === prod.id);
                            
                            if (existeEnCarrito !== -1) {
                                // Si YA ESTÁ en el carrito, revisamos si sumar 1 excede el stock_actual
                                // OJO: Aquí estaba el error, ahora dice prod.stock_actual
                                if (!esServicio && carrito[existeEnCarrito].cantidad >= prod.stock_actual) {
                                    Swal.fire('⚠️ Stock Insuficiente', `Solo tienes ${prod.stock_actual} unidades de "${prod.nombre}" en bodega. No puedes agregar más.`, 'warning');
                                    inputCodigo.value = '';
                                    return; // Cortamos la ejecución aquí, no se agrega
                                }
                                carrito[existeEnCarrito].cantidad += 1;
                            } else {
                                // Si ES NUEVO en el carrito, revisamos si de casualidad el stock_actual es 0
                                // OJO: Aquí también ahora dice prod.stock_actual
                                if (!esServicio && prod.stock_actual <= 0) {
                                    Swal.fire('❌ Agotado', `El producto "${prod.nombre}" se ha quedado sin stock.`, 'error');
                                    inputCodigo.value = '';
                                    return; // Cortamos la ejecución
                                }
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
                    })
                    .catch(error => console.error('Error en búsqueda:', error));
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
            // --- Cambio manual de cantidad en el carrito ---
            window.cambiarCantidad = function(index, nuevaCantidad) {
                let cant = parseInt(nuevaCantidad);
                let item = carrito[index];
                
                if (cant > 0) {
                    let esServicio = (item.es_servicio == 1 || item.es_servicio === true);
                    
                    // Verificamos si el número digitado supera el stock en bodega
                    if (!esServicio && cant > item.stock_actual) {
                        Swal.fire('⚠️ Stock Excedido', `Trataste de poner ${cant}, pero solo hay ${item.stock_actual} disponibles.`, 'warning');
                        item.cantidad = item.stock_actual; // Lo forzamos a bajar al máximo permitido
                    } else {
                        item.cantidad = cant;
                    }
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

            // --- Búsqueda en tiempo real dentro del Modal de Servicios ---
            const buscadorServicios = document.getElementById('buscadorServicios');
            
            if (buscadorServicios) {
                buscadorServicios.addEventListener('keyup', function() {
                    let filtro = this.value.toLowerCase();
                    let items = document.querySelectorAll('.item-servicio');

                    items.forEach(function(item) {
                        // Busca coincidencia en todo el texto del ítem (nombre y código)
                        let texto = item.innerText.toLowerCase();
                        if (texto.includes(filtro)) {
                            item.classList.remove('d-none');
                            item.classList.add('d-flex');
                        } else {
                            item.classList.remove('d-flex');
                            item.classList.add('d-none');
                        }
                    });
                });
            }

            // Opcional: Limpiar el buscador cada vez que se abre el modal
            const modalServicios = document.getElementById('modalServicios');
            if (modalServicios) {
                modalServicios.addEventListener('shown.bs.modal', function () {
                    buscadorServicios.value = '';
                    buscadorServicios.dispatchEvent(new Event('keyup')); // Resetea la lista
                    buscadorServicios.focus(); // Pone el cursor listo para escribir
                });
            }
        });
</script>
@endpush