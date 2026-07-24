<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepciones - MotoTaller</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">Órdenes de Taller</h2>
            <a href="{{ route('recepcion.create') }}" class="btn btn-primary fw-bold">
                + Nueva Recepción
            </a>
        </div>

 <div class="mb-3 d-flex justify-content-end">
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados" aria-expanded="false" aria-controls="filtrosAvanzados">
                🔍 Filtros de Búsqueda
            </button>
        </div>

        <div class="collapse {{ request()->anyFilled(['placa', 'cliente', 'estado', 'fecha']) ? 'show' : '' }}" id="filtrosAvanzados">
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-body pb-0">
                    <form action="{{ route('recepcion.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label class="form-label small fw-bold text-muted">Placa</label>
                                <input type="text" name="placa" class="form-control form-control-sm" placeholder="Ej. M12345" value="{{ request('placa') }}">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label small fw-bold text-muted">Cliente</label>
                                <input type="text" name="cliente" class="form-control form-control-sm" placeholder="Buscar..." value="{{ request('cliente') }}">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label small fw-bold text-muted">Estado</label>
                                <select name="estado" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="En Reparación" {{ request('estado') == 'En Reparación' ? 'selected' : '' }}>En Reparación</option>
                                    <option value="Listo" {{ request('estado') == 'Listo' ? 'selected' : '' }}>Listo</option>
                                    <option value="Facturado" {{ request('estado') == 'Facturado' ? 'selected' : '' }}>Facturado</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label small fw-bold text-muted">Desde</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ request('fecha_inicio') }}" max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label small fw-bold text-muted">Hasta</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ request('fecha_fin') }}" max="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100" title="Buscar">🔍</button>
                                <a href="{{ route('recepcion.index') }}" class="btn btn-sm btn-light border ms-1" title="Limpiar">✖️</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Motocicleta</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordenes as $orden)
                                <tr>
                                    <td>
                                        {{ $orden->created_at->format('d/m/Y') }}<br>
                                        <small class="text-muted">{{ $orden->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-uppercase">{{ $orden->motocicleta->placa }}</span><br>
                                        <small class="text-muted">{{ $orden->motocicleta->marca }} {{ $orden->motocicleta->modelo }}</small>
                                    </td>
                                    <td>
                                        {{ $orden->cliente->nombre }}<br>
                                        
                                    </td>
                                    <td>
                                        @php
                                            $badgeColor = match($orden->estado) {
                                                'Pendiente' => 'bg-warning text-dark',
                                                'En Reparación' => 'bg-primary',
                                                'Listo' => 'bg-success',
                                                'Facturado' => 'bg-secondary',
                                                default => 'bg-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeColor }} fs-6">{{ $orden->estado }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info text-white" title="Ver Detalles">👁️</button>
                                        <button class="btn btn-sm btn-success" title="Facturar / POS">💲</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No hay motocicletas en el taller actualmente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                Mostrando del <span class="fw-bold">{{ $ordenes->firstItem() ?? 0 }}</span> al <span class="fw-bold">{{ $ordenes->lastItem() ?? 0 }}</span> de <span class="fw-bold text-primary">{{ $ordenes->total() }}</span> registros
            </div>
            
            <div>
                {{ $ordenes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            const hoy = "{{ date('Y-m-d') }}"; // Fecha máxima permitida (hoy)

            // Cuando cambia la Fecha de Inicio
            fechaInicio.addEventListener('change', function() {
                if(this.value) {
                    // La fecha de fin no puede ser menor a la de inicio
                    fechaFin.min = this.value;
                } else {
                    fechaFin.min = ""; // Limpiar restricción si borran la fecha
                }
            });

            // Cuando cambia la Fecha de Fin
            fechaFin.addEventListener('change', function() {
                if(this.value) {
                    // La fecha de inicio no puede ser mayor a la fecha de fin elegida
                    fechaInicio.max = this.value;
                } else {
                    // Si borran la fecha fin, restablecer al máximo de hoy
                    fechaInicio.max = hoy; 
                }
            });
        });
    </script>
</html>