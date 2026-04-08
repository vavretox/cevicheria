<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Cevichería Los Pepes</title>
    <style>
        :root {
            --text: #1f2937;
            --muted: #6b7280;
            --border: #dbe3ee;
            --soft: #f8fafc;
            --soft-2: #eef2ff;
            --accent: #0f172a;
            --success: #166534;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: white;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-end;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .title h1 {
            margin: 0 0 6px 0;
            font-size: 24px;
        }

        .title p,
        .meta {
            margin: 0;
            color: var(--muted);
        }

        .meta {
            text-align: right;
            line-height: 1.6;
        }

        .stats {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-spacing: 12px 0;
            margin: 0 -12px 24px -12px;
        }

        .stat {
            display: table-cell;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: var(--soft);
        }

        .stat .label {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .stat .value {
            font-size: 18px;
            font-weight: 700;
        }

        .group {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .group-title {
            font-weight: 700;
            padding: 10px 12px;
            background: var(--soft-2);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .order-block {
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .order-head {
            background: var(--soft);
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .order-id {
            font-size: 15px;
            font-weight: 700;
        }

        .order-total {
            color: var(--success);
            font-size: 16px;
            font-weight: 700;
            text-align: right;
        }

        .meta-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-spacing: 10px 0;
            margin: 0 -10px;
        }

        .meta-item {
            display: table-cell;
            padding: 0 10px;
            vertical-align: top;
        }

        .meta-item .label {
            color: var(--muted);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }

        .meta-item .value {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border-bottom: 1px solid var(--border);
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            text-align: left;
            font-size: 11px;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .right {
            text-align: right;
        }

        .notes {
            color: var(--muted);
            font-size: 11px;
            margin-top: 3px;
        }

        .summary-line {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            padding: 10px 12px;
            border-top: 1px solid var(--border);
            background: #fcfcfd;
            font-weight: 700;
        }

        .muted {
            color: var(--muted);
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">
            <h1>Reporte de Ventas</h1>
            <p>
                <?php if($type === 'month'): ?>
                    Resumen agrupado por mes con detalle completo por pedido
                <?php else: ?>
                    Resumen agrupado por día con detalle completo por pedido
                <?php endif; ?>
            </p>
        </div>
        <div class="meta">
            <div><strong>Desde:</strong> <?php echo e($dateFrom ?: '-'); ?></div>
            <div><strong>Hasta:</strong> <?php echo e($dateTo ?: '-'); ?></div>
            <div><strong>Generado:</strong> <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">Total Ventas</div>
            <div class="value">Bs. <?php echo e(number_format($totalSales, 2)); ?></div>
        </div>
        <div class="stat">
            <div class="label">Pedidos</div>
            <div class="value"><?php echo e($totalOrders); ?></div>
        </div>
        <div class="stat">
            <div class="label">Ticket Promedio</div>
            <div class="value">Bs. <?php echo e($totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00'); ?></div>
        </div>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $label = $type === 'month'
                ? \Carbon\Carbon::createFromFormat('Y-m', $groupKey)->translatedFormat('F Y')
                : \Carbon\Carbon::createFromFormat('Y-m-d', $groupKey)->format('d/m/Y');
            $groupTotal = $items->sum('total');
            $groupProducts = $items->sum(fn ($order) => $order->details->sum('quantity'));
        ?>

        <div class="group">
            <div class="group-title">
                <?php echo e($label); ?> | <?php echo e($items->count()); ?> ventas | <?php echo e($groupProducts); ?> productos | Total Bs. <?php echo e(number_format($groupTotal, 2)); ?>

            </div>

            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="order-block">
                    <div class="order-head">
                        <div class="order-top">
                            <div class="order-id">Pedido #<?php echo e($order->display_number); ?></div>
                            <div class="order-total">Bs. <?php echo e(number_format($order->total, 2)); ?></div>
                        </div>

                        <div class="meta-grid">
                            <div class="meta-item">
                                <div class="label">Fecha</div>
                                <div class="value"><?php echo e(($order->completed_at ?? $order->created_at)->format('d/m/Y H:i')); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Mesa</div>
                                <div class="value"><?php echo e($order->table_label ?? $order->table_number ?? 'Sin mesa'); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Mesero</div>
                                <div class="value"><?php echo e($order->user ? $order->user->name : '-'); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Cajero</div>
                                <div class="value"><?php echo e($order->cashier ? $order->cashier->name : '-'); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Pago</div>
                                <div class="value"><?php echo e($order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR')); ?></div>
                            </div>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 38%;">Producto</th>
                                <th class="right" style="width: 10%;">Cant.</th>
                                <th class="right" style="width: 16%;">Unitario</th>
                                <th style="width: 20%;">Observación</th>
                                <th class="right" style="width: 16%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($detail->product->name ?? 'Producto eliminado'); ?></td>
                                    <td class="right"><?php echo e($detail->quantity); ?></td>
                                    <td class="right">Bs. <?php echo e(number_format($detail->unit_price, 2)); ?></td>
                                    <td><?php echo e($detail->notes ?: '-'); ?></td>
                                    <td class="right">Bs. <?php echo e(number_format($detail->subtotal, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>

                    <div class="summary-line">
                        <span><?php echo e($order->details->sum('quantity')); ?> productos</span>
                        <span>Total: Bs. <?php echo e(number_format($order->total, 2)); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="muted">No hay ventas en el período seleccionado.</p>
    <?php endif; ?>

    <div class="no-print" style="margin-top: 16px;">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/reports-print.blade.php ENDPATH**/ ?>