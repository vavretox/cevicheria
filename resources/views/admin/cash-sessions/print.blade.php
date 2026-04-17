<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Caja y Arqueo</title>
    <style>
        body { font-family: Cambria, Georgia, "Times New Roman", serif; margin: 24px; color: #111827; font-size: 13px; line-height: 1.35; }
        h1 { margin: 0 0 8px; }
        .meta { margin-bottom: 20px; color: #4b5563; }
        .summary { display: flex; gap: 12px; margin-bottom: 20px; }
        .summary-box { border: 1px solid #d1d5db; border-radius: 10px; padding: 12px 14px; min-width: 180px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        th { background: #f3f4f6; }
        .actions { margin-top: 20px; }
        @media print { .actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Reporte de Caja y Arqueo</h1>
    <div class="meta">Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</div>

    <div class="summary">
        <div class="summary-box">
            <div>Cajas abiertas</div>
            <strong>{{ $summary['open_count'] }}</strong>
        </div>
        <div class="summary-box">
            <div>Cajas cerradas</div>
            <strong>{{ $summary['closed_count'] }}</strong>
        </div>
        <div class="summary-box">
            <div>Ventas en efectivo</div>
            <strong>Bs. {{ number_format($summary['cash_sales_total'], 2) }}</strong>
        </div>
        <div class="summary-box">
            <div>Ventas por QR</div>
            <strong>Bs. {{ number_format($summary['qr_sales_total'], 2) }}</strong>
        </div>
        <div class="summary-box">
            <div>Ventas registradas</div>
            <strong>Bs. {{ number_format($summary['sales_total'], 2) }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cajero</th>
                <th>Estado</th>
                <th>Apertura</th>
                <th>Efectivo</th>
                <th>QR</th>
                <th>Total</th>
                <th>Esperado caja</th>
                <th>Contado</th>
                <th>Fechas</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
            <tr>
                <td>{{ $session->cashier?->name ?? '-' }}</td>
                <td>{{ $session->status === 'open' ? 'Abierta' : 'Cerrada' }}</td>
                <td>Bs. {{ number_format($session->opening_amount, 2) }}</td>
                <td>Bs. {{ number_format($session->cash_sales_total, 2) }}</td>
                <td>Bs. {{ number_format($session->qr_sales_total, 2) }}</td>
                <td>Bs. {{ number_format($session->sales_total, 2) }}</td>
                <td>Bs. {{ number_format($session->expected_balance, 2) }}</td>
                <td>{{ $session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-' }}</td>
                <td>
                    Apertura: {{ $session->opened_at?->format('d/m/Y H:i') }}<br>
                    Cierre: {{ $session->closed_at?->format('d/m/Y H:i') ?? '-' }}
                </td>
                <td>
                    @if($session->opening_note || $session->closing_note)
                        @if($session->opening_note)
                            <strong>Apertura:</strong> {{ $session->opening_note }}<br>
                        @endif
                        @if($session->closing_note)
                            <strong>Cierre:</strong> {{ $session->closing_note }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">No hay sesiones para mostrar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="actions">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
