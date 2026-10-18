@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold mb-0">📦 Gestión de Combos / Paquetes</h3>
        <a href="{{ route('combos.create') }}" class="btn btn-success shadow-sm">
            ➕ Nuevo Combo
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover text-center align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start ps-4">Nombre del Combo</th>
                            <th>Precio Total</th>
                            <th>Artículos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($combos as $combo)
                            <!-- Fila Principal -->
                            <tr>
                                <td class="fw-bold text-start ps-4">{{ $combo->nombre }}</td>
                                <td class="text-success fw-bold fs-5">${{ number_format($combo->precio_total, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ $combo->productos->count() }} ítems</span></td>
                                <td>
                                    @if($combo->activo)
                                        <span class="badge bg-success px-3 py-2">Activo</span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Botón Ver Detalles (Despliega la fila inferior) -->
                                    <button class="btn btn-sm btn-info text-white me-1" type="button" data-bs-toggle="collapse" data-bs-target="#detalles-{{ $combo->id }}" aria-expanded="false" title="Ver contenido">
                                        Ver
                                    </button>
                                    
                                    <!-- Botón Editar -->
                                    <a href="{{ route('combos.edit', $combo->id) }}" class="btn btn-sm btn-warning me-1" title="Editar">
                                        Edit
                                    </a>

                                    <!-- Formulario Activar/Desactivar -->
                                    <form action="{{ route('combos.toggle', $combo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $combo->activo ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $combo->activo ? 'Desactivar' : 'Activar' }}">
                                            {{ $combo->activo ? '🚫' : '✅' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Fila Desplegable (Detalles del Combo) -->
                            <tr>
                                <td colspan="5" class="p-0 border-0">
                                    <div class="collapse" id="detalles-{{ $combo->id }}">
                                        <div class="bg-light p-3 border-bottom text-start px-5">
                                            <h6 class="fw-bold text-muted mb-2">Contenido de este paquete:</h6>
                                            <ul class="mb-0">
                                                @foreach($combo->productos as $producto)
                                                    <li>
                                                        <strong>{{ $producto->pivot->cantidad }}x</strong> 
                                                        {{ $producto->nombre }} 
                                                        <span class="text-muted ms-2">( ${{ number_format($producto->precio, 2) }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted py-5">
                                    <h5>No hay combos registrados.</h5>
                                    <p>Crea paquetes de servicios y repuestos para ofrecer precios especiales a tus clientes.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection