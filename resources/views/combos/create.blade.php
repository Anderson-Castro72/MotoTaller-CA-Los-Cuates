@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-primary">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 fw-bold">🛠️ Armar Nuevo Combo</h4>
        </div>
        <div class="card-body">
            
            <form action="{{ route('combos.store') }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre del Combo (Ej: Mantenimiento Premium)</label>
                        <input type="text" name="nombre" class="form-control form-control-lg" required autofocus>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-success">Precio Total del Combo ($)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white">$</span>
                            <input type="number" step="0.01" name="precio_total" class="form-control form-control-lg text-center fw-bold" required placeholder="0.00">
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold text-secondary border-bottom pb-2">Contenido del Combo</h5>
                
                <div class="table-responsive mb-3">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 70%;">Producto / Servicio</th>
                                <th style="width: 20%;">Cantidad</th>
                                <th style="width: 10%;">Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyIngredientes">
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-primary mb-4" onclick="agregarFila()">
                    ➕ Agregar Artículo
                </button>

                <hr>
                
                <div class="text-end">
                    <a href="{{ route('combos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success btn-lg fw-bold">Guardar Combo</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let indiceFila = 0; 

    function agregarFila() {
        let html = `
            <tr>
                <td class="text-start">
                    <select name="productos[${indiceFila}][id]" class="form-select select2-combo" required>
                        <option value="">-- Selecciona un artículo --</option>
                        @foreach($productos as $p)
                            <option value="{{ $p->id }}" data-precio="{{ $p->precio }}">
                                {{ $p->codigo }} - {{ $p->nombre }} (Precio Normal: ${{ number_format($p->precio, 2) }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="productos[${indiceFila}][cantidad]" class="form-control text-center" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger" onclick="quitarFila(this)">❌</button>
                </td>
            </tr>
        `;
        
        $('#tbodyIngredientes').append(html);
        $('.select2-combo').select2({ width: '100%' });
        indiceFila++;
    }

    function quitarFila(boton) {
        $(boton).closest('tr').remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        agregarFila();
    });
</script>
@endsection