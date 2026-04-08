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
            --border: #e5e7eb;
            --accent: #0f172a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .title h1 {
            margin: 0 0 6px 0;
            font-size: 22px;
        }

        .title p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .meta {
            text-align: right;
            font-size: 12px;
            color: var(--muted);
        }

        .stats {
            display: flex;
            gap: 16px;
            margin: 16px 0 24px 0;
        }

        .stat {
            flex: 1;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
        }

        .stat h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat div {
            font-size: 18px;
            font-weight: 700;
        }

        .group {
            margin-bottom: 18px;
        }

        .group-title {
            font-weight: 700;
            margin-bottom: 8px;
            padding: 6px 10px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1px solid var(--border);
            padding: 6px 8px;
            font-size: 12px;
        }

        th {
            text-align: left;
            background: #f1f5f9;
        }

        tfoot td {
            font-weight: 700;
        }

        .right { text-align: right; }
        .muted { color: var(--muted); }
        .payment-lines div + div { margin-top: 2px; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">
            <h1>Reporte de Ventas - Cevichería Los Pepes</h1>
            <p>
                @if($type === 'month')
                    Resumen por mes
                @else
                    Resumen por día
                @endif
            </p>
        </div>
        <div class="meta">
            <div><strong>Desde:</strong> {{ $dateFrom ?: '-' }}</div>
            <div><strong>Hasta:</strong> {{ $dateTo ?: '-' }}</div>
            <div class="muted">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <h4>Total Ventas</h4>
            <div>Bs. {{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="stat">
            <h4>Pedidos</h4>
            <div>{{ $totalOrders }}</div>
        </div>
        <div class="stat">
            <h4>Ticket Promedio</h4>
            <div>Bs. {{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00' }}</div>
        </div>
    </div>

    @forelse($grouped as $groupKey => $items)
        @php
            $label = $type === 'month'
                ? \Carbon\Carbon::createFromFormat('Y-m', $groupKey)->translatedFormat('F Y')
                : \Carbon\Carbon::createFromFormat('Y-m-d', $groupKey)->format('d/m/Y');
            $groupTotal = $items->sum('total');
        @endphp
        <div class="group">
            <div class="group-title">{{ $label }} - Total: Bs. {{ number_format($groupTotal, 2) }}</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Pago</th>
                        <th class="right">Items</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $order)
                        @php
                            $paymentMethodLabel = match ($order->payment_method) {
                                'cash' => 'Efectivo',
                                'mixed' => 'Efectivo + QR',
                                default => 'QR',
                            };
                            $cashAmount = (float) ($order->cash_paid_amount ?? ($order->payment_method === 'cash' ? $order->total : 0));
                            $qrAmount = (float) ($order->qr_paid_amount ?? ($order->payment_method === 'qr' ? $order->total : 0));
                        @endphp
                        <tr>
                            <td>#{{ $order->display_number }}</td>
                            <td>{{ ($order->completed_at ?? $order->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $order->table_number }}</td>
                            <td>{{ $order->user ? $order->user->name : '-' }}</td>
                            <td>{{ $order->cashier ? $order->cashier->name : '-' }}</td>
                            <td>
                                <div class="payment-lines">
                                    <div>{{ $paymentMethodLabel }}</div>
                                    @if($order->payment_method === 'cash')
                                        <div class="muted">Bs. {{ number_format($cashAmount, 2) }}</div>
                                    @elseif($order->payment_method === 'qr')
                                        <div class="muted">Bs. {{ number_format($qrAmount, 2) }}</div>
                                    @elseif($order->payment_method === 'mixed')
                                        <div class="muted">Efectivo: Bs. {{ number_format($cashAmount, 2) }}</div>
                                        <div class="muted">QR: Bs. {{ number_format($qrAmount, 2) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="right">{{ $order->details->sum('quantity') }}</td>
                            <td class="right">Bs. {{ number_format($order->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right">Subtotal del {{ $type === 'month' ? 'mes' : 'día' }}</td>
                        <td class="right">Bs. {{ number_format($groupTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <p class="muted">No hay ventas en el período seleccionado.</p>
    @endforelse

    <div class="no-print" style="margin-top: 16px;">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
