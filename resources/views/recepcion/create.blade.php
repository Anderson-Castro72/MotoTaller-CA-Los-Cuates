<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción - MotoTaller</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-primary fw-bold mb-4">Recepción de Motocicleta</h2>
        <p class="text-muted">Ingresa el DUI o la Placa para autocompletar la información.</p>

        <form action="{{ route('recepcion.store') }}" method="POST" id="formRecepcion" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">👤 Datos del Cliente</h5>
                            
                            <div class="form-check form-switch d-none" id="div_switch_editar_cliente">
                                <input class="form-check-input" type="checkbox" id="switch_editar_cliente">
                                <label class="form-check-label text-white small" for="switch_editar_cliente">Habilitar Edición</label>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">DUI *</label>
                                <input type="text" inputmode="numeric" name="dui" id="dui" class="form-control border-primary" placeholder="00000000-0" required autofocus maxlength="10" pattern="^\d{8}-\d$">                                <small class="text-muted" id="status_cliente">Escribe el DUI para buscar...</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo *</label>
                                <input type="text" name="nombre" id="nombre_cliente" class="form-control inputs-cliente" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="text" inputmode="numeric" name="telefono" id="telefono_cliente" class="form-control inputs-cliente"  required maxlength="9" pattern="^\d{4}-\d{4}$">                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">🏍️ Datos de la Motocicleta</h5>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="motocicleta_id" id="motocicleta_id">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Placa *</label>
                                <input type="text" name="placa" id="placa" class="form-control border-primary text-uppercase" placeholder="M-----" required>
                                <small class="text-muted" id="status_moto">Escribe la placa para buscar...</small>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Marca *</label>
                                    <input type="text" name="marca" id="marca_moto" class="form-control inputs-moto" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Modelo *</label>
                                    <input type="text" name="modelo" id="modelo_moto" class="form-control inputs-moto" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Color *</label>
                                    <input type="text" name="color" id="color_moto" class="form-control inputs-moto" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Año *</label>
                                    <input type="number" name="anio" id="anio_moto" class="form-control inputs-moto" required maxlength="4">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow-sm border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">📋 Detalles del Ingreso (Condiciones actuales)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kilometraje de Entrada *</label>
                                    <div class="input-group">
                                        <input type="number" inputmode="numeric" name="kilometraje_entrada" class="form-control border-warning" placeholder="Ej. 15000" required min="0">
                                        <span class="input-group-text bg-light">Km</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nivel de Combustible *</label>
                                    <select name="nivel_combustible" class="form-select border-warning" required>
                                        <option value="" disabled selected>Selecciona el nivel...</option>
                                        <option value="E">E (Vacío / Reserva)</option>
                                        <option value="1/4">1/4 de tanque</option>
                                        <option value="1/2">1/2 (Mitad)</option>
                                        <option value="3/4">3/4 de tanque</option>
                                        <option value="F">F (Lleno)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Falla Reportada (Lo que dice el cliente) *</label>
                                    <textarea name="falla_reportada" class="form-control" rows="3" placeholder="Ej. La moto pierde fuerza en subidas y se apaga..." required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Observaciones (Piezas faltantes, rayones, etc.)</label>
                                    <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej. Espejo derecho quebrado, ingresa sin casco, llanta trasera lisa..."></textarea>
                                </div>
                            </div>

                            <hr class="text-muted">
                            
                            <h6 class="fw-bold mb-3 text-secondary">📸 Evidencia Fotográfica (Opcional)</h6>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-3">
                                    <label class="form-label small">Foto Frontal</label>
                                    <input type="file" name="foto_1" class="form-control form-control-sm" accept="image/*" capture="environment">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label class="form-label small">Foto Lateral Derecho</label>
                                    <input type="file" name="foto_2" class="form-control form-control-sm" accept="image/*" capture="environment">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label class="form-label small">Foto Trasera</label>
                                    <input type="file" name="foto_3" class="form-control form-control-sm" accept="image/*" capture="environment">
                                </div>
                                <div class="col-md-3 col-6 mb-3">
                                    <label class="form-label small">Foto Lateral Izquierdo</label>
                                    <input type="file" name="foto_4" class="form-control form-control-sm" accept="image/*" capture="environment">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-5">
                <button type="submit" class="btn btn-success btn-lg fw-bold px-5">Crear Orden de Entrada</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // =========================================
            // LÓGICA INTELIGENTE DEL CLIENTE (DUI)
            // =========================================
            const inputDui = document.getElementById('dui');
            const inputNombre = document.getElementById('nombre_cliente');
            const inputTelefono = document.getElementById('telefono_cliente');
            const inputClienteId = document.getElementById('cliente_id');
            const statusCliente = document.getElementById('status_cliente');
            
            const divSwitchEditar = document.getElementById('div_switch_editar_cliente');
            const switchEditar = document.getElementById('switch_editar_cliente');
            const inputsCliente = document.querySelectorAll('.inputs-cliente');

            // 1. Formateo estricto en tiempo real
            inputDui.addEventListener('input', function(e) {
                // Quitamos cualquier letra o símbolo (solo dejamos números)
                let valor = this.value.replace(/\D/g, ''); 
                
                // Limitamos a un máximo de 9 números puros (8 del cuerpo + 1 verificador)
                if (valor.length > 9) {
                    valor = valor.substring(0, 9);
                }
                
                // Inyectamos el guion justo antes del último dígito
                if (valor.length > 8) {
                    valor = valor.substring(0, 8) + '-' + valor.substring(8);
                }
                
                // Devolvemos el valor formateado a la casilla
                this.value = valor;
            });

            // 1. Formateo estricto del Teléfono en tiempo real (Ej: 1234-5678)
            inputTelefono.addEventListener('input', function(e) {
                // Quitamos cualquier letra o símbolo
                let valor = this.value.replace(/\D/g, ''); 
                
                // Limitamos a un máximo de 8 números puros
                if (valor.length > 8) {
                    valor = valor.substring(0, 8);
                }
                
                // Inyectamos el guion justo después del cuarto dígito
                if (valor.length > 4) {
                    valor = valor.substring(0, 4) + '-' + valor.substring(4);
                }
                
                // Devolvemos el valor formateado a la casilla
                this.value = valor;
            });
            inputDui.addEventListener('change', async function() {
                const dui = this.value.trim();
                if(!dui) return;

                statusCliente.innerHTML = '<span class="text-primary">Buscando en base de datos...</span>';

                try {
                    // Petición AJAX al controlador que crearemos luego
                    let response = await fetch(`/recepcion/verificar-cliente/${dui}`);
                    let data = await response.json();

                    if(data.existe) {
                        statusCliente.innerHTML = '<span class="text-success fw-bold">✓ Cliente encontrado</span>';
                        // Rellenamos los campos
                        inputClienteId.value = data.cliente.id;
                        inputNombre.value = data.cliente.nombre;
                        inputTelefono.value = data.cliente.telefono;

                        // Bloqueamos los campos visual y funcionalmente
                        inputsCliente.forEach(input => input.setAttribute('readonly', true));
                        inputsCliente.forEach(input => input.classList.add('bg-light', 'text-muted'));

                        // ¡Aparece el Switch para editar!
                        divSwitchEditar.classList.remove('d-none');
                        switchEditar.checked = false; // Se asegura de iniciar apagado
                        
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Cliente Autocompletado', showConfirmButton: false, timer: 1500 });
                    } else {
                        // Si es nuevo cliente
                        statusCliente.innerHTML = '<span class="text-warning fw-bold">Nuevo cliente</span>';
                        inputClienteId.value = '';
                        inputNombre.value = '';
                        inputTelefono.value = '';
                        
                        // Habilitamos los campos y ocultamos el switch
                        inputsCliente.forEach(input => input.removeAttribute('readonly'));
                        inputsCliente.forEach(input => input.classList.remove('bg-light', 'text-muted'));
                        divSwitchEditar.classList.add('d-none');
                    }
                } catch(error) {
                    console.error("Error buscando cliente", error);
                }
            });

            // Vigilar cuando el usuario activa/desactiva el Switch de edición
            switchEditar.addEventListener('change', function() {
                if(this.checked) {
                    // Activar Edición
                    inputsCliente.forEach(input => input.removeAttribute('readonly'));
                    inputsCliente.forEach(input => input.classList.remove('bg-light', 'text-muted'));
                } else {
                    // Bloquear Edición de nuevo
                    inputsCliente.forEach(input => input.setAttribute('readonly', true));
                    inputsCliente.forEach(input => input.classList.add('bg-light', 'text-muted'));
                }
            });


            // =========================================
            // LÓGICA INTELIGENTE DE LA MOTO (PLACA)
            // =========================================
            const inputPlaca = document.getElementById('placa');
            const inputMarca = document.getElementById('marca_moto');
            const inputModelo = document.getElementById('modelo_moto');
            const inputColor = document.getElementById('color_moto');
            const inputAnio = document.getElementById('anio_moto');
            const inputMotoId = document.getElementById('motocicleta_id');
            const statusMoto = document.getElementById('status_moto');
            const inputsMoto = document.querySelectorAll('.inputs-moto');

            inputPlaca.addEventListener('change', async function() {
                const placa = this.value.trim().toUpperCase();
                this.value = placa; // Convertimos forzosamente a mayúsculas
                if(!placa) return;

                statusMoto.innerHTML = '<span class="text-primary">Buscando en base de datos...</span>';

                try {
                    let response = await fetch(`/recepcion/verificar-moto/${placa}`);
                    let data = await response.json();

                    if(data.existe) {
                        statusMoto.innerHTML = '<span class="text-success fw-bold">✓ Moto encontrada</span>';
                        inputMotoId.value = data.moto.id;
                        inputMarca.value = data.moto.marca;
                        inputModelo.value = data.moto.modelo;
                        inputColor.value = data.moto.color;
                        inputAnio.value = data.moto.anio || '';

                        // Aquí bloqueamos los campos PERMANENTEMENTE (Sin switch)
                        inputsMoto.forEach(input => input.setAttribute('readonly', true));
                        inputsMoto.forEach(input => input.classList.add('bg-light', 'text-muted'));

                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Motocicleta Autocompletada', showConfirmButton: false, timer: 1500 });
                    } else {
                        statusMoto.innerHTML = '<span class="text-warning fw-bold">Nueva motocicleta</span>';
                        inputMotoId.value = '';
                        inputMarca.value = '';
                        inputModelo.value = '';
                        inputColor.value = '';
                        inputAnio.value = '';

                        inputsMoto.forEach(input => input.removeAttribute('readonly'));
                        inputsMoto.forEach(input => input.classList.remove('bg-light', 'text-muted'));
                    }
                } catch(error) {
                    console.error("Error buscando moto", error);
                }
            });

            // 2. Validación final antes de guardar (DUI y Teléfono)
            formRecepcion.addEventListener('submit', function(e) {
                const duiActual = inputDui.value;
                const telefonoActual = inputTelefono.value;
                
                // Validar DUI
                if (duiActual.length > 0 && duiActual.length !== 10) {
                    e.preventDefault(); 
                    Swal.fire({
                        icon: 'error',
                        title: 'DUI Incompleto',
                        text: 'El DUI debe tener exactamente 10 caracteres (Ej: 12345678-9).',
                        confirmButtonColor: '#0d6efd'
                    });
                    inputDui.focus();
                    return; // Detenemos la ejecución aquí
                }

                // Validar Teléfono
                if (telefonoActual.length > 0 && telefonoActual.length !== 9) {
                    e.preventDefault(); 
                    Swal.fire({
                        icon: 'error',
                        title: 'Teléfono Incompleto',
                        text: 'El número de teléfono debe tener exactamente 9 caracteres (Ej: 1234-5678).',
                        confirmButtonColor: '#0d6efd'
                    });
                    inputTelefono.focus();
                }
            });

        });

        // Mostrar alerta de éxito al recargar la página tras guardar
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Excelente!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#198754'
                });
            @endif
    </script>
</body>
</html>