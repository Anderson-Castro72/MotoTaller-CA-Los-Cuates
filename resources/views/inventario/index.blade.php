<!DOCTYPE html>
<html lang="es">
<head>
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - MotoTaller</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="text-primary fw-bold">Catálogo de Inventario</h2>
                <p class="text-muted">Gestiona los repuestos, accesorios y servicios del taller.</p>
            </div>
            <div class="col-md-4 text-end">
            <a href="{{ route('inventario.create') }}" class="btn btn-success fw-bold">+ Nuevo Producto</a>
            </div><div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('inventario.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="buscar" id="buscar_input" class="form-control" placeholder="Buscar por código o nombre..." value="{{ request('buscar') }}">
                            <button type="button" class="btn btn-outline-secondary" id="btn-scan-buscar">📷 Escanear</button>
                        </div>
                        <div id="reader-buscar" width="100%" class="mt-2" style="display: none;"></div>
                    </div>
                    <div class="col-md-3">
                        <select name="estado" class="form-select">
                        <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="activos" {{ request('estado') == 'activos' ? 'selected' : '' }}>Activos</option>
                            <option value="inactivos" {{ request('estado') == 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Precio (Sin IVA)</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                            <tr>
                                <td>{{ $producto->codigo ?? 'N/A' }}</td>
                                <td>{{ $producto->nombre }}</td>
                                <td>${{ number_format($producto->precio_sin_iva, 2) }}</td>
                                <td>
                                    @if($producto->es_servicio)
                                        <span class="badge bg-info">Servicio</span>
                                    @else
                                        <span class="badge {{ $producto->stock_actual <= $producto->stock_minimo ? 'bg-danger' : 'bg-success' }}">
                                            {{ $producto->stock_actual }} en stock
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($producto->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('inventario.edit', $producto->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('inventario.destroy', $producto->id) }}" method="POST" class="d-inline form-estado">
                                        @csrf
                                        @method('DELETE')
                                        @if($producto->activo)
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-cambiar-estado">Desactivar</button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-success btn-cambiar-estado">Activar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 d-flex justify-content-center">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnScan = document.getElementById('btn-scan-buscar');
        const readerDiv = document.getElementById('reader-buscar');
        const inputBuscar = document.getElementById('buscar_input');
        let html5QrcodeScanner;

        if(btnScan) {
            btnScan.addEventListener('click', function() {
                readerDiv.style.display = 'block';
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader-buscar", { fps: 10, qrbox: {width: 250, height: 100} }, false);

                html5QrcodeScanner.render(function(decodedText) {
                    // 1. Coloca el código escaneado en el buscador
                    inputBuscar.value = decodedText;
                    // 2. Apaga la cámara
                    html5QrcodeScanner.clear();
                    readerDiv.style.display = 'none';
                    // 3. ¡Magia! Envía el formulario automáticamente para filtrar la tabla
                    inputBuscar.closest('form').submit();
                }, function(error) {
                    // Ignorar errores continuos de búsqueda de enfoque
                });
            });
        }
    });
    // 3. Confirmación Inteligente para cambiar estado
        const botonesEstado = document.querySelectorAll('.btn-cambiar-estado');
        
        botonesEstado.forEach(boton => {
            boton.addEventListener('click', function() {
                const formulario = this.closest('.form-estado'); // Busca el formulario padre
                const accion = this.textContent.trim(); // "Activar" o "Desactivar"
                const colorBoton = accion === 'Desactivar' ? '#dc3545' : '#198754';

                Swal.fire({
                    title: `¿${accion} producto?`,
                    text: accion === 'Desactivar' ? "El producto no aparecerá en ventas, pero conservará su historial." : "El producto volverá a estar disponible para ventas.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: colorBoton,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formulario.submit();
                    }
                });
            });
        });
</script>
</body>
</html>