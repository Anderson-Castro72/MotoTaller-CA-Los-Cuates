@extends('layouts.app')

@section('title', 'Iniciar Sesión - MotoTaller')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            
            <!-- Contenedor del Logo -->
            <div class="text-center mb-4">
                <!-- Asegúrate de colocar tu logo en la carpeta public/img/logo.png -->
                <img src="{{ asset('img/logo.png') }}" alt="MotoTaller Logo" style="max-width: 180px;" class="mb-3">
                <p class="text-muted">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Tarjeta de Login -->
            <div class="card shadow border-0 rounded-lg">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold text-secondary">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input id="email" type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="">
                            </div>
                            @error('email')
                                <span class="text-danger small mt-1 d-block">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold text-secondary">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input id="password" type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="">
                            </div>
                            @error('password')
                                <span class="text-danger small mt-1 d-block">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Recordarme -->
                        <div class="mb-4 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="remember">
                                Mantener sesión iniciada
                            </label>
                        </div>

                        <!-- Botón Ingresar -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                Ingresar <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection