<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.products*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.products')); ?>">
            <i class="fas fa-box"></i> Productos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.warehouse.beverages*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.warehouse.beverages')); ?>">
            <i class="fas fa-warehouse"></i> Almacén Bebidas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.categories*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.categories')); ?>">
            <i class="fas fa-tags"></i> Categorías
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.tables*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.tables')); ?>">
            <i class="fas fa-table-cells-large"></i> Mesas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.users*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users')); ?>">
            <i class="fas fa-users"></i> Usuarios
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.cash-sessions') ? 'active' : ''); ?>" href="<?php echo e(route('admin.cash-sessions')); ?>">
            <i class="fas fa-cash-register"></i> Caja y Arqueo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('admin.reports*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports')); ?>">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
    </li>
</ul>
<?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/_sidebar.blade.php ENDPATH**/ ?>