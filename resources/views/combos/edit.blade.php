@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-warning">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0 fw-bold">✏️ Editar Combo: {{ $combo->nombre }}</h4>
        </div>
        <div class="card-body">
            
            <form action="{{ route('combos.update', $combo->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre del Combo</label>
                        <input type="text" name="nombre" class="form-control form-control-lg" value="{{ old('nombre', $combo->nombre) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-success">Precio Total del Combo ($)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white">$</span>
                            <input type="number" step="0.01" name="precio_total" class="form-control form-control-lg text-center fw-bold" value="{{ old('precio_total', $combo->precio_total) }}" required>
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
                            <!-- Filas generadas por servidor (Ingredientes Actuales) -->
                            @foreach($combo->productos as $prodCombo)
                                <tr>
                                    <td class="text-start">
                                        <select name="productos[{{ $loop->index }}][id]" class="form-select select2-combo" required>
                                            <option value="">-- Selecciona un artículo --</option>
                                            @foreach($productos as $p)
                                                <option value="{{ $p->id }}" {{ $p->id == $prodCombo->id ? 'selected' : '' }}>
                                                    {{ $p->codigo }} - {{ $p->nombre }} (Precio Normal: ${{ number_format($p->precio, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="productos[{{ $loop->index }}][cantidad]" class="form-control text-center" value="{{ $prodCombo->pivot->cantidad }}" min="1" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger" onclick="quitarFila(this)">❌</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-primary mb-4" onclick="agregarFila()">
                    ➕ Agregar Artículo
                </button>

                <hr>
                
                <div class="text-end">
                    <a href="{{ route('combos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-warning btn-lg fw-bold">Actualizar Combo</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- TEMPLATE OCULTO PARA JAVASCRIPT -->
<!-- Al usar un template nativo, evitamos que Blade se corrompa intentando leer código dentro de comillas invertidas de JavaScript -->
<template id="fila-template">
    <tr>
        <td class="text-start">
            <select name="productos[__INDEX__][id]" class="form-select select2-combo-new" required>
                <option value="">-- Selecciona un artículo --</option>
                @foreach($productos as $p)
                    <option value="{{ $p->id }}">{{ $p->codigo }} - {{ $p->nombre }} (Precio Normal: ${{ number_format($p->precio, 2) }})</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="productos[__INDEX__][cantidad]" class="form-control text-center" value="1" min="1" required>
        </td>
        <td>
            <button type="button" class="btn btn-danger" onclick="quitarFila(this)">❌</button>
        </td>
    </tr>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Inicia el contador de filas desde la cantidad de productos que ya tiene el combo
    let indiceFila = {{ $combo->productos->count() }}; 

    function agregarFila() {
        // Clonamos el código HTML del template oculto
        let template = document.getElementById('fila-template').innerHTML;
        // Reemplazamos la palabra clave __INDEX__ por el número actual de la fila
        let html = template.replace(/__INDEX__/g, indiceFila);
        
        $('#tbodyIngredientes').append(html);
        
        // Inicializamos Select2 para la nueva fila
        $('.select2-combo-new').removeClass('select2-combo-new').addClass('select2-combo').select2({ width: '100%' });
        
        indiceFila++;
    }

    function quitarFila(boton) {
        $(boton).closest('tr').remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar los Select2 de las filas que vienen de la base de datos
        $('.select2-combo').select2({ width: '100%' });
        
        // Si el combo por alguna razón no tiene productos, dibuja la primera fila
        if(indiceFila === 0) {
            agregarFila();
        }
    });
</script>
@endsection