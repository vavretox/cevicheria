?

<?php $__env->startSection('title', 'Reporte de Ventas'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('cashier._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Reporte de Ventas</h2>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('cashier.sales')); ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="date_from" class="form-control" 
                       value="<?php echo e(request('date_from')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="date_to" class="form-control" 
                       value="<?php echo e(request('date_to')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="<?php echo e(route('cashier.sales')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Limpiar
                    </a>
                    <a href="<?php echo e(route('cashier.sales.print', ['type' => 'day', 'date_from' => request('date_from'), 'date_to' => request('date_to')])); ?>" 
                       class="btn btn-outline-dark" target="_blank" rel="noopener">
                        <i class="fas fa-print me-2"></i>Imprimir Día
                    </a>
                    <a href="<?php echo e(route('cashier.sales.print', ['type' => 'month', 'date_from' => request('date_from'), 'date_to' => request('date_to')])); ?>" 
                       class="btn btn-outline-dark" target="_blank" rel="noopener">
                        <i class="fas fa-print me-2"></i>Imprimir Mes
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Ventas</h6>
                <h2 class="text-success mb-0">Bs. <?php echo e(number_format($totalSales, 2)); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Pedidos Completados</h6>
                <h2 class="text-primary mb-0"><?php echo e($orders->total()); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Ventas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Pago</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong>#<?php echo e($order->display_number); ?></strong></td>
                        <td><?php echo e($order->completed_at->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($order->table_number); ?></td>
                        <td><?php echo e($order->user->name); ?></td>
                        <td><?php echo e($order->cashier ? $order->cashier->name : '-'); ?></td>
                        <td><?php echo e($order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR')); ?></td>
                        <td><strong class="text-success">Bs. <?php echo e(number_format($order->total, 2)); ?></strong></td>
                        <td>
                            <a href="<?php echo e(route('cashier.print-receipt', $order->id)); ?>" 
                               class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-print"></i>
                            </a>
                            <form action="<?php echo e(route('cashier.revert-order', $order->id)); ?>" method="POST" class="d-inline revert-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="reason" value="">
                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                        onclick="return confirmRevert(this)">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No hay ventas registradas en este período</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($orders->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>

<?php if(!empty($audits) && $audits->count() > 0): ?>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Auditoría Reciente</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Pedido</th>
                        <th>Acción</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $audits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($audit->created_at->format('d/m/Y H:i')); ?></td>
                        <td>#<?php echo e($audit->order_id); ?></td>
                        <td><?php echo e(ucfirst($audit->action)); ?></td>
                        <td><?php echo e($audit->user ? $audit->user->name : '-'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function confirmRevert(button) {
    const reason = prompt('Motivo de la reversión:');
    if (!reason) {
        return false;
    }
    const form = button.closest('form');
    form.querySelector('input[name=\"reason\"]').value = reason;
    return confirm('¿Revertir la venta a pendiente?');
}
</script>
<?php $__env->stopSection(); ?>







<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/cashier/sales.blade.php ENDPATH**/ ?>