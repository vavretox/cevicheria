<?php $__env->startSection('title', 'Caja y Arqueo'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2><i class="fas fa-cash-register me-2"></i>Caja y Arqueo</h2>
            <p class="text-muted mb-0">Supervisa aperturas, cierres y diferencias de caja por cajero.</p>
        </div>
        <a href="<?php echo e(route('admin.cash-sessions.print', request()->query())); ?>" class="btn btn-outline-dark" target="_blank" rel="noopener">
            <i class="fas fa-print me-2"></i>Imprimir
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cajas abiertas</div>
                <div class="fs-3 fw-bold"><?php echo e($summary['open_count']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cajas cerradas</div>
                <div class="fs-3 fw-bold"><?php echo e($summary['closed_count']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Ventas registradas</div>
                <div class="fs-3 fw-bold">Bs. <?php echo e(number_format($summary['sales_total'], 2)); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.cash-sessions')); ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Cajero</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    <?php $__currentLoopData = $cashiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cashier->id); ?>" <?php echo e((string) request('user_id') === (string) $cashier->id ? 'selected' : ''); ?>>
                            <?php echo e($cashier->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="open" <?php echo e(request('status') === 'open' ? 'selected' : ''); ?>>Abierta</option>
                    <option value="closed" <?php echo e(request('status') === 'closed' ? 'selected' : ''); ?>>Cerrada</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
                <a href="<?php echo e(route('admin.cash-sessions')); ?>" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Cajero</th>
                        <th>Estado</th>
                        <th>Apertura</th>
                        <th>Ventas</th>
                        <th>Esperado</th>
                        <th>Contado</th>
                        <th>Diferencia</th>
                        <th>Fechas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($session->cashier?->name ?? '-'); ?></td>
                        <td>
                            <span class="badge <?php echo e($session->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                <?php echo e($session->status === 'open' ? 'Abierta' : 'Cerrada'); ?>

                            </span>
                        </td>
                        <td>Bs. <?php echo e(number_format($session->opening_amount, 2)); ?></td>
                        <td>Bs. <?php echo e(number_format($session->sales_total, 2)); ?></td>
                        <td>Bs. <?php echo e(number_format($session->expected_balance, 2)); ?></td>
                        <td><?php echo e($session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-'); ?></td>
                        <td class="<?php echo e(($session->difference_amount ?? 0) < 0 ? 'text-danger' : 'text-success'); ?>">
                            <?php echo e($session->difference_amount !== null ? 'Bs. ' . number_format($session->difference_amount, 2) : '-'); ?>

                        </td>
                        <td>
                            <div><small>Apertura: <?php echo e($session->opened_at?->format('d/m/Y H:i')); ?></small></div>
                            <div><small>Cierre: <?php echo e($session->closed_at?->format('d/m/Y H:i') ?? '-'); ?></small></div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay sesiones de caja para mostrar.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($sessions->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/cash-sessions/index.blade.php ENDPATH**/ ?>