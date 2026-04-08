@extends('layouts.app')

@section('title', 'Reportes de Ventas')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
@php
    $query = request()->only(['date_from', 'date_to']);
@endphp

<style>
    .report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: end;
    }

    .report-stats .card {
        border: 0;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.07);
        border-radius: 18px;
    }

    .report-stats .label {
        color: #64748b;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .report-shell {
        border: 0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    }

    .report-shell .card-header {
        background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
        border-bottom: 1px solid #fed7aa;
        padding: 1.25rem 1.5rem;
    }

    .sale-summary {
        min-width: 260px;
    }

    .sale-meta {
        font-size: 0.9rem;
        color: #64748b;
    }

    .sale-detail-box {
        max-height: 120px;
        overflow-y: auto;
        padding-right: 0.35rem;
    }

    .sale-detail-box::-webkit-scrollbar {
        width: 6px;
    }

    .sale-detail-box::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .sale-detail-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.6rem 0.75rem;
    }

    .audit-table td,
    .audit-table th {
        vertical-align: middle;
    }

    .profitability-note {
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        border-radius: 16px;
        padding: 0.9rem 1rem;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Reportes de Ventas</h2>
        <p class="text-muted mb-0">Resumen ejecutivo con detalle completo por pedido vendido.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 22px;">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.reports') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fecha Desde</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fecha Hasta</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <div class="report-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left me-2"></i>Limpiar
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h me-2"></i>Más acciones
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.beverages.gain-print', $query) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-wine-bottle me-2 text-danger"></i>Reporte ganancia bebidas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.thermal-print', $query) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-receipt me-2 text-warning"></i>Impresión rápida térmica
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.print', array_merge(['type' => 'day'], $query)) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-print me-2 text-dark"></i>Imprimir por día
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.print', array_merge(['type' => 'month'], $query)) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-calendar-alt me-2 text-dark"></i>Imprimir por mes
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.pdf', array_merge(['type' => 'day'], $query)) }}">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i>Descargar PDF
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.xlsx', $query) }}">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Descargar XLSX
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.export', $query) }}">
                                    <i class="fas fa-file-csv me-2 text-success"></i>Descargar CSV
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4 report-stats">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4 text-center">
                <div class="label mb-2">Total Ventas</div>
                <div class="fs-2 fw-bold text-success">Bs. {{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4 text-center">
                <div class="label mb-2">Pedidos Completados</div>
                <div class="fs-2 fw-bold text-primary">{{ $totalOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-4 text-center">
                <div class="label mb-2">Ticket Promedio</div>
                <div class="fs-2 fw-bold text-info">
                    Bs. {{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00' }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bloque de productos más vendidos oculto temporalmente.
<div class="card border-0 shadow-sm mb-4" style="border-radius: 22px;">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h5 class="mb-1"><i class="fas fa-trophy me-2 text-warning"></i>Productos Más Vendidos</h5>
        <p class="text-muted mb-0">Los productos con mayor movimiento dentro del período filtrado.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Cantidad Vendida</th>
                        <th>Total Generado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $index => $product)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <i class="fas fa-trophy text-warning fa-lg"></i>
                                @elseif($index === 1)
                                    <i class="fas fa-medal text-secondary fa-lg"></i>
                                @elseif($index === 2)
                                    <i class="fas fa-medal text-danger fa-lg"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td>
                                <span class="badge text-bg-info">{{ $product->category->name }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-primary">{{ $product->total_sold }} unidades</span>
                            </td>
                            <td class="fw-semibold text-success">
                                Bs. {{ number_format($product->price * $product->total_sold, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay datos de productos vendidos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
--}}

@php
    $beverageSummary = $beverageGainReport['summary'];
    $beverageProducts = $beverageGainReport['products'];
@endphp

<div class="card border-0 shadow-sm mb-4" style="border-radius: 22px;">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h5 class="mb-1"><i class="fas fa-wine-bottle me-2 text-danger"></i>Ganancia de Bebidas</h5>
        <p class="text-muted mb-0">Comparación de compra vs venta por cada bebida usando el costo promedio registrado en almacén hasta la fecha final del filtro.</p>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <div class="label mb-2">Venta de Bebidas</div>
                        <div class="fs-4 fw-bold text-success">Bs. {{ number_format($beverageSummary['total_revenue'], 2) }}</div>
                        <div class="small text-muted">{{ $beverageSummary['total_units_sold'] }} unidades vendidas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <div class="label mb-2">Compra Estimada</div>
                        <div class="fs-4 fw-bold text-primary">Bs. {{ number_format($beverageSummary['estimated_cost'], 2) }}</div>
                        <div class="small text-muted">Sobre Bs. {{ number_format($beverageSummary['covered_revenue'], 2) }} con costo registrado</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <div class="label mb-2">Ganancia</div>
                        <div class="fs-4 fw-bold {{ $beverageSummary['estimated_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            Bs. {{ number_format($beverageSummary['estimated_profit'], 2) }}
                        </div>
                        <div class="small text-muted">
                            Margen:
                            {{ $beverageSummary['estimated_margin'] !== null ? number_format($beverageSummary['estimated_margin'], 2) . '%' : 'Sin base de costo' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <div class="label mb-2">Cobertura del Cálculo</div>
                        <div class="fs-4 fw-bold text-dark">{{ number_format($beverageSummary['coverage_percent'], 2) }}%</div>
                        <div class="small text-muted">{{ $beverageSummary['missing_cost_products'] }} productos y {{ $beverageSummary['missing_cost_units'] }} unidades sin costo</div>
                    </div>
                </div>
            </div>
        </div>

        @if($beverageSummary['missing_cost_products'] > 0)
            <div class="profitability-note mb-3">
                Algunas bebidas vendidas no tienen compras cargadas en almacén. En esos casos no se estima compra ni ganancia para evitar mostrar números engañosos.
            </div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-end">Venta Prom./Unid.</th>
                        <th class="text-end">Compra Prom./Unid.</th>
                        <th class="text-end">Total Venta</th>
                        <th class="text-end">Total Compra</th>
                        <th class="text-end">Ganancia</th>
                        <th class="text-end">Margen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beverageProducts as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->units_sold }}</td>
                            <td class="text-end">Bs. {{ number_format($item->average_sale_price, 2) }}</td>
                            <td class="text-end">
                                @if($item->has_cost_data)
                                    Bs. {{ number_format($item->average_unit_cost, 2) }}
                                @else
                                    <span class="badge text-bg-warning">Sin costo</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold text-success">Bs. {{ number_format($item->revenue, 2) }}</td>
                            <td class="text-end">
                                @if($item->has_cost_data)
                                    Bs. {{ number_format($item->estimated_cost, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item->has_cost_data)
                                    <span class="fw-semibold {{ $item->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                        Bs. {{ number_format($item->gross_profit, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item->margin !== null)
                                    {{ number_format($item->margin, 2) }}%
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No hay ventas de bebidas en el período seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card report-shell">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-1"><i class="fas fa-receipt me-2"></i>Detalle de Ventas</h5>
            <p class="text-muted mb-0">Cada venta muestra su cantidad total de productos y el desglose completo del pedido.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border">{{ $orders->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Venta</th>
                        <th>Pedido</th>
                        <th>Equipo</th>
                        <th>Detalle</th>
                        <th class="text-end pe-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-4 sale-summary">
                                <div class="fw-bold mb-1">#{{ $order->display_number }}</div>
                                <div class="sale-meta">{{ ($order->completed_at ?? $order->created_at)->format('d/m/Y H:i') }}</div>
                                <div class="sale-meta">Mesa: {{ $order->table_label ?? $order->table_number ?? 'Sin mesa' }}</div>
                                <div class="sale-meta">Pago: {{ $order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->details->sum('quantity') }} productos</div>
                                <div class="small text-muted">{{ $order->details->count() }} líneas registradas</div>
                            </td>
                            <td>
                                <div class="small"><span class="text-muted">Mesero:</span> {{ $order->user->name ?? '-' }}</div>
                                <div class="small"><span class="text-muted">Cajero:</span> {{ $order->cashier->name ?? '-' }}</div>
                            </td>
                            <td style="min-width: 340px;">
                                <div class="sale-detail-box d-grid gap-2">
                                    @foreach($order->details as $detail)
                                        <div class="sale-detail-item">
                                            <div class="d-flex justify-content-between gap-2">
                                                <div class="fw-semibold">
                                                    {{ $detail->quantity }} x {{ $detail->product->name ?? 'Producto eliminado' }}
                                                </div>
                                                <div class="text-success fw-semibold">Bs. {{ number_format($detail->subtotal, 2) }}</div>
                                            </div>
                                            <div class="small text-muted">
                                                Unitario: Bs. {{ number_format($detail->unit_price, 2) }}
                                            </div>
                                            @if(!empty($detail->notes))
                                                <div class="small text-muted">Nota: {{ $detail->notes }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="fw-bold fs-5 text-success">Bs. {{ number_format($order->total, 2) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No hay ventas en el período seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-top">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@if(!empty($audits) && $audits->count() > 0)
    <div class="card border-0 shadow-sm mt-4" style="border-radius: 22px;">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="mb-1"><i class="fas fa-history me-2"></i>Auditoría Reciente</h5>
            <p class="text-muted mb-0">Últimos movimientos registrados sobre pedidos y ventas.</p>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-sm audit-table mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Pedido</th>
                            <th>Acción</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($audits as $audit)
                            <tr>
                                <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                                <td>#{{ $audit->order_id }}</td>
                                <td>{{ ucfirst($audit->action) }}</td>
                                <td>{{ $audit->user ? $audit->user->name : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
