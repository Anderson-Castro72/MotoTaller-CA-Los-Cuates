@extends('layouts.app')

@section('title', 'Dashboard - MotoTaller')

@section('content')
<div class="container-fluid py-3">
    <h3 class="fw-bold text-dark mb-4">📊 Panel de Control</h3>

    <!-- Tarjetas de Resumen -->
    <div class="row mb-4">
        <!-- Ventas del Día -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="fas fa-cash-register"></i></div>
                    <div>
                        <h6 class="mb-0 text-uppercase fw-bold">Ventas del Día</h6>
                        <h3 class="mb-0 fw-bold">${{ number_format($ventasHoy ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motos en Taller -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="fs-1 me-3"><i class="fas fa-motorcycle"></i></div>
                    <div>
                        <h6 class="mb-0 text-uppercase fw-bold">En Reparación</h6>
                        <h3 class="mb-0 fw-bold">{{ $motosEnTaller }} <small class="fs-6 fw-normal">motos</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 bg-dark text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <a href="{{ route('ventas.directa') }}" class="btn btn-outline-light mb-2 fw-bold"><i class="fas fa-shopping-cart"></i> Venta Rápida</a>
                    <a href="{{ route('recepcion.create') }}" class="btn btn-light fw-bold text-dark"><i class="fas fa-plus"></i> Nueva Recepción</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y Tablas -->
    <div class="row">
        <!-- Tabla de Stock Mínimo -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-danger h-100">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i> Alertas de Stock Mínimo
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosBajoStock as $producto)
                                <tr>
                                    <td class="align-middle fw-bold">{{ $producto->codigo }}</td>
                                    <td class="align-middle text-start">{{ $producto->nombre }}</td>
                                    <td class="align-middle">
                                        <span class="badge bg-danger fs-6">{{ $producto->stock_actual }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-muted">Todo el inventario está en niveles óptimos.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Espacio reservado para métricas futuras (ej. rendimiento mecánicos) -->
        <div class="col-md-6 mb-4">
             <div class="card shadow-sm border-secondary h-100">
                <div class="card-header bg-secondary text-white fw-bold">
                    <i class="fas fa-chart-pie me-2"></i> Reportes Adicionales
                </div>
                <div class="card-body d-flex align-items-center justify-content-center text-muted">
                    <p class="mb-0">Próximamente: Gráficas de productos más vendidos</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection