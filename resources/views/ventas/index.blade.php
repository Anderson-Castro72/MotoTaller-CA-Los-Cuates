@extends('layouts.app')

@section('title', 'Historial de Ventas')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold mb-0">📊 Historial de Ventas</h3>
        <a href="{{ route('ventas.directa') }}" class="btn btn-success fw-bold">
            🛒 Nueva Venta Directa
        </a>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted mb-1 fw-bold text-uppercase small">Ingresos de Hoy</p>
                    <h3 class="text-success fw-bold mb-0">${{ number_format($totalHoy, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <p class="text-muted mb-1 fw-bold text-uppercase small">Ingresos del Mes</p>
                    <h3 class="text-primary fw-bold mb-0">${{ number_format($totalMes, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>N° Documento</th>
                            <th>Tipo</th>
                            <th class="text-start">Cliente</th>
                            <th>Vehículo / Tipo Venta</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($ventas->count() > 0): ?>
                            <?php foreach($ventas as $venta): ?>
                                <tr>
                                    <td class="text-muted fw-bold">
                                        {{ $venta->created_at->format('d/m/Y h:i A') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">#{{ strtoupper(substr($venta->id, -8)) }}</span>
                                    </td>
                                    <td>
                                        @if($venta->tipo_documento == 'Ticket')
                                            <span class="badge bg-light text-dark border"><i class="fas fa-receipt"></i> Ticket</span>
                                        @elseif($venta->tipo_documento == 'FCF')
                                            <span class="badge bg-primary"><i class="fas fa-file-invoice"></i> FCF</span>
                                        @else
                                            <span class="badge bg-success"><i class="fas fa-file-invoice-dollar"></i> CCF</span>
                                        @endif
                                    </td>
                                    <td class="text-start fw-bold text-dark">
                                        {{ $venta->cliente->nombre }}
                                    </td>
                                    <td>
                                        @if($venta->orden_entrada_id && $venta->orden)
                                            <span class="badge bg-info text-dark">
                                                🏍️ {{ $venta->orden->motocicleta->placa }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Mostrador</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success fs-6">
                                        ${{ number_format($venta->total_pagar, 2) }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <!-- NUEVO: Botón que abre el Modal -->
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalDetalle{{ $venta->id }}" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                                 Ver
                                            </button>
                                            <a href="{{ route('ventas.imprimir', $venta->id) }}" class="btn btn-sm btn-outline-dark" title="Reimprimir Ticket">
                                                🖨️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-5 text-muted fs-5">
                                    No hay ventas registradas en el sistema todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-center pt-3">
            {{ $ventas->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- ZONA DE MODALES (Uno generado por registro)-->
<!-- ========================================== -->
<?php if($ventas->count() > 0): ?>
    <?php foreach($ventas as $venta): ?>
        <div class="modal fade" id="modalDetalle{{ $venta->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    
                    <div class="modal-header bg-dark text-white p-3">
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fas fa-receipt me-2"></i> Documento: {{ $venta->tipo_documento }}
                            <span class="badge bg-light text-dark ms-2">#{{ strtoupper(substr($venta->id, -8)) }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body p-4">
                        <div class="row mb-4 border-bottom pb-3">
                            <div class="col-sm-6">
                                <p class="text-muted mb-1 small">Datos del Cliente</p>
                                <h5 class="fw-bold mb-1">{{ $venta->cliente->nombre }}</h5>
                                <?php if($venta->cliente->telefono): ?><p class="mb-0 small"><i class="fas fa-phone"></i> {{ $venta->cliente->telefono }}</p><?php endif; ?>
                                <?php if($venta->cliente->dui): ?><p class="mb-0 small"><i class="fas fa-id-card"></i> {{ $venta->cliente->dui }}</p><?php endif; ?>
                            </div>
                            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                                <p class="text-muted mb-1 small">Información de la Venta</p>
                                <p class="mb-1"><strong>Fecha:</strong> {{ $venta->created_at->format('d/m/Y h:i A') }}</p>
                                <p class="mb-0"><strong>Tipo:</strong> 
                                    <?php if($venta->orden_entrada_id && $venta->orden): ?>
                                        Taller ({{ $venta->orden->motocicleta->placa }} - {{ $venta->orden->motocicleta->marca }})
                                    <?php else: ?>
                                        Venta Directa de Mostrador
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <h6 class="fw-bold text-muted mb-3">Artículos y Servicios Facturados:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start">Descripción</th>
                                        <th>Cant.</th>
                                        <th>Precio Unit.</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($venta->detalles as $detalle): ?>
                                        <tr>
                                            <td class="text-start fw-bold">
                                                {{ $detalle->producto->codigo }} - {{ $detalle->producto->nombre }}
                                            </td>
                                            <td>{{ $detalle->cantidad }}</td>
                                            <td>${{ number_format($detalle->precio_unitario_sin_iva + $detalle->monto_iva_unitario, 2) }}</td>
                                            <td class="fw-bold text-dark">
                                                ${{ number_format(($detalle->precio_unitario_sin_iva + $detalle->monto_iva_unitario) * $detalle->cantidad, 2) }}
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-7 offset-md-5">
                                <ul class="list-group list-group-flush text-end">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Subtotal:</span>
                                        <span class="fw-bold">${{ number_format($venta->subtotal, 2) }}</span>
                                    </li>
                                    
                                    <?php if($venta->descuento > 0): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light text-success border-success">
                                            <span class="fw-bold">Ahorro por Combos:</span>
                                            <span class="fw-bold">-${{ number_format($venta->descuento, 2) }}</span>
                                        </li>
                                    <?php endif; ?>

                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="text-muted">IVA (13%):</span>
                                        <span class="fw-bold">${{ number_format($venta->total_iva, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center mt-2 border-top-0">
                                        <h5 class="text-dark fw-bold mb-0">Total Pagado:</h5>
                                        <h5 class="text-success fw-bold mb-0">${{ number_format($venta->total_pagar, 2) }}</h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light justify-content-between p-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <a href="{{ route('ventas.imprimir', $venta->id) }}" class="btn btn-dark fw-bold">
                            🖨️ Reimprimir 
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
@endsection