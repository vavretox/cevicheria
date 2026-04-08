<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Almacén de Bebidas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 24px;
        }

        h1, h2, h3 {
            margin: 0;
        }

        .header {
            margin-bottom: 18px;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
        }

        .summary {
            display: flex;
            gap: 18px;
            margin: 18px 0;
        }

        .summary-box {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 12px 14px;
            min-width: 180px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            font-size: 12px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .mt-24 {
            margin-top: 24px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Almacén de Bebidas</h1>
        <div>Historial de entradas y salidas</div>
        <div>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary">
        <div class="summary-box">
            <strong>Stock actual</strong>
            <div>{{ number_format($totalBeverageUnits) }} unidades</div>
        </div>
        <div class="summary-box">
            <strong>Entradas registradas</strong>
            <div>{{ number_format($entryUnits) }} unidades</div>
        </div>
        <div class="summary-box">
            <strong>Salidas registradas</strong>
            <div>{{ number_format($exitUnits) }} unidades</div>
        </div>
    </div>

    <h3>Stock actual por producto</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-right">Precio venta</th>
                <th class="text-center">Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($beverageProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="text-right">Bs. {{ number_format($product->price, 2) }}</td>
                    <td class="text-center">{{ $product->stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No hay bebidas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-24">
        <h3>Historial de movimientos</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Movimiento</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Unidades</th>
                    <th>Detalle</th>
                    <th>Registró</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->movement_type_label }}</td>
                        <td>{{ $movement->product?->name ?? 'Producto' }}</td>
                        <td>{{ $movement->entry_type_label }}</td>
                        <td class="text-center">{{ $movement->quantity }}</td>
                        <td class="text-center">{{ $movement->movement_type === 'exit' ? '-' : '+' }}{{ $movement->total_units }}</td>
                        <td>
                            @if($movement->entry_type === 'box')
                                {{ $movement->quantity }} caja(s) de {{ $movement->units_per_box }} unidades
                            @else
                                {{ $movement->quantity }} unidad(es)
                            @endif
                            @if($movement->notes)
                                <br>{{ $movement->notes }}
                            @endif
                        </td>
                        <td>{{ $movement->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay movimientos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="no-print" style="margin-top: 20px;">
        <button onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>
