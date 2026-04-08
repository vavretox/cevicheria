@extends('layouts.app')

@section('title', 'Vista de Mesas')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('styles')
<style>
    .table-board {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 14px;
    }

    .table-card {
        border: 2px solid #dfe7ef;
        border-radius: 14px;
        padding: 14px;
        background: #fff;
    }

    .table-card.available {
        background: #ffffff;
        border-color: #dbe4ee;
    }

    .table-card.reserved {
        background: #fff8e8;
        border-color: #facc15;
    }

    .table-card.occupied {
        background: #fee2e2;
        border-color: #ef4444;
    }

    .table-card.closed {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .table-card-status {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="mb-4">
    <h2><i class="fas fa-table-cells-large me-2"></i>Vista de Mesas</h2>
    <p class="text-muted mb-0">Panel informativo para caja con estado, reservas y pedido activo por mesa.</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-board">
            @foreach($tableBoard as $table)
                @php
                    $status = $table->ui_status;
                    $activeOrder = $table->activeOrders->first();
                @endphp
                <div class="table-card {{ $status }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>{{ $table->name }}</strong>
                        <span class="table-card-status">
                            @if($status === 'available')
                                Libre
                            @elseif($status === 'reserved')
                                Reservada
                            @elseif($status === 'occupied')
                                Ocupada
                            @else
                                Cerrada
                            @endif
                        </span>
                    </div>
                    @if($table->zone)
                        <div class="small text-muted mb-1">{{ $table->zone }}</div>
                    @endif
                    @if($table->isReserved())
                        <div class="small text-muted"><strong>Reserva:</strong> {{ $table->reservation_name }}</div>
                        <div class="small text-muted mb-2">{{ $table->reservation_at?->format('d/m H:i') }}</div>
                    @endif
                    @if($activeOrder)
                        <div class="small"><strong>Pedido:</strong> #{{ $activeOrder->display_number }}</div>
                        <div class="small"><strong>Mesero:</strong> {{ $activeOrder->user?->name ?? '-' }}</div>
                        <div class="small"><strong>Total:</strong> Bs. {{ number_format($activeOrder->total, 2) }}</div>
                    @else
                        <div class="small text-muted">Sin pedido activo</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
