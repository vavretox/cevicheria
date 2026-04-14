<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Venta #{{ $order->display_number }}</title>
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
            font-family: Cambria, Georgia, "Times New Roman", serif;
            max-width: 80mm;
            margin: 0 auto;
            padding: 20px;
            font-size: 13px;
            line-height: 1.35;
            position: relative;
            overflow-x: hidden;
        }

        .receipt {
            position: relative;
            z-index: 1;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .receipt-logo {
            display: block;
            width: 42mm;
            max-width: 100%;
            height: auto;
            margin: 0 auto 8px auto;
        }

        .receipt-info {
            margin-bottom: 15px;
            font-size: 13px;
        }

        .receipt-info p {
            margin: 3px 0;
        }

        .receipt-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 5px 2px;
            text-align: left;
            vertical-align: top;
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
            font-size: 14px;
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
            font-size: 12px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-print {
            text-align: center;
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .no-print button,
        .no-print a {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: #2c3e50;
            color: #fff;
            cursor: pointer;
            font: inherit;
            text-decoration: none;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .no-print .back-link {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <img src="{{ asset('images/logo-los-pepes.jpeg') }}" alt="Logo" class="receipt-logo">
            <p style="margin: 5px 0;">Restaurante de Ceviche y Mariscos</p>
            <p style="margin: 5px 0;">Dirección: B. Bartolome Attard C. Capitan Mendieta</p>
            <p style="margin: 5px 0;">Teléfono: 67691315</p>
        </div>

        <!-- Info del Pedido -->
        <div class="receipt-info">
            <p><strong>BOLETA DE VENTA</strong></p>
            <p>Nro: {{ $order->display_number }}</p>
            <p>Fecha: {{ $order->completed_at ? $order->completed_at->format('d/m/Y H:i:s') : $order->created_at->format('d/m/Y H:i:s') }}</p>
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
                @foreach($order->details as $detail)
                <tr>
                    <td>{{ $detail->quantity }}</td>
                    <td>
                        {{ $detail->product->name }}
                        <div style="font-size: 10px;">{{ $detail->service_type_label }}</div>
                    </td>
                    <td class="text-right">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="receipt-total">
            <p>
                <span>SUBTOTAL:</span>
                <span class="text-right" style="float: right;">Bs. {{ number_format($order->subtotal, 2) }}</span>
            </p>
            <p class="total-amount">
                <span>TOTAL:</span>
                <span class="text-right" style="float: right;">Bs. {{ number_format($order->total, 2) }}</span>
            </p>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p>¡GRACIAS POR SU PREFERENCIA!</p>
            <p>Vuelva Pronto</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="no-print">
        <button type="button" onclick="window.print()">Imprimir</button>
        <a href="{{ $returnUrl ?? route('cashier.dashboard') }}" class="back-link">Volver</a>
    </div>

    <script src="https://kit.fontawesome.com/your-code.js"></script>
    <script>
        // Auto print cuando se carga la página (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>












