<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="theme-color" content="#2c3e50">
    <link rel="manifest" href="<?php echo e(asset('manifest.json')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/icon-192.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Cevichería Los Pepes'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sidebar {
            min-height: calc(100vh - 56px);
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar,
        .main-content {
            transition: all 0.25s ease;
        }
        .sidebar-overlay {
            display: none;
        }
        .sidebar-logo {
            display: flex;
            justify-content: center;
            padding: 20px 10px 10px;
            border-top: 1px solid #eef0f2;
        }
        .sidebar-logo img {
            width: 150px;
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .sidebar .nav-link {
            color: #495057;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #e9ecef;
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #34495e);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
        }

        .btn-primary:hover {
            background: #34495e;
        }

        .btn-danger {
            background: var(--secondary-color);
            border: none;
        }

        .btn-success {
            background: var(--success-color);
            border: none;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .table {
            background: white;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }

        @media (min-width: 768px) {
            #appShell.sidebar-collapsed .sidebar {
                flex: 0 0 80px;
                max-width: 80px;
            }

            #appShell.sidebar-collapsed .main-content {
                flex: 0 0 calc(100% - 80px);
                max-width: calc(100% - 80px);
            }

            #appShell.sidebar-collapsed .sidebar .nav-link {
                font-size: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                padding-left: 10px;
                padding-right: 10px;
            }

            #appShell.sidebar-collapsed .sidebar .nav-link i {
                font-size: 1rem;
                margin-right: 0;
            }

            #appShell.sidebar-collapsed .sidebar-logo {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: 0;
                width: 280px;
                max-width: 85vw;
                height: calc(100vh - 56px);
                z-index: 1045;
                transform: translateX(-100%);
                overflow-y: auto;
            }

            .main-content {
                flex: 0 0 100%;
                max-width: 100%;
            }

            #appShell.mobile-sidebar-open .sidebar {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 56px;
                left: 0;
                width: 100vw;
                height: calc(100vh - 56px);
                background: rgba(0, 0, 0, 0.35);
                z-index: 1040;
            }

            #appShell.mobile-sidebar-open .sidebar-overlay {
                display: block;
            }
        }
    </style>

    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>
                Cevichería Los Pepes
            </a>
            <button class="btn btn-outline-light btn-sm me-2 no-print" id="sidebarToggle" type="button" title="Mostrar/Ocultar menú">
                <i class="fas fa-bars"></i>
            </button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo e(Auth::user()->name); ?>

                            <span class="badge bg-light text-dark ms-2"><?php echo e(ucfirst(Auth::user()->role)); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?php echo e(route('logout')); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row" id="appShell">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar no-print">
                <div class="position-sticky pt-3 d-flex flex-column h-100">
                    <?php echo $__env->yieldContent('sidebar'); ?>
                    <div class="sidebar-logo mt-auto">
                        <img src="<?php echo e(asset('images/logo-los-pepes.jpeg')); ?>" alt="Cevichería Los Pepes">
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4 main-content">
                <div class="py-4">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
            <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        // CSRF Token para AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo e(asset('sw.js')); ?>');
            });
        }
    </script>
    <script>
        (() => {
            const shell = document.getElementById('appShell');
            const toggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleIcon = toggle ? toggle.querySelector('i') : null;
            const storageKey = 'sidebar_collapsed';

            if (!shell || !toggle) return;

            const syncToggleIcon = () => {
                if (!toggleIcon) return;
                const isMobileOpen = shell.classList.contains('mobile-sidebar-open');
                toggleIcon.classList.toggle('fa-bars', !isMobileOpen);
                toggleIcon.classList.toggle('fa-xmark', isMobileOpen);
            };

            if (window.innerWidth >= 768 && localStorage.getItem(storageKey) === '1') {
                shell.classList.add('sidebar-collapsed');
            }
            syncToggleIcon();

            toggle.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    shell.classList.toggle('mobile-sidebar-open');
                    syncToggleIcon();
                    return;
                }

                shell.classList.toggle('sidebar-collapsed');
                localStorage.setItem(storageKey, shell.classList.contains('sidebar-collapsed') ? '1' : '0');
            });

            if (overlay) {
                overlay.addEventListener('click', () => {
                    shell.classList.remove('mobile-sidebar-open');
                    syncToggleIcon();
                });
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    shell.classList.remove('mobile-sidebar-open');
                }
                syncToggleIcon();
            });
        })();
    </script>
</body>
</html>










<?php /**PATH /var/www/html/cevicheria-pos/resources/views/layouts/app.blade.php ENDPATH**/ ?>