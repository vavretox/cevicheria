<?php $__env->startSection('title', 'Vista de Mesas'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('cashier._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .table-board {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }

    .table-card {
        border: 2px solid #dfe7ef;
        border-radius: 14px;
        padding: 14px;
        background: #fff;
    }

    .table-card.available {
        background: #ffffff;
        border-color: #dbe4ee;
    }

    .table-card.reserved {
        background: #fff8e8;
        border-color: #facc15;
    }

    .table-card.occupied {
        background: #fee2e2;
        border-color: #ef4444;
    }

    .table-card.closed {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .table-card-status {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <h2><i class="fas fa-table-cells-large me-2"></i>Vista de Mesas</h2>
    <p class="text-muted mb-0">Panel informativo para caja con estado, reservas y pedido activo por mesa.</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-board">
            <?php $__currentLoopData = $tableBoard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $status = $table->ui_status;
                    $activeOrder = $table->activeOrders->first();
                ?>
                <div class="table-card <?php echo e($status); ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong><?php echo e($table->name); ?></strong>
                        <span class="table-card-status">
                            <?php if($status === 'available'): ?>
                                Libre
                            <?php elseif($status === 'reserved'): ?>
                                Reservada
                            <?php elseif($status === 'occupied'): ?>
                                Ocupada
                            <?php else: ?>
                                Cerrada
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if($table->zone): ?>
                        <div class="small text-muted mb-1"><?php echo e($table->zone); ?></div>
                    <?php endif; ?>
                    <?php if($table->isReserved()): ?>
                        <div class="small text-muted"><strong>Reserva:</strong> <?php echo e($table->reservation_name); ?></div>
                        <div class="small text-muted mb-2"><?php echo e($table->reservation_at?->format('d/m H:i')); ?></div>
                    <?php endif; ?>
                    <?php if($activeOrder): ?>
                        <div class="small"><strong>Pedido:</strong> #<?php echo e($activeOrder->display_number); ?></div>
                        <div class="small"><strong>Mesero:</strong> <?php echo e($activeOrder->user?->name ?? '-'); ?></div>
                        <div class="small"><strong>Total:</strong> Bs. <?php echo e(number_format($activeOrder->total, 2)); ?></div>
                    <?php else: ?>
                        <div class="small text-muted">Sin pedido activo</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/cashier/tables.blade.php ENDPATH**/ ?>