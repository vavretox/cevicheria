<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($printLabel); ?> #<?php echo e($printReference ?? $order->display_number); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 16px;
            color: #111;
            background: #fff;
        }

        .ticket {
            max-width: 340px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .meta {
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 12px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .item {
            margin-bottom: 10px;
        }

        .item:last-child {
            margin-bottom: 0;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 15px;
            font-weight: bold;
        }

        .notes {
            margin-top: 3px;
            font-size: 12px;
            padding-left: 8px;
        }

        .empty {
            text-align: center;
            font-size: 13px;
            padding: 14px 0;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 12px;
        }

        .no-print {
            text-align: center;
            margin-top: 16px;
        }

        .no-print button {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: #2c3e50;
            color: #fff;
            cursor: pointer;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php
        $sortedItems = $foodItems->sortBy(function ($detail) {
            $category = strtolower(trim($detail->product?->category?->name ?? ''));

            if (str_contains($category, 'ceviche')) {
                return '1-' . ($detail->product?->name ?? '');
            }

            return '2-' . ($detail->product?->name ?? '');
        })->values();

        $dineInItems = $sortedItems->where('service_type', 'dine_in')->values();
        $takeawayItems = $sortedItems->where('service_type', 'takeaway')->values();
    ?>
    <div class="ticket">
        <div class="header">
            <h1>COCINA</h1>
            <div><?php echo e($printLabel); ?></div>
            <div>Cevicheria Los Pepes</div>
        </div>

        <div class="meta">
            <div class="meta-row">
                <span><strong>Pedido:</strong> #<?php echo e($printReference ?? $order->display_number); ?></span>
                <span><strong>Mesa:</strong> <?php echo e($order->table_number); ?></span>
            </div>
            <div class="meta-row">
                <span><strong>Mesero:</strong> <?php echo e($order->user->name); ?></span>
                <span><strong>Hora:</strong> <?php echo e($printedAt->format('d/m H:i')); ?></span>
            </div>
        </div>

        <div class="items">
            <?php if($sortedItems->isEmpty()): ?>
                <div class="empty">
                    No hay alimentos para imprimir en esta comanda.
                </div>
            <?php else: ?>
                <?php if($dineInItems->isNotEmpty()): ?>
                    <div class="notes" style="padding-left:0; margin-bottom:8px; font-size:13px;">
                        <strong>PARA MESA</strong>
                    </div>
                    <?php $__currentLoopData = $dineInItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="item">
                            <div class="item-line">
                                <span><?php echo e($detail->quantity); ?> x <?php echo e($detail->product?->name ?? 'Producto'); ?></span>
                            </div>
                            <?php if($detail->notes): ?>
                                <div class="notes"><?php echo e($detail->notes); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                <?php if($takeawayItems->isNotEmpty()): ?>
                    <?php if($dineInItems->isNotEmpty()): ?>
                        <div style="border-top:1px dashed #000; margin:10px 0;"></div>
                    <?php endif; ?>
                    <div class="notes" style="padding-left:0; margin-bottom:8px; font-size:13px;">
                        <strong>PARA LLEVAR</strong>
                    </div>
                    <?php $__currentLoopData = $takeawayItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="item">
                            <div class="item-line">
                                <span><?php echo e($detail->quantity); ?> x <?php echo e($detail->product?->name ?? 'Producto'); ?></span>
                            </div>
                            <?php if($detail->notes): ?>
                                <div class="notes"><?php echo e($detail->notes); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="footer">
            <?php if($scope === 'added'): ?>
                Solo se imprimen los alimentos agregados en la ultima modificacion.
            <?php else: ?>
                Comanda completa de cocina. Las bebidas no se incluyen.
            <?php endif; ?>
        </div>

        <div class="no-print">
            <button type="button" id="printKitchenTicket">Imprimir</button>
        </div>
    </div>
    <script>
        const waiterDashboardUrl = <?php echo json_encode(route('waiter.dashboard'), 15, 512) ?>;
        let hasHandledPrintReturn = false;

        function returnToWaiterDashboard() {
            if (hasHandledPrintReturn) {
                return;
            }

            hasHandledPrintReturn = true;

            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location = waiterDashboardUrl;
                    window.opener.focus();
                    window.close();
                    return;
                } catch (error) {
                    // If the opener cannot be controlled, fall back to redirecting this tab.
                }
            }

            window.location.href = waiterDashboardUrl;
        }

        document.getElementById('printKitchenTicket')?.addEventListener('click', function () {
            hasHandledPrintReturn = false;
            window.print();
        });

        window.addEventListener('afterprint', returnToWaiterDashboard);
    </script>
</body>
</html>
<?php /**PATH /var/www/html/cevicheria-pos/resources/views/waiter/print-order.blade.php ENDPATH**/ ?>