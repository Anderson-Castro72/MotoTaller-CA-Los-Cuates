@extends('layouts.app')

@section('title', 'Recepciones - MotoTaller')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">Órdenes de Taller</h2>
            <a href="{{ route('recepcion.create') }}" class="btn btn-primary fw-bold">
                <i class="fas fa-plus"></i> Nuevo
            </a>
        </div>

        <div class="mb-3 d-flex justify-content-end">
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados" aria-expanded="false" aria-controls="filtrosAvanzados">
                <i class="fas fa-search"></i> Filtros
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
                                        <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetalles{{ $orden->id }}" title="Ver Detalles">👁️</button>
                                        <a href="{{ route('ventas.pos', $orden->id) }}" class="btn btn-sm btn-success" title="Facturar / POS">💲</a>
                                    </td>
                                </tr>
                                
                                <div class="modal fade text-start" id="modalDetalles{{ $orden->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $orden->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-info text-white">
                                                <h5 class="modal-title fw-bold" id="modalLabel{{ $orden->id }}">📋 Detalles de Orden #{{ str_pad($orden->id, 5, '0', STR_PAD_LEFT) }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6 mb-2">
                                                        <h6 class="fw-bold text-primary border-bottom pb-1">🏍️ Motocicleta</h6>
                                                        <p class="mb-1"><strong>Placa:</strong> <span class="text-uppercase">{{ $orden->motocicleta->placa }}</span></p>
                                                        <p class="mb-1"><strong>Vehículo:</strong> {{ $orden->motocicleta->marca }} {{ $orden->motocicleta->modelo }}</p>
                                                        <p class="mb-1"><strong>Kilometraje:</strong> {{ number_format($orden->kilometraje_entrada) }} km</p>
                                                        <p class="mb-0"><strong>Combustible:</strong> {{ $orden->nivel_combustible }}</p>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <h6 class="fw-bold text-primary border-bottom pb-1">👤 Cliente (Responsable)</h6>
                                                        <p class="mb-1"><strong>Nombre:</strong> {{ $orden->cliente->nombre }}</p>
                                                        <p class="mb-1"><strong>DUI:</strong> {{ $orden->cliente->dui ?? 'N/A' }}</p>
                                                        <p class="mb-0"><strong>Teléfono:</strong> {{ $orden->cliente->telefono ?? 'N/A' }}</p>
                                                    </div>
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <h6 class="fw-bold text-primary border-bottom pb-1">🛠️ Reporte de Ingreso</h6>
                                                        <p class="mb-1"><strong>Falla Reportada:</strong><br> {{ $orden->falla_reportada }}</p>
                                                        <p class="mb-0"><strong>Observaciones físicas:</strong><br> {{ $orden->observaciones ?? 'Ninguna observación extra.' }}</p>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="fw-bold text-primary border-bottom pb-1 mt-3">📸 Evidencia Fotográfica</h6>
                                                <div class="row text-center mt-2">
                                                    @for($i = 1; $i <= 4; $i++)
                                                        @php $foto = 'foto_' . $i; @endphp
                                                        <div class="col-md-3 col-6 mb-2">
                                                            @if($orden->$foto)
                                                                <a href="{{ asset('storage/' . $orden->$foto) }}" target="_blank">
                                                                    <img src="{{ asset('storage/' . $orden->$foto) }}" class="img-thumbnail shadow-sm" alt="Foto {{ $i }}" style="height: 120px; width: 100%; object-fit: cover; border-radius: 8px;">
                                                                </a>
                                                            @else
                                                                <div class="border rounded bg-light d-flex flex-column align-items-center justify-content-center text-muted" style="height: 120px; border-radius: 8px;">
                                                                    <span class="fs-4">📷</span>
                                                                    <small>Sin foto</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar Detalles</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
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
@endsection

@push('scripts')
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

        @if(session('success'))
            @if(session('tipo_documento') == 'Ticket')
                // 1. Es TICKET INTERNO: Abre la ventana de impresión automáticamente
                window.location.href = "{{ url('/ventas/imprimir-red') }}/{{ session('ticket_id') }}";
                
                // 2. Muestra un mensaje breve que se cierra solo en 2 segundos (sin molestar al cajero)
                Swal.fire({
                    icon: 'success',
                    title: '¡Ticket Generado!',
                    text: '{{ session('success') }}',
                    timer: 2000,
                    showConfirmButton: false
                });
            @else
                // Es FCF o CCF: Solo muestra la alerta de éxito tradicional
                Swal.fire({
                    icon: 'success',
                    title: '¡Cobro Procesado!',
                    text: 'Documento {{ session('tipo_documento') }} registrado. {{ session('success') }}',
                    confirmButtonColor: '#198754'
                });
            @endif
        @endif
    });
</script>
@endpush