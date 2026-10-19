@extends('layouts.app')

@section('title', 'Punto de Venta - MotoTaller')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .tabla-carrito th, .tabla-carrito td { vertical-align: middle; }
        .resumen-card { position: sticky; top: 20px; }
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
            <p class="mb-0"><strong>Vehículo:</strong> {{ $orden->motocicleta->placa }} - {{$orden->motocicleta->marca }}</p>
            
            <input type="hidden" name="orden_entrada_id" form="formVenta" value="{{ $orden->id }}">
            <input type="hidden" name="cliente_id" form="formVenta" value="{{ $orden->cliente_id }}">
        @else
            <h5 class="card-title text-success mb-3"> Venta Directa (Mostrador)</h5>
            
            <label class="form-label"><strong>Seleccionar Cliente:</strong></label>
            <div class="d-flex gap-2 mb-4">
                <div class="col-lg-8">
                    <select name="cliente_id" id="clienteSelect" form="formVenta" class="form-select select2" required>
                        <option value="1">VENTA DE MOSTRADOR (Consumidor Final)</option>
                        
                        <?php foreach($clientes as$cliente): ?>
                            @if($cliente->id != 1)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->nombre }} 
                                    {{ $cliente->telefono ? ' \vert{} Tel: '.$cliente->telefono : '' }} 
                                    {{ $cliente->dui ? ' \vert{} DUI: '.$cliente->dui : '' }}
                                </option>
                            @endif
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="button" class="btn btn-primary h-100" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="fas fa-plus"></i> 
                    </button>
                </div>
            </div>
        @endif    

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-3 border-success">
                    <div class="card-body">
                        <label class="form-label fw-bold text-success"> Buscar Productos o Servicios</label>
                        <div class="input-group mb-2">
                            <input type="text" id="codigo_producto" class="form-control form-control-lg border-success" placeholder="Código de producto" autofocus>
                            <button class="btn btn-success" type="button" id="btnBuscar">➕</button>
                        </div>
                        <button type="button" id="btnEscanerCamara" class="btn btn-sm btn-outline-dark">
                            <i class="fas "></i> Escáner
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalServicios">
                             Catálogo
                        </button>
                        <!-- BOTÓN DE COMBOS -->
                        <button type="button" class="btn btn-sm btn-warning ms-2 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalCombos">
                            Agregar Combo
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
                        
                        <!-- ÁREA DE COMBOS APLICADOS -->
                        <div id="listaCombosAplicados" class="mb-3"></div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal (Sin IVA):</span>
                            <span class="fw-bold">$<span id="txtSubtotal">0.00</span></span>
                        </div>
                        
                        <!-- FILA DE DESCUENTO -->
                        <div class="d-flex justify-content-between mb-2 text-success" id="filaDescuento" style="display: none !important;">
                            <span class="fw-bold">Descuento (Combos):</span>
                            <span class="fw-bold">-$<span id="txtDescuento">0.00</span></span>
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
                            <input type="hidden" name="descuento_final" id="inputDescuentoHidden" value="0">
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

    <!-- MODAL CATÁLOGO NORMAL -->
    <div class="modal fade" id="modalServicios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg"> 
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"> Catálogo de Productos y Servicios</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light">🔍</span>
                        <input type="text" id="buscadorServicios" class="form-control" placeholder="Buscar por nombre o código..." autocomplete="off">
                    </div>

                    <div class="list-group list-group-flush" id="listaServicios">
                        <?php foreach($servicios as$servicio): ?>
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
                        <?php endforeach; ?>

                        @if(isset($productos))
                            <?php foreach($productos as$producto): ?>
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
                            <?php endforeach; ?>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE COMBOS -->
    <div class="modal fade" id="modalCombos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg"> 
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">Paquetes y Combos Promocionales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="list-group list-group-flush">
                        <?php if(count($combos) > 0): ?>
                            <?php foreach($combos as$combo): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3 border-bottom">
                                    <div>
                                        <h6 class="mb-1 fw-bold fs-5">{{ $combo->nombre }}</h6>
                                        <small class="text-muted d-block mb-2">Contiene: 
                                            <?php foreach($combo->productos as$p): ?>
                                                {{ $p->pivot->cantidad }}x {{$p->nombre }},
                                            <?php endforeach; ?>
                                        </small>
                                    </div>
                                    <div class="text-end ms-3">
                                        <span class="d-block text-success fw-bold fs-4 mb-1">${{ number_format($combo->precio_total, 2) }}</span>
                                        <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-dismiss="modal" onclick="agregarCombo({{ $combo->id }})">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">No hay combos activos en este momento.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CLIENTE RÁPIDO -->
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
    document.addEventListener('DOMContentLoaded', function() {

            @if(session('success'))
                @if(session('tipo_documento') == 'Ticket')
                    fetch("{{ url('/ventas/imprimir-red') }}/{{ session('ticket_id') }}")
                        .catch(error => console.error('Error al auto-imprimir:', error));
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cobro Procesado con Éxito!',
                        text: 'Ticket impreso automáticamente.',
                        showCancelButton: true,
                        confirmButtonText: 'Reimprimir',
                        cancelButtonText: 'Aceptar',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#198754',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Enviando a impresora...',
                                text: 'Por favor espere.',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading() }
                            });

                            fetch("{{ url('/ventas/imprimir-red') }}/{{ session('ticket_id') }}")
                                .then(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Reimpreso!',
                                        text: 'El ticket se volvió a imprimir.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                })
                                .catch(() => {
                                    Swal.fire('Error', 'No se pudo comunicar con la impresora', 'error');
                                });
                        }
                    });

                @else
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cobro Procesado!',
                        text: 'Documento registrado.',
                        confirmButtonColor: '#198754'
                    });
                @endif
            @endif

        });

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
            if (!response.ok) { return response.json().then(err => { throw err; }); }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let texto = data.cliente.nombre;
                if (data.cliente.telefono) texto += ' | Tel: ' + data.cliente.telefono;
                if (data.cliente.dui) texto += ' | DUI: ' + data.cliente.dui;
                
                let nuevaOpcion = new Option(texto, data.cliente.id, true, true);
                $('#clienteSelect').append(nuevaOpcion).trigger('change');
                
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
                modal.hide();
                form.reset();
                
                Swal.fire('¡Éxito!', 'Cliente registrado y seleccionado.', 'success');
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            let mensaje = "Error desconocido al intentar conectar con el servidor.";
            if (error.errors) {
                mensaje = Object.values(error.errors).flat().join("\n");
            } else if (error.message) {
                mensaje = error.message;
            }
            Swal.fire('Error al guardar', mensaje, 'error');
        });
    }

    let carrito = []; 
    let combosAplicados = []; 
    const tasaIVA = 0.13; 
    
    const combosDisponibles = @json($combos ?? []);

    document.addEventListener('DOMContentLoaded', function() {
        const inputCodigo = document.getElementById('codigo_producto');
        $('.select2').select2({ placeholder: "Escriba para buscar un cliente...", allowClear: true, width: '100%' });

        window.renderizarCarrito = function() {
            const cuerpo = document.getElementById('cuerpoCarrito');
            const inputsOcultos = document.getElementById('inputsCarritoOcultos');
            cuerpo.innerHTML = '';
            inputsOcultos.innerHTML = '';
            
            let subtotalNormalSinIva = 0;
            let totalNormalConIva = 0;

            if (carrito.length === 0) {
                cuerpo.innerHTML = '<tr id="filaVacia"><td colspan="6" class="text-muted py-4">El carrito está vacío. Escanea un repuesto para empezar.</td></tr>';
                document.getElementById('btnCobrar').disabled = true;
            } else {
                document.getElementById('btnCobrar').disabled = false;
                
                carrito.forEach((item, index) => {
                    let precioFinalUnitario = parseFloat(item.precio); 
                    let precioUnitarioSinIva = precioFinalUnitario / 1.13; 
                    let montoIvaUnitario = precioFinalUnitario - precioUnitarioSinIva; 

                    let subtotalLinea = precioUnitarioSinIva * item.cantidad;
                    let ivaLinea = montoIvaUnitario * item.cantidad;
                    
                    subtotalNormalSinIva += subtotalLinea;
                    totalNormalConIva += (precioFinalUnitario * item.cantidad);

                    let esServicio = (item.es_servicio == 1 || item.es_servicio === true);
                    let maxAtributo = esServicio ? "" : `max="${item.stock_actual}"`;

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

                    inputsOcultos.innerHTML += `
                        <input type="hidden" name="productos[${index}][id]" value="${item.id}">
                        <input type="hidden" name="productos[${index}][cantidad]" value="${item.cantidad}">
                        <input type="hidden" name="productos[${index}][precio_unitario_sin_iva]" value="${precioUnitarioSinIva.toFixed(2)}">
                        <input type="hidden" name="productos[${index}][monto_iva_unitario]" value="${montoIvaUnitario.toFixed(2)}">
                        <input type="hidden" name="productos[${index}][subtotal_linea]" value="${subtotalLinea.toFixed(2)}">
                    `;
                });
            }

            let ahorroTotalConIva = 0;
            combosAplicados.forEach(c => ahorroTotalConIva += c.ahorroConIva);

            if (ahorroTotalConIva > totalNormalConIva) ahorroTotalConIva = totalNormalConIva;
            let ahorroSinIva = ahorroTotalConIva / 1.13;

            let subtotalFinalSinIva = subtotalNormalSinIva; 
            let ivaFinal = (subtotalNormalSinIva - ahorroSinIva) * 0.13; 
            let totalFinal = totalNormalConIva - ahorroTotalConIva; 

            document.getElementById('txtSubtotal').innerText = subtotalFinalSinIva.toFixed(2);
            
            if (ahorroTotalConIva > 0) {
                document.getElementById('filaDescuento').style.setProperty('display', 'flex', 'important');
                document.getElementById('txtDescuento').innerText = ahorroSinIva.toFixed(2);
            } else {
                document.getElementById('filaDescuento').style.setProperty('display', 'none', 'important');
            }
            
            document.getElementById('txtIva').innerText = ivaFinal.toFixed(2);
            document.getElementById('txtTotal').innerText = totalFinal.toFixed(2);

            document.getElementById('inputSubtotal').value = subtotalFinalSinIva.toFixed(2);
            document.getElementById('inputDescuentoHidden').value = ahorroSinIva.toFixed(2);
            document.getElementById('inputIva').value = ivaFinal.toFixed(2);
            document.getElementById('inputTotal').value = totalFinal.toFixed(2);
        }

        window.renderizarCombosAplicados = function() {
            let div = document.getElementById('listaCombosAplicados');
            div.innerHTML = '';
            combosAplicados.forEach((c, index) => {
                div.innerHTML += `
                    <div class="alert alert-warning p-2 mb-2 d-flex justify-content-between align-items-center shadow-sm border-warning">
                        <small class="fw-bold text-dark">🎁 ${c.nombre} (Ahorró: $${c.ahorroConIva.toFixed(2)})</small>
                        <button type="button" class="btn-close btn-close-sm" onclick="quitarCombo(${index})" title="Quitar descuento"></button>
                    </div>
                `;
            });
        }

        window.agregarCombo = function(idCombo) {
            let combo = combosDisponibles.find(c => c.id == idCombo);
            if (!combo) return;

            let stockSuficiente = true;
            let mensajeError = '';

            for (let prod of combo.productos) {
                let esServicio = (prod.es_servicio == 1 || prod.es_servicio === true);
                if (!esServicio) {
                    let cantRequerida = parseInt(prod.pivot.cantidad);
                    let indexEnCarrito = carrito.findIndex(p => p.id === prod.id);
                    let cantEnCarrito = indexEnCarrito !== -1 ? carrito[indexEnCarrito].cantidad : 0;
                    
                    if ((cantEnCarrito + cantRequerida) > prod.stock_actual) {
                        stockSuficiente = false;
                        mensajeError = `Falta inventario de "${prod.nombre}". Requiere ${cantRequerida}, Disponible extra: ${prod.stock_actual - cantEnCarrito}.`;
                        break;
                    }
                }
            }

            if (!stockSuficiente) {
                Swal.fire('❌ Stock Insuficiente', mensajeError, 'error');
                return;
            }

            let sumaNormalConIva = 0;
            combo.productos.forEach(prod => {
                let cant = parseInt(prod.pivot.cantidad);
                sumaNormalConIva += (parseFloat(prod.precio) * cant);
                
                let index = carrito.findIndex(p => p.id === prod.id);
                if (index !== -1) {
                    carrito[index].cantidad += cant;
                } else {
                    let nuevoProd = {...prod};
                    nuevoProd.cantidad = cant;
                    carrito.push(nuevoProd);
                }
            });

            let ahorroConIva = sumaNormalConIva - parseFloat(combo.precio_total);
            if (ahorroConIva < 0) ahorroConIva = 0; 

            combosAplicados.push({
                nombre: combo.nombre,
                ahorroConIva: ahorroConIva
            });

            renderizarCombosAplicados();
            renderizarCarrito();
            Swal.fire({
                icon: 'success',
                title: '¡Combo Aplicado!',
                text: `Se aplicó un descuento de $${ahorroConIva.toFixed(2)}.`,
                timer: 2000,
                showConfirmButton: false
            });
        }

        window.quitarCombo = function(index) {
            combosAplicados.splice(index, 1);
            renderizarCombosAplicados();
            renderizarCarrito();
        }

        window.buscarProducto = function(termino) {
            if (!termino) return;
            fetch(`/ventas/buscar-producto/${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.existe) {
                        let prod = data.producto;
                        let esServicio = (prod.es_servicio == 1 || prod.es_servicio === true);
                        let existeEnCarrito = carrito.findIndex(p => p.id === prod.id);
                        
                        if (existeEnCarrito !== -1) {
                            if (!esServicio && carrito[existeEnCarrito].cantidad >= prod.stock_actual) {
                                Swal.fire('⚠️ Stock Insuficiente', `Solo tienes ${prod.stock_actual} unidades en bodega.`, 'warning');
                                inputCodigo.value = '';
                                return;
                            }
                            carrito[existeEnCarrito].cantidad += 1;
                        } else {
                            if (!esServicio && prod.stock_actual <= 0) {
                                Swal.fire('❌ Agotado', `El producto se ha quedado sin stock.`, 'error');
                                inputCodigo.value = '';
                                return;
                            }
                            prod.cantidad = 1;
                            carrito.push(prod);
                        }
                        
                        renderizarCarrito();
                        inputCodigo.value = ''; 
                        inputCodigo.focus();
                    } else {
                        Swal.fire('No encontrado', 'El código no existe o el producto está inactivo.', 'error');
                        inputCodigo.select();
                    }
                })
                .catch(error => console.error('Error en búsqueda:', error));
        }   

        document.getElementById('btnBuscar').addEventListener('click', () => buscarProducto(inputCodigo.value));
        inputCodigo.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                buscarProducto(this.value);
            }
        });

        window.cambiarCantidad = function(index, nuevaCantidad) {
            let cant = parseInt(nuevaCantidad);
            let item = carrito[index];
            if (cant > 0) {
                let esServicio = (item.es_servicio == 1 || item.es_servicio === true);
                if (!esServicio && cant > item.stock_actual) {
                    Swal.fire('⚠️ Stock Excedido', `Solo hay ${item.stock_actual} disponibles.`, 'warning');
                    item.cantidad = item.stock_actual; 
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
            buscarProducto(decodedText);
        }

        const buscadorServicios = document.getElementById('buscadorServicios');
        if (buscadorServicios) {
            buscadorServicios.addEventListener('keyup', function() {
                let filtro = this.value.toLowerCase();
                let items = document.querySelectorAll('.item-servicio');
                items.forEach(function(item) {
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
    });
</script>
@endpush