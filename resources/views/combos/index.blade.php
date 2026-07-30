@extends('layouts.app') @section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold mb-0">📦 Gestión de Combos/Paquetes</h3>
        <a href="{{ route('combos.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Crear Nuevo Combo
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped text-center mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre del Combo</th>
                        <th>Cant. de Artículos</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combos as $combo)
                        <tr>
                            <td class="fw-bold">{{ $combo->nombre }}</td>
                            <td>{{ $combo->productos->count() }} elementos</td>
                            <td>
                                @if($combo->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted py-4">No hay combos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection