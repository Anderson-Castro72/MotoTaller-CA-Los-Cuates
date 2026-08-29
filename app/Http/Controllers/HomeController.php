<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HomeController extends Controller implements HasMiddleware
{
    /**
     * Aplicar seguridad al controlador (Sintaxis Laravel 11)
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    /**
     * Muestra el dashboard de la aplicación.
     */
    public function index()
    {
        return view('home');
        
        // Nota: Si prefieres que al iniciar sesión no vean la pantalla 
        // básica que dice "You are logged in", sino que el sistema los mande 
        // directo a trabajar, comenta la línea de arriba y usa esta:
        // return redirect('/inventario');
    }
}