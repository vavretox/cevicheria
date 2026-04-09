<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $printLabel }} #{{ $printReference ?? $order->display_number }}</title>
    <style>
        body {
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 10px 12px;
            color: #111;
            background: #fff;
            font-size: 12px;
            line-height: 1.2;
        }

        .ticket {
            max-width: 300px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0 0 2px;
        }

        .meta {
            font-size: 11px;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .items {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 6px 0;
        }

        .item {
            margin-bottom: 6px;
        }

        .item:last-child {
            margin-bottom: 0;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 13px;
            font-weight: bold;
        }

        .notes {
            margin-top: 2px;
            font-size: 11px;
            padding-left: 6px;
        }

        .empty {
            text-align: center;
            font-size: 12px;
            padding: 10px 0;
        }

        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 11px;
        }

        .no-print {
            text-align: center;
            margin-top: 16px;
        }

        .no-print button {
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            background: #2c3e50;
            color: #fff;
            cursor: pointer;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $sortedItems = $foodItems->sortBy(function ($detail) {
            $category = strtolower(trim($detail->product?->category?->name ?? ''));

            if (str_contains($category, 'ceviche')) {
                return '1-' . ($detail->product?->name ?? '');
            }

            return '2-' . ($detail->product?->name ?? '');
        })->values();

        $dineInItems = $sortedItems->where('service_type', 'dine_in')->values();
        $takeawayItems = $sortedItems->where('service_type', 'takeaway')->values();
    @endphp
    <div class="ticket">
        <div class="header">
            <h1>COCINA</h1>
            <div>{{ $printLabel }}</div>
            <div>Cevicheria Los Pepes</div>
        </div>

        <div class="meta">
            <div class="meta-row">
                <span><strong>Pedido:</strong> #{{ $printReference ?? $order->display_number }}</span>
                <span><strong>Mesa:</strong> {{ $order->table_label }}</span>
            </div>
            <div class="meta-row">
                <span><strong>Mesero:</strong> {{ $order->user->name }}</span>
                <span><strong>Hora:</strong> {{ $printedAt->format('d/m H:i') }}</span>
            </div>
        </div>

        <div class="items">
            @if($sortedItems->isEmpty())
                <div class="empty">
                    No hay alimentos para imprimir en esta comanda.
                </div>
            @else
                @if($dineInItems->isNotEmpty())
                    <div class="notes" style="padding-left:0; margin-bottom:8px; font-size:13px;">
                        <strong>PARA MESA</strong>
                    </div>
                    @foreach($dineInItems as $detail)
                        <div class="item">
                            <div class="item-line">
                                <span>{{ $detail->quantity }} x {{ $detail->product?->name ?? 'Producto' }}</span>
                            </div>
                            @if($detail->notes)
                                <div class="notes">{{ $detail->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif

                @if($takeawayItems->isNotEmpty())
                    @if($dineInItems->isNotEmpty())
                        <div style="border-top:1px dashed #000; margin:10px 0;"></div>
                    @endif
                    <div class="notes" style="padding-left:0; margin-bottom:8px; font-size:13px;">
                        <strong>PARA LLEVAR</strong>
                    </div>
                    @foreach($takeawayItems as $detail)
                        <div class="item">
                            <div class="item-line">
                                <span>{{ $detail->quantity }} x {{ $detail->product?->name ?? 'Producto' }}</span>
                            </div>
                            @if($detail->notes)
                                <div class="notes">{{ $detail->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                @endif
            @endif
        </div>

        <div class="footer">
            @if($scope === 'added')
                Solo se imprimen los alimentos agregados en la ultima modificacion.
            @else
                Comanda completa de cocina. Las bebidas no se incluyen.
            @endif
        </div>

        <div class="no-print">
            <button type="button" id="printKitchenTicket">Imprimir</button>
        </div>
    </div>
    <script>
        const waiterDashboardUrl = @json(route('waiter.dashboard'));
        const autoCloseAfterPrint = @json($autoCloseAfterPrint ?? false);
        let hasHandledPrintReturn = false;

        function returnToWaiterDashboard() {
            if (hasHandledPrintReturn) {
                return;
            }

            hasHandledPrintReturn = true;

            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location = waiterDashboardUrl;
                    window.opener.focus();
                    window.close();
                    return;
                } catch (error) {
                    // If the opener cannot be controlled, fall back to redirecting this tab.
                }
            }

            window.location.href = waiterDashboardUrl;
        }

        document.getElementById('printKitchenTicket')?.addEventListener('click', function () {
            hasHandledPrintReturn = false;
            window.print();
        });

        if (autoCloseAfterPrint) {
            window.addEventListener('afterprint', returnToWaiterDashboard);
        }
    </script>
</body>
</html>
