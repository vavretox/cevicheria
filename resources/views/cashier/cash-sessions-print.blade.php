<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja y Arqueo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 20px; color: #4b5563; }
        .card { border: 1px solid #d1d5db; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        th { background: #f3f4f6; }
        .actions { margin-top: 20px; }
        @media print { .actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Caja y Arqueo</h1>
    <div class="meta">Reporte de sesiones del cajero. Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</div>

    @if($currentSession)
    <div class="card">
        <h2>Caja abierta actual</h2>
        <p><strong>Apertura:</strong> Bs. {{ number_format($currentSession->opening_amount, 2) }}</p>
        <p><strong>Abierta desde:</strong> {{ $currentSession->opened_at?->format('d/m/Y H:i') }}</p>
        <p><strong>Ventas del turno:</strong> Bs. {{ number_format($currentSession->sales_total, 2) }}</p>
        <p><strong>Esperado actual:</strong> Bs. {{ number_format($currentSession->expected_balance, 2) }}</p>
        @if($currentSession->opening_note)
            <p><strong>Observación:</strong> {{ $currentSession->opening_note }}</p>
        @endif
    </div>
    @endif

    <div class="card">
        <h2>Historial de sesiones</h2>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Apertura</th>
                    <th>Ventas</th>
                    <th>Esperado</th>
                    <th>Contado</th>
                    <th>Diferencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->status === 'open' ? 'Abierta' : 'Cerrada' }}</td>
                    <td>
                        Bs. {{ number_format($session->opening_amount, 2) }}<br>
                        <small>{{ $session->opened_at?->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>Bs. {{ number_format($session->sales_total, 2) }}</td>
                    <td>Bs. {{ number_format($session->expected_balance, 2) }}</td>
                    <td>{{ $session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-' }}</td>
                    <td>{{ $session->difference_amount !== null ? 'Bs. ' . number_format($session->difference_amount, 2) : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">No hay sesiones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="actions">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
