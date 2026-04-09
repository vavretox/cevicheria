@extends('layouts.app')

@section('title', 'Gestion de Mesas')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('styles')
<style>
    .table-board {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }

    .table-card {
        border-radius: 16px;
        padding: 16px;
        border: 2px solid #e5e7eb;
        background: #fff;
    }

    .table-card.available {
        background: #ffffff;
        border-color: #dbe4ee;
    }

    .table-card.reserved {
        background: #fff7e6;
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

    .table-card-title {
        font-size: 1.05rem;
        font-weight: 700;
    }

    .table-merge-card {
        border: 1px dashed #94a3b8;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        border-radius: 18px;
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

    .merge-summary {
        border-top: 1px dashed #cbd5e1;
        margin-top: 10px;
        padding-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-table-cells-large me-2"></i>Gestion de Mesas</h2>
        <p class="text-muted mb-0">Tablero visual, reservas y configuracion basica del salon.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTableModal">
        <i class="fas fa-plus me-2"></i>Nueva Mesa
    </button>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Libres</div>
                <div class="fs-3 fw-bold">{{ $tables->where('ui_status', 'available')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Reservadas</div>
                <div class="fs-3 fw-bold">{{ $tables->where('ui_status', 'reserved')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Ocupadas</div>
                <div class="fs-3 fw-bold">{{ $tables->where('ui_status', 'occupied')->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cerradas</div>
                <div class="fs-3 fw-bold">{{ $tables->where('ui_status', 'closed')->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 table-merge-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h5 class="mb-1"><i class="fas fa-object-group me-2"></i>Fusion de Mesas</h5>
                <p class="text-muted mb-0">Prueba operativa: una mesa principal puede absorber mesas libres para atender grupos grandes y mostrarse como una sola unidad.</p>
            </div>
        </div>
        <form action="{{ route('admin.tables.merge') }}" method="POST" class="row g-3 align-items-end mt-1">
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
                <small class="text-muted">Si ya tiene un pedido activo, esa mesa queda como la cabecera del grupo.</small>
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
                <small class="text-muted">Para esta primera version, solo se agregan mesas sin pedido activo.</small>
            </div>
            <div class="col-lg-2 d-grid">
                <button type="submit" class="btn btn-dark">
                    <i class="fas fa-link me-2"></i>Fusionar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chess-board me-2"></i>Tablero del Salon</h5>
    </div>
    <div class="card-body">
        <div class="table-board">
            @forelse($tables as $table)
                <div class="table-card {{ $table->ui_status }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="table-card-title">{{ $table->merged_display_name }}</div>
                        <span class="badge bg-light text-dark">
                            @if($table->ui_status === 'available')
                                Libre
                            @elseif($table->ui_status === 'reserved')
                                Reservada
                            @elseif($table->ui_status === 'occupied')
                                Ocupada
                            @else
                                Cerrada
                            @endif
                        </span>
                    </div>
                    <div class="small text-muted mb-1">{{ $table->zone ?: 'Salon principal' }}</div>
                    <div class="small text-muted mb-2">Capacidad total: {{ $table->combined_capacity ?: '-' }}</div>
                    @if($table->hasMergedChildren())
                        <div class="merge-members">
                            @foreach($table->merged_members as $member)
                                <span class="merge-chip">{{ $member->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($table->isReserved())
                        <div class="small"><strong>Reserva:</strong> {{ $table->reservation_name }}</div>
                        <div class="small text-muted">{{ $table->reservation_at?->format('d/m/Y H:i') }}</div>
                    @endif
                    @if($table->group_reservation_summary && !$table->isReserved())
                        <div class="small text-muted mt-1"><strong>Reservas del grupo:</strong> {{ $table->group_reservation_summary }}</div>
                    @endif
                    @if($table->reservation_notes)
                        <div class="small text-muted mt-1">{{ $table->reservation_notes }}</div>
                    @endif
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button
                            class="btn btn-sm btn-outline-dark"
                            onclick='showTableActivity({{ $table->id }}, @json($table->merged_display_name))'
                        >
                            <i class="fas fa-list"></i>
                        </button>
                        <button
                            class="btn btn-sm btn-outline-primary"
                            onclick='editTable({{ $table->id }}, @json($table->name), @json($table->zone), {{ $table->capacity ? $table->capacity : 'null' }}, {{ $table->active ? 'true' : 'false' }})'
                        >
                            <i class="fas fa-pen"></i>
                        </button>
                        <button
                            class="btn btn-sm btn-outline-warning"
                            onclick='editReservation({{ $table->id }}, @json($table->name), @json($table->reservation_name), @json(optional($table->reservation_at)->format('Y-m-d\\TH:i')), @json($table->reservation_notes))'
                        >
                            <i class="fas fa-calendar-check"></i>
                        </button>
                        @if($table->hasMergedChildren())
                            <form action="{{ route('admin.tables.unmerge', $table->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-muted">No hay mesas registradas.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Mesa</th>
                        <th>Zona</th>
                        <th>Capacidad</th>
                        <th>Estado</th>
                        <th>Reserva</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tables as $table)
                    <tr>
                        <td>
                            <strong>{{ $table->merged_display_name }}</strong>
                            @if($table->hasMergedChildren())
                                <div class="small text-muted">Fusionada: {{ $table->merged_members->pluck('name')->join(', ') }}</div>
                            @endif
                        </td>
                        <td>{{ $table->zone ?: '-' }}</td>
                        <td>{{ $table->combined_capacity ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $table->ui_status === 'available' ? 'bg-success' : ($table->ui_status === 'reserved' ? 'bg-warning text-dark' : ($table->ui_status === 'occupied' ? 'bg-primary' : 'bg-secondary')) }}">
                                @if($table->ui_status === 'available')
                                    Libre
                                @elseif($table->ui_status === 'reserved')
                                    Reservada
                                @elseif($table->ui_status === 'occupied')
                                    Ocupada
                                @else
                                    Cerrada
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($table->isReserved())
                                <div>{{ $table->reservation_name }}</div>
                                <small class="text-muted">{{ $table->reservation_at?->format('d/m/Y H:i') }}</small>
                            @else
                                <span class="text-muted">Sin reserva</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button
                                class="btn btn-sm btn-outline-dark"
                                onclick='showTableActivity({{ $table->id }}, @json($table->merged_display_name))'
                                title="Ver pedidos y movimientos"
                            >
                                <i class="fas fa-list"></i>
                            </button>
                            <button
                                class="btn btn-sm btn-warning"
                                onclick='editTable({{ $table->id }}, @json($table->name), @json($table->zone), {{ $table->capacity ? $table->capacity : 'null' }}, {{ $table->active ? 'true' : 'false' }})'
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                class="btn btn-sm btn-outline-warning"
                                onclick='editReservation({{ $table->id }}, @json($table->name), @json($table->reservation_name), @json(optional($table->reservation_at)->format('Y-m-d\\TH:i')), @json($table->reservation_notes))'
                            >
                                <i class="fas fa-calendar-check"></i>
                            </button>
                            @if($table->hasMergedChildren())
                            <form action="{{ route('admin.tables.unmerge', $table->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Separar mesas">
                                    <i class="fas fa-unlink"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.tables.delete', $table->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Eliminar esta mesa?')"
                                        {{ $table->orders_count > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-table-cells-large fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">Aun no hay mesas registradas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.tables.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Mesa 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Zona</label>
                        <input type="text" name="zone" class="form-control" placeholder="Ej: Terraza">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacidad</label>
                        <input type="number" name="capacity" class="form-control" min="1" placeholder="Ej: 4">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" id="createTableActive" value="1" checked>
                        <label class="form-check-label" for="createTableActive">Mesa habilitada</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTableForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" id="editTableName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Zona</label>
                        <input type="text" name="zone" id="editTableZone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Capacidad</label>
                        <input type="number" name="capacity" id="editTableCapacity" class="form-control" min="1">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" id="editTableActive" value="1">
                        <label class="form-check-label" for="editTableActive">Mesa habilitada</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reservationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Reserva de Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reservationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="mb-3 text-muted">Configura o limpia la reserva de <strong id="reservationTableName"></strong>.</p>
                    <div class="mb-3">
                        <label class="form-label">Nombre de reserva</label>
                        <input type="text" name="reservation_name" id="reservationName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha y hora</label>
                        <input type="datetime-local" name="reservation_at" id="reservationAt" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="reservation_notes" id="reservationNotes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearReservationFields()">Limpiar reserva</button>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="tableActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list me-2"></i>Actividad de Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="activityTableTitle" class="mb-3"></h6>
                <div id="tableActivityContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function editTable(id, name, zone, capacity, active) {
    const form = document.getElementById('editTableForm');
    form.action = '{{ url("admin/tables") }}/' + id;
    document.getElementById('editTableName').value = name;
    document.getElementById('editTableZone').value = zone ?? '';
    document.getElementById('editTableCapacity').value = capacity ?? '';
    document.getElementById('editTableActive').checked = !!active;

    const modal = new bootstrap.Modal(document.getElementById('editTableModal'));
    modal.show();
}

function editReservation(id, tableName, reservationName, reservationAt, reservationNotes) {
    const form = document.getElementById('reservationForm');
    form.action = '{{ url("admin/tables") }}/' + id + '/reservation';
    document.getElementById('reservationTableName').textContent = tableName;
    document.getElementById('reservationName').value = reservationName ?? '';
    document.getElementById('reservationAt').value = reservationAt ?? '';
    document.getElementById('reservationNotes').value = reservationNotes ?? '';

    const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
    modal.show();
}

function clearReservationFields() {
    document.getElementById('reservationName').value = '';
    document.getElementById('reservationAt').value = '';
    document.getElementById('reservationNotes').value = '';
}

function showTableActivity(id, tableName) {
    const modalEl = document.getElementById('tableActivityModal');
    const modal = new bootstrap.Modal(modalEl);
    document.getElementById('activityTableTitle').textContent = tableName;
    document.getElementById('tableActivityContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;

    modal.show();

    fetch('{{ url("admin/tables") }}/' + id + '/activity')
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudo cargar la actividad de la mesa.');
            }
            return response.json();
        })
        .then(data => {
            const ordersHtml = data.orders.length
                ? `
                    <div class="table-responsive mb-4">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Fecha</th>
                                    <th>Mesero</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.orders.map(order => `
                                    <tr>
                                        <td>#${order.id}</td>
                                        <td>${order.created_at}</td>
                                        <td>${order.waiter}</td>
                                        <td>Bs. ${order.total}</td>
                                        <td>${order.status}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `
                : '<p class="text-muted mb-4">No hay pedidos asociados a esta mesa.</p>';

            const movementsHtml = data.movements.length
                ? `
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Movimiento</th>
                                    <th>Detalle</th>
                                    <th>Usuario</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.movements.map(movement => `
                                    <tr>
                                        <td>#${movement.order_id}</td>
                                        <td>${movement.direction}</td>
                                        <td>${movement.description}</td>
                                        <td>${movement.user}</td>
                                        <td>${movement.created_at}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `
                : '<p class="text-muted mb-0">No hay cambios de mesa registrados para esta mesa.</p>';

            document.getElementById('tableActivityContent').innerHTML = `
                <div class="mb-3">
                    <h6 class="mb-2">Pedidos de la mesa</h6>
                    ${ordersHtml}
                </div>
                <div>
                    <h6 class="mb-2">Historial de cambios de mesa</h6>
                    ${movementsHtml}
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('tableActivityContent').innerHTML = `
                <div class="alert alert-danger mb-0">${error.message}</div>
            `;
        });
}
</script>
@endsection
