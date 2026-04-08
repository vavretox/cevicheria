<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ganancia de Bebidas - Cevichería Los Pepes</title>
    <style>
        :root {
            --text: #1f2937;
            --muted: #6b7280;
            --border: #dbe3ee;
            --soft: #f8fafc;
            --soft-2: #fff7ed;
            --success: #166534;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: #fff;
            font-size: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-end;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title h1 {
            margin: 0 0 6px;
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
            margin: 0 -12px 20px;
        }

        .stat {
            display: table-cell;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: var(--soft);
        }

        .label {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }

        .value {
            font-size: 18px;
            font-weight: 700;
        }

        .note {
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid var(--border);
            padding: 8px 10px;
            vertical-align: middle;
        }

        th {
            background: var(--soft-2);
            text-align: left;
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .success {
            color: var(--success);
            font-weight: 700;
        }

        .danger {
            color: var(--danger);
            font-weight: 700;
        }

        .muted {
            color: var(--muted);
        }

        .no-print {
            margin-top: 16px;
            text-align: center;
        }

        .no-print button {
            border: none;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            padding: 10px 14px;
            cursor: pointer;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php
        $beverageSummary = $beverageGainReport['summary'];
        $beverageProducts = $beverageGainReport['products'];
    @endphp

    <div class="header">
        <div class="title">
            <h1>Reporte de Ganancia de Bebidas</h1>
            <p>Comparación de compra vs venta por cada producto de bebidas.</p>
        </div>
        <div class="meta">
            <div><strong>Desde:</strong> {{ $dateFrom ?: '-' }}</div>
            <div><strong>Hasta:</strong> {{ $dateTo ?: '-' }}</div>
            <div><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">Venta de Bebidas</div>
            <div class="value">Bs. {{ number_format($beverageSummary['total_revenue'], 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Compra Estimada</div>
            <div class="value">Bs. {{ number_format($beverageSummary['estimated_cost'], 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Ganancia</div>
            <div class="value">Bs. {{ number_format($beverageSummary['estimated_profit'], 2) }}</div>
        </div>
        <div class="stat">
            <div class="label">Unidades Vendidas</div>
            <div class="value">{{ $beverageSummary['total_units_sold'] }}</div>
        </div>
    </div>

    @if($beverageSummary['missing_cost_products'] > 0)
        <div class="note">
            Hay {{ $beverageSummary['missing_cost_products'] }} producto(s) y {{ $beverageSummary['missing_cost_units'] }} unidad(es) sin compra registrada. En esos casos no se calcula ganancia.
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="center">Unidades</th>
                <th class="right">Venta Prom./Unid.</th>
                <th class="right">Compra Prom./Unid.</th>
                <th class="right">Total Venta</th>
                <th class="right">Total Compra</th>
                <th class="right">Ganancia</th>
                <th class="right">Margen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($beverageProducts as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="center">{{ $item->units_sold }}</td>
                    <td class="right">Bs. {{ number_format($item->average_sale_price, 2) }}</td>
                    <td class="right">
                        @if($item->has_cost_data)
                            Bs. {{ number_format($item->average_unit_cost, 2) }}
                        @else
                            <span class="muted">Sin costo</span>
                        @endif
                    </td>
                    <td class="right">Bs. {{ number_format($item->revenue, 2) }}</td>
                    <td class="right">
                        @if($item->has_cost_data)
                            Bs. {{ number_format($item->estimated_cost, 2) }}
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td class="right">
                        @if($item->has_cost_data)
                            <span class="{{ $item->gross_profit >= 0 ? 'success' : 'danger' }}">
                                Bs. {{ number_format($item->gross_profit, 2) }}
                            </span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td class="right">
                        @if($item->margin !== null)
                            {{ number_format($item->margin, 2) }}%
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center muted">No hay ventas de bebidas en el período seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="no-print">
        <button type="button" onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
