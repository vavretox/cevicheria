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

    .merge-members {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }

    .merge-chip {
        border-radius: 999px;
        padding: 4px 10px;
        background: #e2e8f0;
        color: #1e293b;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .table-merge-card {
        border: 1px dashed #94a3b8;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border-radius: 18px;
    }
</style>
@endsection

@section('content')
<div class="mb-4">
    <h2><i class="fas fa-table-cells-large me-2"></i>Vista de Mesas</h2>
    <p class="text-muted mb-0">Panel informativo para caja con estado, reservas y pedido activo por mesa.</p>
</div>

<div class="card mb-4 table-merge-card">
    <div class="card-body">
        <h5 class="mb-1"><i class="fas fa-plus-circle me-2"></i>Crear Mesa Desde Caja</h5>
        <p class="text-muted mb-3">Si falta una mesa en operación, el cajero puede registrarla directamente desde aquí.</p>
        <form action="{{ route('cashier.tables.store') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-lg-4">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" placeholder="Ej: Mesa 10" required>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Zona</label>
                <input type="text" name="zone" class="form-control" placeholder="Ej: Salon principal">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Capacidad</label>
                <input type="number" name="capacity" class="form-control" min="1" placeholder="4">
            </div>
            <div class="col-lg-2">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="active" id="cashierCreateTableActive" value="1" checked>
                    <label class="form-check-label" for="cashierCreateTableActive">Habilitada</label>
                </div>
            </div>
            <div class="col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4 table-merge-card">
    <div class="card-body">
        <h5 class="mb-1"><i class="fas fa-object-group me-2"></i>Fusionar Mesas Desde Caja</h5>
        <p class="text-muted mb-3">Cuando llegue un grupo grande, puedes unir mesas libres y operarlas como una sola desde caja.</p>
        <form action="{{ route('cashier.tables.merge') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-lg-4">
                <label class="form-label">Mesa principal</label>
                <select name="base_table_id" class="form-select" required>
                    <option value="">Selecciona una mesa</option>
                    @foreach($mergeableTables as $table)
                        <option value="{{ $table->id }}">
                            {{ $table->name }}{{ $table->zone ? ' - ' . $table->zone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="form-label">Mesas a sumar</label>
                <select name="merged_table_ids[]" class="form-select" multiple size="5" required>
                    @foreach($mergeableTables as $table)
                        <option value="{{ $table->id }}">
                            {{ $table->name }} | Cap. {{ $table->capacity ?: '-' }}{{ $table->active_orders_count ? ' | Ocupada' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Solo se agregarán mesas sin pedido activo.</small>
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-link me-2"></i>Fusionar
                </button>
            </div>
        </form>
    </div>
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
                        <strong>{{ $table->merged_display_name }}</strong>
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
                    <div class="small text-muted mb-2">Capacidad total: {{ $table->combined_capacity ?: '-' }}</div>
                    @if($table->hasMergedChildren())
                        <div class="merge-members">
                            @foreach($table->merged_members as $member)
                                <span class="merge-chip">{{ $member->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($table->isReserved())
                        <div class="small text-muted"><strong>Reserva:</strong> {{ $table->reservation_name }}</div>
                        <div class="small text-muted mb-2">{{ $table->reservation_at?->format('d/m H:i') }}</div>
                    @elseif($table->group_reservation_summary)
                        <div class="small text-muted mb-2"><strong>Reservas del grupo:</strong> {{ $table->group_reservation_summary }}</div>
                    @endif
                    @if($activeOrder)
                        <div class="small"><strong>Pedido:</strong> #{{ $activeOrder->display_number }}</div>
                        <div class="small"><strong>Mesero:</strong> {{ $activeOrder->user?->name ?? '-' }}</div>
                        <div class="small"><strong>Total:</strong> Bs. {{ number_format($activeOrder->total, 2) }}</div>
                    @else
                        <div class="small text-muted">Sin pedido activo</div>
                    @endif
                    @if($table->hasMergedChildren())
                        <form action="{{ route('cashier.tables.unmerge', $table->id) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-unlink me-1"></i>Desfusionar
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
