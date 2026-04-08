<?php $__env->startSection('title', 'Caja y Arqueo'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('cashier._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2><i class="fas fa-cash-register me-2"></i>Caja y Arqueo</h2>
            <p class="text-muted mb-0">Abre tu caja, procesa ventas y realiza el cierre con arqueo al final del turno.</p>
        </div>
        <a href="<?php echo e(route('cashier.cash-sessions.print')); ?>" class="btn btn-outline-dark" target="_blank" rel="noopener">
            <i class="fas fa-print me-2"></i>Imprimir
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <?php if($currentSession): ?>
        <div class="card border-warning">
            <div class="card-header bg-warning-subtle">
                <h5 class="mb-0"><i class="fas fa-lock-open me-2"></i>Caja Abierta</h5>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Apertura:</strong> Bs. <?php echo e(number_format($currentSession->opening_amount, 2)); ?></div>
                <div class="mb-2"><strong>Abierta desde:</strong> <?php echo e($currentSession->opened_at?->format('d/m/Y H:i')); ?></div>
                <div class="mb-2"><strong>Ventas del turno:</strong> Bs. <?php echo e(number_format($currentSession->sales_total, 2)); ?></div>
                <div class="mb-3"><strong>Esperado actual:</strong> Bs. <?php echo e(number_format($currentSession->expected_balance, 2)); ?></div>
                <?php if($currentSession->opening_note): ?>
                    <div class="alert alert-light small"><?php echo e($currentSession->opening_note); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('cashier.cash-sessions.close', $currentSession->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Monto contado al cierre</label>
                        <input type="number" step="0.01" min="0" name="counted_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación de cierre</label>
                        <textarea name="closing_note" class="form-control" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-lock me-2"></i>Cerrar Caja y Hacer Arqueo
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-success">
            <div class="card-header bg-success-subtle">
                <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>Abrir Caja</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('cashier.cash-sessions.open')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Monto inicial</label>
                        <input type="number" step="0.01" min="0" name="opening_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación de apertura</label>
                        <textarea name="opening_note" class="form-control" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-play me-2"></i>Abrir Caja
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Sesiones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Apertura</th>
                                <th>Ventas</th>
                                <th>Esperado</th>
                                <th>Contado</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo e($session->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                        <?php echo e($session->status === 'open' ? 'Abierta' : 'Cerrada'); ?>

                                    </span>
                                </td>
                                <td>
                                    <div>Bs. <?php echo e(number_format($session->opening_amount, 2)); ?></div>
                                    <small class="text-muted"><?php echo e($session->opened_at?->format('d/m H:i')); ?></small>
                                </td>
                                <td>Bs. <?php echo e(number_format($session->sales_total, 2)); ?></td>
                                <td>Bs. <?php echo e(number_format($session->expected_balance, 2)); ?></td>
                                <td><?php echo e($session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-'); ?></td>
                                <td class="<?php echo e(($session->difference_amount ?? 0) < 0 ? 'text-danger' : 'text-success'); ?>">
                                    <?php echo e($session->difference_amount !== null ? 'Bs. ' . number_format($session->difference_amount, 2) : '-'); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aún no tienes sesiones de caja registradas.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/cashier/cash-sessions.blade.php ENDPATH**/ ?>