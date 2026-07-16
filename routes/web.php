<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/inventario/verificar-codigo/{codigo}', [ProductoController::class, 'verificarCodigo']);
Route::resource('inventario', ProductoController::class);