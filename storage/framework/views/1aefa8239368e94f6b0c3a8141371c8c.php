<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('cashier.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('cashier.dashboard')); ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('cashier.tables') ? 'active' : ''); ?>" href="<?php echo e(route('cashier.tables')); ?>">
            <i class="fas fa-table-cells-large"></i> Mesas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('cashier.cash-sessions*') ? 'active' : ''); ?>" href="<?php echo e(route('cashier.cash-sessions')); ?>">
            <i class="fas fa-cash-register"></i> Caja y Arqueo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(request()->routeIs('cashier.sales') ? 'active' : ''); ?>" href="<?php echo e(route('cashier.sales')); ?>">
            <i class="fas fa-chart-line"></i> Ventas
        </a>
    </li>
</ul>
<?php /**PATH /var/www/html/cevicheria-pos/resources/views/cashier/_sidebar.blade.php ENDPATH**/ ?>