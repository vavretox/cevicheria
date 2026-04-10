?@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Reporte de Ventas</h2>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cashier.sales') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="date_from" class="form-control" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="date_to" class="form-control" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('cashier.sales') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Limpiar
                    </a>
                    <a href="{{ route('cashier.sales.print', ['type' => 'day', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
                       class="btn btn-outline-dark" target="_blank" rel="noopener">
                        <i class="fas fa-print me-2"></i>Imprimir Día
                    </a>
                    <a href="{{ route('cashier.sales.print', ['type' => 'month', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
                       class="btn btn-outline-dark" target="_blank" rel="noopener">
                        <i class="fas fa-print me-2"></i>Imprimir Mes
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Ventas</h6>
                <h2 class="text-success mb-0">Bs. {{ number_format($totalSales, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Pedidos Completados</h6>
                <h2 class="text-primary mb-0">{{ $orders->total() }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Ventas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Pago</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->display_number }}</strong></td>
                        <td>{{ $order->completed_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->table_label }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->cashier ? $order->cashier->name : '-' }}</td>
                        <td>{{ $order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR') }}</td>
                        <td><strong class="text-success">Bs. {{ number_format($order->total, 2) }}</strong></td>
                        <td>
                            <a href="{{ route('cashier.print-receipt', $order->id) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-print"></i>
                            </a>
                            <form action="{{ route('cashier.revert-order', $order->id) }}" method="POST" class="d-inline revert-form">
                                @csrf
                                <input type="hidden" name="reason" value="">
                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                        onclick="return confirmRevert(this)">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No hay ventas registradas en este período</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@if(!empty($audits) && $audits->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Auditoría Reciente</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
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

@section('scripts')
<script>
function confirmRevert(button) {
    const reason = prompt('Motivo de la reversión:');
    if (!reason) {
        return false;
    }
    const form = button.closest('form');
    form.querySelector('input[name=\"reason\"]').value = reason;
    return confirm('¿Revertir la venta a pendiente?');
}
</script>
@endsection






