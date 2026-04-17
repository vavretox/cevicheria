@extends('layouts.app')

@section('title', 'Caja y Arqueo')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2><i class="fas fa-cash-register me-2"></i>Caja y Arqueo</h2>
            <p class="text-muted mb-0">Supervisa aperturas, cierres y diferencias de caja por cajero.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cash-sessions.thermal-print', request()->only(['user_id', 'date'])) }}" class="btn btn-warning">
                <i class="fas fa-receipt me-2"></i>Impresión térmica
            </a>
            <a href="{{ route('admin.cash-sessions.print', request()->query()) }}" class="btn btn-outline-dark" target="_blank" rel="noopener">
                <i class="fas fa-print me-2"></i>Imprimir
            </a>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-4 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cajas abiertas</div>
                <div class="fs-3 fw-bold">{{ $summary['open_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cajas cerradas</div>
                <div class="fs-3 fw-bold">{{ $summary['closed_count'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Ventas en efectivo</div>
                <div class="fs-3 fw-bold">Bs. {{ number_format($summary['cash_sales_total'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Ventas por QR</div>
                <div class="fs-3 fw-bold">Bs. {{ number_format($summary['qr_sales_total'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Ventas registradas</div>
                <div class="fs-3 fw-bold">Bs. {{ number_format($summary['sales_total'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.cash-sessions') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Cajero</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ (string) request('user_id') === (string) $cashier->id ? 'selected' : '' }}>
                            {{ $cashier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Abierta</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="col-md-12 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
                <a href="{{ route('admin.cash-sessions') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
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
                        <td>
                            <span class="badge {{ $session->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                {{ $session->status === 'open' ? 'Abierta' : 'Cerrada' }}
                            </span>
                        </td>
                        <td>Bs. {{ number_format($session->opening_amount, 2) }}</td>
                        <td>Bs. {{ number_format($session->cash_sales_total, 2) }}</td>
                        <td>Bs. {{ number_format($session->qr_sales_total, 2) }}</td>
                        <td>Bs. {{ number_format($session->sales_total, 2) }}</td>
                        <td>Bs. {{ number_format($session->expected_balance, 2) }}</td>
                        <td class="fw-semibold {{ $session->counted_amount !== null ? 'text-primary' : 'text-muted' }}">
                            {{ $session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-' }}
                        </td>
                        <td>
                            <div><small>Apertura: {{ $session->opened_at?->format('d/m/Y H:i') }}</small></div>
                            <div><small>Cierre: {{ $session->closed_at?->format('d/m/Y H:i') ?? '-' }}</small></div>
                        </td>
                        <td>
                            @if($session->opening_note || $session->closing_note)
                                @if($session->opening_note)
                                    <div><small><strong>Apertura:</strong> {{ $session->opening_note }}</small></div>
                                @endif
                                @if($session->closing_note)
                                    <div><small><strong>Cierre:</strong> {{ $session->closing_note }}</small></div>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No hay sesiones de caja para mostrar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $sessions->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
