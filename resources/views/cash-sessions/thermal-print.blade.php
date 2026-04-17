<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión rápida de caja</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 4mm;
        }

        * {
            box-sizing: border-box;
        }

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
            font-family: Cambria, Georgia, "Times New Roman", serif;
            width: 80mm;
            margin: 0 auto;
            padding: 10px 8px 24px;
            color: #111827;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.32;
        }

        .center {
            text-align: center;
        }

        .right {
            float: right;
            text-align: right;
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

        .section {
            border-bottom: 1px dashed #111827;
            padding: 8px 0;
        }

        .section p {
            margin: 4px 0;
        }

        .total-line {
            font-size: 16px;
            font-weight: 700;
        }

        .muted {
            color: #4b5563;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 12px;
        }

        .empty {
            border: 1px dashed #111827;
            padding: 14px 10px;
            margin-top: 12px;
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
        }

        .no-print .back-link {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header center">
        <h1>Cevichería Los Pepes</h1>
        <div>Impresión rápida de caja</div>
    </div>

    @if($session)
        <div class="section">
            <p><strong>{{ $title }}</strong></p>
            <p><strong>Cajero:</strong> {{ $session->cashier?->name ?? '-' }}</p>
            <p><strong>Estado:</strong> {{ $session->status === 'open' ? 'Abierta' : 'Cerrada' }}</p>
            @if($selectedDate)
                <p><strong>Fecha:</strong> {{ $selectedDate->format('d/m/Y') }}</p>
            @endif
            <p><strong>Apertura:</strong> {{ $session->opened_at?->format('d/m/Y H:i') }}</p>
            @if($session->closed_at)
                <p><strong>Cierre:</strong> {{ $session->closed_at->format('d/m/Y H:i') }}</p>
            @endif
            <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="section">
            <p><strong>Monto inicial:</strong> <span class="right">Bs. {{ number_format($session->opening_amount, 2) }}</span></p>
            <div style="clear: both;"></div>
            <p><strong>Ventas:</strong> <span class="right">{{ $session->completed_orders_count ?? 0 }}</span></p>
            <div style="clear: both;"></div>
        </div>

        <div class="section">
            <p><strong>Efectivo:</strong> <span class="right">Bs. {{ number_format($session->cash_sales_total, 2) }}</span></p>
            <div style="clear: both;"></div>
            <p><strong>QR:</strong> <span class="right">Bs. {{ number_format($session->qr_sales_total, 2) }}</span></p>
            <div style="clear: both;"></div>
            <p class="total-line"><strong>Total ventas:</strong> <span class="right">Bs. {{ number_format($session->sales_total, 2) }}</span></p>
            <div style="clear: both;"></div>
        </div>

        <div class="section">
            <p><strong>Esperado en caja:</strong> <span class="right">Bs. {{ number_format($session->expected_balance, 2) }}</span></p>
            <div style="clear: both;"></div>
            <p class="muted">Apertura + ventas en efectivo. QR queda reportado aparte.</p>
            @if($session->counted_amount !== null)
                <p><strong>Contado:</strong> <span class="right">Bs. {{ number_format($session->counted_amount, 2) }}</span></p>
                <div style="clear: both;"></div>
            @endif
        </div>

        @if($session->opening_note)
            <div class="section">
                <p><strong>Observación de apertura:</strong></p>
                <p>{{ $session->opening_note }}</p>
            </div>
        @endif

        @if($session->closing_note)
            <div class="section">
                <p><strong>Observación de cierre:</strong></p>
                <p>{{ $session->closing_note }}</p>
            </div>
        @endif

        <div class="footer">
            <div>{{ $session->status === 'open' ? 'Resumen de caja abierta' : 'Resumen de caja cerrada' }}</div>
            <div>Impresión térmica</div>
        </div>
    @else
        <div class="empty">
            <strong>No hay caja para la fecha seleccionada.</strong>
            <div class="muted">Elige otro día o revisa el filtro antes de imprimir.</div>
        </div>
    @endif

    <div class="no-print">
        @if($session)
            <button type="button" onclick="window.print()">Imprimir</button>
        @endif
        <a href="{{ $returnUrl }}" class="back-link">Volver</a>
    </div>
</body>
</html>
