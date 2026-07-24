<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RecepcionController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/inventario/verificar-codigo/{codigo}', [ProductoController::class, 'verificarCodigo']);
Route::resource('inventario', ProductoController::class);

// Rutas para el Módulo de Recepción (Todo en Uno)
Route::get('/recepcion', [RecepcionController::class, 'index'])->name('recepcion.index');
Route::get('/recepcion/create', [RecepcionController::class, 'create'])->name('recepcion.create');
Route::post('/recepcion/guardar', [RecepcionController::class, 'store'])->name('recepcion.store');

// Rutas invisibles para AJAX (Buscadores)
Route::get('/recepcion/verificar-cliente/{dui}', [RecepcionController::class, 'verificarCliente']);
Route::get('/recepcion/verificar-moto/{placa}', [RecepcionController::class, 'verificarMoto']);