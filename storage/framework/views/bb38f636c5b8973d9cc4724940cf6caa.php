?<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Venta #<?php echo e($order->display_number); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 20px;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            max-width: 80mm;
            margin: 0 auto;
            padding: 20px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .receipt-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .receipt-info {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .receipt-info p {
            margin: 3px 0;
        }

        .receipt-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 5px 2px;
            text-align: left;
        }

        .receipt-table th {
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }

        .receipt-total {
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }

        .receipt-total p {
            margin: 3px 0;
            font-size: 13px;
        }

        .receipt-total .total-amount {
            font-size: 18px;
            font-weight: bold;
        }

        .receipt-footer {
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <h1>Cevichería Los Pepes</h1>
            <p style="margin: 5px 0;">Restaurante de Ceviche y Mariscos</p>
            <p style="margin: 5px 0;">RUC: 20123456789</p>
            <p style="margin: 5px 0;">Dirección: Av. Principal 123</p>
            <p style="margin: 5px 0;">Teléfono: (01) 234-5678</p>
        </div>

        <!-- Info del Pedido -->
        <div class="receipt-info">
            <p><strong>BOLETA DE VENTA</strong></p>
            <p>Nro: <?php echo e($order->display_number); ?></p>
            <p>Fecha: <?php echo e($order->completed_at ? $order->completed_at->format('d/m/Y H:i:s') : $order->created_at->format('d/m/Y H:i:s')); ?></p>
            <p>Mesa: <?php echo e($order->table_number); ?></p>
            <p>Atendido por: <?php echo e($order->user->name); ?></p>
            <?php if($order->cashier): ?>
            <p>Cajero: <?php echo e($order->cashier->name); ?></p>
            <?php endif; ?>
            <p>Pago: <?php echo e($order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR')); ?></p>
            <?php if($order->payment_method === 'cash'): ?>
            <p>Recibido: Bs. <?php echo e(number_format($order->amount_received ?? 0, 2)); ?></p>
            <p>Vuelto: Bs. <?php echo e(number_format($order->change_amount ?? 0, 2)); ?></p>
            <?php elseif($order->payment_method === 'mixed'): ?>
            <p>Efectivo: Bs. <?php echo e(number_format($order->cash_paid_amount ?? 0, 2)); ?></p>
            <p>QR: Bs. <?php echo e(number_format($order->qr_paid_amount ?? 0, 2)); ?></p>
            <?php endif; ?>
        </div>

        <!-- Productos -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>CANT</th>
                    <th>DESCRIPCIÓN</th>
                    <th class="text-right">P.UNIT</th>
                    <th class="text-right">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($detail->quantity); ?></td>
                    <td>
                        <?php echo e(Str::limit($detail->product->name, 15)); ?>

                        <div style="font-size: 10px;"><?php echo e($detail->service_type_label); ?></div>
                    </td>
                    <td class="text-right"><?php echo e(number_format($detail->unit_price, 2)); ?></td>
                    <td class="text-right"><?php echo e(number_format($detail->subtotal, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="receipt-total">
            <p>
                <span>SUBTOTAL:</span>
                <span class="text-right" style="float: right;">Bs. <?php echo e(number_format($order->subtotal, 2)); ?></span>
            </p>
            <p class="total-amount">
                <span>TOTAL:</span>
                <span class="text-right" style="float: right;">Bs. <?php echo e(number_format($order->total, 2)); ?></span>
            </p>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p>¡GRACIAS POR SU PREFERENCIA!</p>
            <p>Vuelva Pronto</p>
            <p style="margin-top: 10px;">Sistema POS v1.0</p>
            <p><?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <a href="<?php echo e(route('cashier.download-receipt', $order->id)); ?>" class="btn btn-success me-2">
            <i class="fas fa-download"></i> Descargar PDF
        </a>
        <a href="<?php echo e(route('cashier.dashboard')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <script src="https://kit.fontawesome.com/your-code.js"></script>
    <script>
        // Auto print cuando se carga la página (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>












<?php /**PATH /var/www/html/cevicheria-pos/resources/views/cashier/receipt.blade.php ENDPATH**/ ?>