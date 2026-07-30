<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'MotoTaller')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* =========================================
           DISEÑO DEL MENÚ LATERAL (SIDEBAR)
           ========================================= */
        body {
            height: 100vh; /* Fija la altura exactamente al 100% de la pantalla */
            overflow: hidden; /* Elimina el scroll global de la ventana */
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa; 
        }
        
        /* Contenedor que agrupa el menú y el contenido */
        .wrapper {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Estilos del Sidebar */
        .sidebar {
            width: 260px;
            background-color: #212529; /* bg-dark */
            color: white;
            transition: margin 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;   
        }

        /* Clase para ocultar el sidebar en PC */
        .sidebar.oculto {
            margin-left: -260px;
        }

        /* Estilos de los enlaces del Sidebar */
        .nav-link-side {
            color: #adb5bd;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            transition: all 0.2s;
        }
        .nav-link-side:hover, .nav-link-side.active {
            color: #fff;
            background-color: #343a40;
            border-left: 4px solid #198754; /* Detalle verde al seleccionar */
        }

        /* Área de contenido a la derecha */
        .content-area {
            flex: 1;
            overflow-y: auto;
            width: 100%;
        }

        /* =========================================
           COMPORTAMIENTO EN TELÉFONOS (MÓVILES)
           ========================================= */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                height: 100%;
                margin-left: -260px; /* Oculto por defecto en móvil */
                z-index: 1050;
            }
            .sidebar.mostrar {
                margin-left: 0; /* Mostrar en móvil */
            }

            /* Fondo oscuro semi-transparente para móvil */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .sidebar-overlay.mostrar {
                display: block;
            }
        }
    </style>

    @stack('css')
</head>
<body>

    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary shadow-sm">
        <div class="container-fluid">
            <button class="btn btn-dark text-white me-2 border-0" id="btnToggleMenu" title="Ocultar/Mostrar Menú">
                <i class="fas fa-bars fs-4"></i>
            </button>
            
            <a class="navbar-brand fw-bold me-auto" href="/">🏍️ MotoTaller</a>
            
            <div class="text-white">
                <i class="fas fa-user-circle fs-4"></i>
            </div>
        </div>
    </nav>

    <div class="wrapper position-relative">
        
        <div class="sidebar-overlay" id="overlayMenu"></div>

        <aside class="sidebar py-3" id="menuLateral">
            <div class="text-muted small fw-bold px-4 mb-2 text-uppercase">Opciones</div>
            
            <a href="{{ route('recepcion.index') }}" class="nav-link-side">
                <i class="fas fa-clipboard-list me-3"></i> Órdenes de Taller
            </a>
            
            <a href="#" class="nav-link-side text-success fw-bold"> <i class="fas fa-shopping-cart me-3"></i> Punto de Venta
            </a>
            <a href="{{ route('inventario.index') }}" class="nav-link-side">
                <i class="fas fa-boxes me-3"></i> Inventario (Repuestos)
            </a>
            <a href="{{ route('combos.index') }}" class="nav-link-side">
                <i class="fas fa-box me-3"></i> Combos y Paquetes
            </a>
            
            <hr class="text-secondary mx-3">
            <a href="#" class="nav-link-side">
                <i class="fas fa-chart-line me-3"></i> Reportes
            </a>
        </aside>

        <main class="content-area p-3 p-md-4">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnToggle = document.getElementById('btnToggleMenu');
            const sidebar = document.getElementById('menuLateral');
            const overlay = document.getElementById('overlayMenu');

            // Función al hacer clic en el botón hamburguesa
            btnToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // Modo Teléfono
                    sidebar.classList.toggle('mostrar');
                    overlay.classList.toggle('mostrar');
                } else {
                    // Modo Computadora
                    sidebar.classList.toggle('oculto');
                }
            });

            // Función para cerrar el menú si se hace clic afuera (en modo teléfono)
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mostrar');
                this.classList.remove('mostrar');
            });
        });
    </script>

    @stack('scripts')

</body>
</html>