<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - MotoTaller</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-primary fw-bold mb-4">Editar Producto / Servicio</h2>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('inventario.update', $producto->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Código de Barras</label>
                            <div class="input-group">
                                <input type="text" name="codigo" id="codigo_input" class="form-control" value="{{ old('codigo', $producto->codigo) }}" {{ $producto->es_servicio ? 'readonly' : 'autofocus' }}>
                                <button type="button" class="btn btn-outline-secondary" id="btn-scan-camara" {{ $producto->es_servicio ? 'disabled' : '' }}>
                                    📷 Escanear 
                                </button>
                            </div>
                            <div id="reader" width="100%" class="mt-2" style="display: none;"></div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label">Nombre del Producto / Servicio *</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $producto->nombre) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio (Sin IVA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.10" name="precio_sin_iva" class="form-control" value="{{ old('precio_sin_iva', $producto->precio_sin_iva) }}" required min="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Actual *</label>
                            <input type="number" name="stock_actual" class="form-control {{ $producto->es_servicio ? 'bg-secondary text-white' : '' }}" value="{{ old('stock_actual', $producto->stock_actual) }}" required min="0" {{ $producto->es_servicio ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Mínimo (Alerta) *</label>
                            <input type="number" name="stock_minimo" class="form-control {{ $producto->es_servicio ? 'bg-secondary text-white' : '' }}" value="{{ old('stock_minimo', $producto->stock_minimo) }}" required min="0" {{ $producto->es_servicio ? 'readonly' : '' }}>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="hidden" name="es_servicio" value="0">
                            <input class="form-check-input" type="checkbox" name="es_servicio" value="1" id="es_servicio" {{ $producto->es_servicio ? 'checked' : '' }}>
                            <label class="form-check-label" for="es_servicio">Es un servicio (No descuenta stock)</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold">Actualizar Producto</button>
                    <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Alertas de Error (SweetAlert2)
            @if($errors->any())
                let errorHtml = '<ul class="text-start list-unstyled">';
                @foreach($errors->all() as $error)
                    errorHtml += '<li>⚠️ {{ $error }}</li>';
                @endforeach
                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo actualizar',
                    html: errorHtml,
                    confirmButtonColor: '#0d6efd'
                });
            @endif

            // 2. Bloquear números negativos en tiempo real
            const numberInputs = document.querySelectorAll('input[type="number"]');
            numberInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = Math.abs(this.value); 
                    }
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === '-') e.preventDefault();
                });
            });

            // 3. Lógica del checkbox de servicios
            const checkServicio = document.getElementById('es_servicio');
            const inputCodigo = document.getElementById('codigo_input');
            const btnScan = document.getElementById('btn-scan-camara');
            const inputStock = document.querySelector('input[name="stock_actual"]');
            const inputMinimo = document.querySelector('input[name="stock_minimo"]');
            const codigoOriginal = "{{ $producto->codigo }}"; // Guardamos el código actual
            
            // Si cambian el checkbox de servicio
            checkServicio.addEventListener('change', function() {
                if(this.checked) {
                    // Si se marca como servicio: bloqueamos código, stock y cámara
                    inputCodigo.setAttribute('readonly', true); 
                    if(btnScan) btnScan.disabled = true;
                    
                    inputStock.value = 0;
                    inputMinimo.value = 0;
                    inputStock.setAttribute('readonly', true);
                    inputStock.classList.add('bg-secondary', 'text-white');
                    inputMinimo.setAttribute('readonly', true);
                    inputMinimo.classList.add('bg-secondary', 'text-white');
                } else {
                    // Si se desmarca (vuelve a ser repuesto físico)
                    inputCodigo.value = codigoOriginal; // Regresamos el código que tenía
                    inputCodigo.removeAttribute('readonly');
                    if(btnScan) btnScan.disabled = false;
                    inputCodigo.focus(); 
                    
                    inputStock.value = 1;
                    inputMinimo.value = 1;
                    inputStock.removeAttribute('readonly');
                    inputStock.classList.remove('bg-secondary', 'text-white');
                    inputMinimo.removeAttribute('readonly');
                    inputMinimo.classList.remove('bg-secondary', 'text-white');
                }
            });

            // 4. Función de AJAX para Validar Código
            async function validarCodigoEnBD(codigo) {
                // IMPORTANTÍSIMO: Si está vacío o es el mismo código de este producto, no hacemos la consulta
                if (!codigo || codigo === codigoOriginal) return; 
                
                try {
                    let response = await fetch(`/inventario/verificar-codigo/${codigo}`);
                    let data = await response.json();

                    if (data.existe) {
                        Swal.fire({
                            icon: 'warning',
                            title: '¡Código Duplicado!',
                            text: `El código ${codigo} ya pertenece a otro registro.`,
                            confirmButtonColor: '#d33'
                        });
                        
                        inputCodigo.value = codigoOriginal; // Restauramos el código original
                        inputCodigo.focus();
                    }
                } catch (error) {
                    console.error('Error al validar el código:', error);
                }
            }

            inputCodigo.addEventListener('change', function() {
                validarCodigoEnBD(this.value);
            });

            // 5. Lógica de la Cámara
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

                        validarCodigoEnBD(decodedText);
                    }, function(error) {
                        // Ignorar errores continuos
                    });
                });
            }
        });
    </script>
</body>
</html>