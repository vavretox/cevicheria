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
            font-family: Cambria, Georgia, "Times New Roman", serif;
            color: var(--text);
            background: white;
            font-size: 13px;
            line-height: 1.35;
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
                @if($type === 'month')
                    Resumen agrupado por mes con detalle completo por pedido
                @else
                    Resumen agrupado por día con detalle completo por pedido
                @endif
            </p>
        </div>
        <div class="meta">
            <div><strong>Desde:</strong> {{ $dateFrom ?: '-' }}</div>
            <div><strong>Hasta:</strong> {{ $dateTo ?: '-' }}</div>
            <div><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">Total Ventas</div>
            <div class="value">Bs. {{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Pedidos</div>
            <div class="value">{{ $totalOrders }}</div>
        </div>
        <div class="stat">
            <div class="label">Ticket Promedio</div>
            <div class="value">Bs. {{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00' }}</div>
        </div>
    </div>

    @forelse($grouped as $groupKey => $items)
        @php
            $label = $type === 'month'
                ? \Carbon\Carbon::createFromFormat('Y-m', $groupKey)->translatedFormat('F Y')
                : \Carbon\Carbon::createFromFormat('Y-m-d', $groupKey)->format('d/m/Y');
            $groupTotal = $items->sum('total');
            $groupProducts = $items->sum(fn ($order) => $order->details->sum('quantity'));
        @endphp

        <div class="group">
            <div class="group-title">
                {{ $label }} | {{ $items->count() }} ventas | {{ $groupProducts }} productos | Total Bs. {{ number_format($groupTotal, 2) }}
            </div>

            @foreach($items as $order)
                <div class="order-block">
                    <div class="order-head">
                        <div class="order-top">
                            <div class="order-id">Pedido #{{ $order->display_number }}</div>
                            <div class="order-total">Bs. {{ number_format($order->total, 2) }}</div>
                        </div>

                        <div class="meta-grid">
                            <div class="meta-item">
                                <div class="label">Fecha</div>
                                <div class="value">{{ ($order->completed_at ?? $order->created_at)->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Mesa</div>
                                <div class="value">{{ $order->table_label ?? $order->table_number ?? 'Sin mesa' }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Mesero</div>
                                <div class="value">{{ $order->user ? $order->user->name : '-' }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Cajero</div>
                                <div class="value">{{ $order->cashier ? $order->cashier->name : '-' }}</div>
                            </div>
                            <div class="meta-item">
                                <div class="label">Pago</div>
                                <div class="value">{{ $order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR') }}</div>
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
                            @foreach($order->details as $detail)
                                <tr>
                                    <td>{{ $detail->product->name ?? 'Producto eliminado' }}</td>
                                    <td class="right">{{ $detail->quantity }}</td>
                                    <td class="right">Bs. {{ number_format($detail->unit_price, 2) }}</td>
                                    <td>{{ $detail->notes ?: '-' }}</td>
                                    <td class="right">Bs. {{ number_format($detail->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="summary-line">
                        <span>{{ $order->details->sum('quantity') }} productos</span>
                        <span>Total: Bs. {{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="muted">No hay ventas en el período seleccionado.</p>
    @endforelse

    <div class="no-print" style="margin-top: 16px;">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
