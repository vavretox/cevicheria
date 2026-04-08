<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión Rápida de Ventas</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: "Courier New", monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px 8px 24px;
            color: #111827;
            background: #ffffff;
            font-size: 12px;
        }

        .center {
            text-align: center;
        }

        .header {
            border-bottom: 2px dashed #111827;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .meta p,
        .summary p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 4px 0;
            vertical-align: top;
        }

        thead th {
            border-top: 1px solid #111827;
            border-bottom: 1px solid #111827;
            font-size: 11px;
        }

        tbody tr + tr td {
            border-top: 1px dashed #d1d5db;
        }

        .product-name {
            width: 44%;
            word-break: break-word;
            padding-right: 4px;
        }

        .right {
            text-align: right;
        }

        .totals {
            border-top: 2px dashed #111827;
            border-bottom: 2px dashed #111827;
            margin-top: 10px;
            padding: 10px 0;
        }

        .totals p {
            margin: 4px 0;
        }

        .grand-total {
            font-size: 16px;
            font-weight: 700;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header center">
        <h1>Cevichería Los Pepes</h1>
        <div>Impresión rápida de ventas</div>
    </div>

    <div class="meta">
        <p><strong>Desde:</strong> {{ $dateFrom ?: 'Todas' }}</p>
        <p><strong>Hasta:</strong> {{ $dateTo ?: 'Todas' }}</p>
        <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>Ventas:</strong> {{ $ordersCount }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="product-name">Producto</th>
                <th class="right">Cant.</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td class="product-name">{{ $product->name }}</td>
                    <td class="right">{{ $product->quantity }}</td>
                    <td class="right">Bs. {{ number_format($product->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center" style="padding-top: 10px;">No hay ventas para imprimir.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Total de productos:</strong> <span class="right" style="float: right;">{{ $totalProducts }}</span></p>
        <p class="grand-total"><strong>Total general:</strong> <span class="right" style="float: right;">Bs. {{ number_format($grandTotal, 2) }}</span></p>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        <div>Resumen por producto</div>
        <div>Impresión térmica</div>
    </div>

    <div class="center no-print" style="margin-top: 16px;">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
