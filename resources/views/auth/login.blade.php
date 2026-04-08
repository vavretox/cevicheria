<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2c3e50">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    <title>Login - Cevichería Los Pepes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .login-image {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), 
                        url('https://images.unsplash.com/photo-1559797432-7a6609bb74a6?w=800') center/cover;
            padding: 60px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-image h2 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .login-image p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .login-logo {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }
        .login-logo img {
            width: 200px;
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }

        .login-form {
            padding: 60px;
        }

        .login-form h3 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            color: white;
            width: 100%;
            transition: transform 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row login-container">
            <div class="col-md-6 login-image d-none d-md-flex">
                <div>
                    <h2><i class="fas fa-utensils me-3"></i>Cevichería Los Pepes</h2>
                    <p>Sistema de punto de venta profesional para restaurantes</p>
                    <div class="mt-4">
                        <i class="fas fa-check me-2"></i> Gestión de pedidos<br>
                        <i class="fas fa-check me-2"></i> Control de inventario<br>
                        <i class="fas fa-check me-2"></i> Reportes de ventas<br>
                        <i class="fas fa-check me-2"></i> Multi-usuario
                    </div>
                    <div class="login-logo">
                        <img src="{{ asset('images/logo-los-pepes.jpeg') }}" alt="Cevichería Los Pepes">
                    </div>
                </div>
            </div>
            <div class="col-md-6 login-form">
                <h3>Iniciar Sesión</h3>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" required autofocus 
                                   placeholder="correo@ejemplo.com" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                    </button>
                </form>

                <div class="mt-4 text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Usuarios de prueba disponibles en el README
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}');
            });
        }
    </script>
</body>
</html>
