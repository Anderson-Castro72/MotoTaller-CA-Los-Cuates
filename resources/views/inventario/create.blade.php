<!DOCTYPE html>
<html lang="es">
<head>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - MotoTaller</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-primary fw-bold mb-4">Registrar Nuevo Producto</h2>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('inventario.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Código de Barras</label>
                            <div class="input-group">
                                <input type="text" name="codigo" id="codigo_input" class="form-control" autofocus>
                                <button type="button" class="btn btn-outline-secondary" id="btn-scan-camara">
                                    Escanear
                                </button>
                            </div>
                            <div id="reader" width="100%" class="mt-2" style="display: none;"></div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label">Nombre del Producto / Servicio *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio (Sin IVA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.10" name="precio_sin_iva" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Actual *</label>
                            <input type="number" name="stock_actual" class="form-control" value="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Mínimo (Alerta) *</label>
                            <input type="number" name="stock_minimo" class="form-control" value="" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="hidden" name="es_servicio" value="0">
                            <input class="form-check-input" type="checkbox" name="es_servicio" value="1" id="es_servicio">
                            <label class="form-check-label" for="es_servicio">Es un servicio (No descuenta stock)</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold">Guardar Producto</button>
                    <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Alertas de Éxito
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2500, // Se cierra solo después de 2.5 segundos
                toast: true, // Estilo notificación pequeña (opcional, quítalo si quieres alerta grande)
                position: 'top-end'
            });
        @endif

        // 2. Alertas de Error (Validaciones)
        @if($errors->any())
            let errorHtml = '<ul class="text-start list-unstyled">';
            @foreach($errors->all() as $error)
                errorHtml += '<li>⚠️ {{ $error }}</li>';
            @endforeach
            errorHtml += '</ul>';

            Swal.fire({
                icon: 'error',
                title: 'No se pudo guardar',
                html: errorHtml,
                confirmButtonColor: '#0d6efd'
            });
        @endif
    });
</script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ==========================================
        // 1. BLOQUEAR NÚMEROS NEGATIVOS EN TIEMPO REAL
        // ==========================================
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Si el usuario escribe un número menor a 0, lo borramos o lo pasamos a positivo
                if (this.value < 0) {
                    this.value = Math.abs(this.value); // Lo convierte a positivo automáticamente
                }
            });
            // Prevenir que escriban el signo de resta (-) con el teclado
            input.addEventListener('keydown', function(e) {
                if (e.key === '-') {
                    e.preventDefault();
                }
            });
        });

        // ==========================================
        // 2. LÓGICA DEL CHECKBOX DE SERVICIOS
        // ==========================================
        const checkServicio = document.getElementById('es_servicio');
        const inputCodigo = document.getElementById('codigo_input');
        const inputStock = document.querySelector('input[name="stock_actual"]');
        const inputMinimo = document.querySelector('input[name="stock_minimo"]');
        
        // Esta variable viene desde el ProductoController (Ej: TMCA-0001)
        const codigoGenerado = "{{ $siguienteCodigo ?? 'TMCA-0001' }}";

        checkServicio.addEventListener('change', function() {
            if(this.checked) {
                // Si es servicio: pone código automático y DESACTIVA los stocks
                inputCodigo.value = codigoGenerado;
                inputCodigo.setAttribute('readonly', true); 
                
                inputStock.value = 0;
                inputMinimo.value = 0;
                inputStock.setAttribute('readonly', true);
                inputStock.classList.add('bg-secondary', 'text-white'); // Efecto visual de apagado
                
                inputMinimo.setAttribute('readonly', true);
                inputMinimo.classList.add('bg-secondary', 'text-white');
            } else {
                // Si es repuesto: limpia el código y VUELVE A PEDIR el stock
                inputCodigo.value = '';
                inputCodigo.removeAttribute('readonly');
                inputCodigo.focus(); 
                
                inputStock.value = 1;
                inputMinimo.value = 1;
                inputStock.removeAttribute('readonly');
                inputStock.classList.remove('bg-secondary', 'text-white');
                
                inputMinimo.removeAttribute('readonly');
                inputMinimo.classList.remove('bg-secondary', 'text-white');
            }
        });

// ==========================================
        // NUEVA FUNCIÓN: VALIDAR CÓDIGO CON AJAX
        // ==========================================
        async function validarCodigoEnBD(codigo) {
            if (!codigo) return; // Si está vacío, no hace nada
            
            try {
                // Hacemos la consulta invisible a nuestro nuevo endpoint de Laravel
                let response = await fetch(`/inventario/verificar-codigo/${codigo}`);
                let data = await response.json();

                if (data.existe) {
                    // Si Laravel dice que sí existe, disparamos SweetAlert2
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Código Duplicado!',
                        text: `El código ${codigo} ya está registrado en la base de datos. Por favor, ingresa uno distinto o búscalo en la tabla para editarlo.`,
                        confirmButtonColor: '#d33'
                    });
                    
                    // Limpiamos la casilla para obligar al usuario a intentar de nuevo
                    inputCodigo.value = '';
                    inputCodigo.focus();
                }
            } catch (error) {
                console.error('Error al validar el código:', error);
            }
        }

        // 1. Escuchar cuando escriben con teclado o usan escáner USB (evento change)
        inputCodigo.addEventListener('change', function() {
            validarCodigoEnBD(this.value);
        });

        // ==========================================
        // ACTUALIZACIÓN: LÓGICA PARA LA CÁMARA DEL CELULAR
        // ==========================================
        const btnScan = document.getElementById('btn-scan-camara');
        const readerDiv = document.getElementById('reader');
        let html5QrcodeScanner;

        if(btnScan) {
            btnScan.addEventListener('click', function() {
                readerDiv.style.display = 'block';
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", { fps: 10, qrbox: {width: 250, height: 100} }, false);
                
                html5QrcodeScanner.render(function(decodedText) {
                    inputCodigo.value = decodedText; 
                    html5QrcodeScanner.clear();
                    readerDiv.style.display = 'none';

                    // 2. ¡Llamamos a la validación justo después de escanear con el celular!
                    validarCodigoEnBD(decodedText);
                    
                }, function(error) {
                    // Ignorar errores continuos de lectura
                });
            });
        }
    });
</script>
</html>