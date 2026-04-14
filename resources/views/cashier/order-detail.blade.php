@extends('layouts.app')

@section('title', 'Procesar Pedido')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('styles')
<style>
    .product-detail-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
    }

    .table-transfer-card {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border: 1px solid #fdba74;
        border-radius: 14px;
        padding: 14px 16px;
        box-shadow: inset 0 0 0 1px rgba(251, 146, 60, 0.08);
    }

    .table-transfer-card .form-label {
        color: #9a3412;
        font-weight: 700;
    }

    .table-transfer-help {
        color: #9a3412;
        font-size: 0.85rem;
    }

    .audit-scroll {
        max-height: 420px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .audit-entry {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px 14px;
        background: #fff;
    }

    .audit-entry + .audit-entry {
        margin-top: 10px;
    }

    .audit-detail-list {
        margin: 8px 0 0;
        padding-left: 18px;
    }

    .audit-detail-list li {
        margin-bottom: 4px;
        font-size: 0.88rem;
        color: #475569;
    }

    .stock-warning-banner {
        border: 1px solid #fbbf24;
        background: #fffbeb;
        color: #92400e;
        border-radius: 14px;
        padding: 12px 14px;
    }
</style>
@endsection

@section('content')
@php
    $lowStockProducts = $products
        ->filter(fn ($product) => (int) $product->stock < 10)
        ->sortBy('name')
        ->values();
@endphp

<div class="mb-4">
    <a href="{{ route('cashier.dashboard') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    <h2><i class="fas fa-receipt me-2"></i>Pedido #{{ $order->display_number }}</h2>
    <div class="mt-2">
        <span class="badge {{ $order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary') }}">
            {{ $order->service_mode_label }}
        </span>
    </div>
</div>

@if($lowStockProducts->isNotEmpty())
<div class="stock-warning-banner mb-4">
    <div class="fw-semibold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Advertencia de stock bajo</div>
    <div class="small">
        @foreach($lowStockProducts as $product)
            <span class="me-3 d-inline-block">{{ $product->name }}: {{ $product->stock }} unid.</span>
        @endforeach
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        @if($order->status === 'pending')
                            <div class="table-transfer-card mb-3">
                                <label class="form-label">Mesa</label>
                                <div class="small table-transfer-help mb-2">
                                    Usa este bloque para transferir el pedido a otra mesa disponible.
                                </div>
                                <select class="form-select form-select-sm" id="editTableSelect">
                                    @foreach($selectableTables as $table)
                                        <option value="{{ $table->id }}" {{ $order->table_id === $table->id ? 'selected' : '' }}>
                                            {{ $table->merged_display_name }}{{ !$table->active ? ' (cerrada)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tipo de pedido</label>
                                <div class="small text-muted mb-2">Si cambias líneas al otro tipo, se moverán a un pedido hermano de la misma mesa al guardar.</div>
                                <div>
                                    <span class="badge {{ $order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary') }}">
                                        {{ $order->service_mode_label }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Mesero</label>
                                <select class="form-select form-select-sm" id="editWaiter">
                                    @foreach($waiters as $waiter)
                                        <option value="{{ $waiter->id }}" {{ $order->user_id === $waiter->id ? 'selected' : '' }}>
                                            {{ $waiter->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <p><strong>Mesa:</strong> {{ $order->table_label }}</p>
                            <p><strong>Mesero:</strong> {{ $order->user->name }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
                        <p><strong>Hora:</strong> {{ $order->created_at->format('H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Productos del Pedido</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                                <th>Notas</th>
                                @if($order->status === 'pending')
                                <th class="text-end">Quitar</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="cashierOrderItemsBody">
                            @foreach($order->details as $detail)
                            <tr data-item-key="detail-{{ $detail->id }}" data-product-id="{{ $detail->product_id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $detail->product->image_url }}" 
                                             class="product-detail-image me-3"
                                             onerror="this.src='https://via.placeholder.com/80?text=?'">
                                        <div>
                                            <strong>{{ $detail->product->name }}</strong>
                                            @if($detail->notes)
                                            <br><small class="text-muted">Nota: {{ $detail->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    @if($order->status === 'pending')
                                        <select class="form-select form-select-sm service-type-input" data-item-key="detail-{{ $detail->id }}">
                                            <option value="dine_in" {{ $detail->service_type === 'dine_in' ? 'selected' : '' }}>En mesa</option>
                                            <option value="takeaway" {{ $detail->service_type === 'takeaway' ? 'selected' : '' }}>Para llevar</option>
                                        </select>
                                    @else
                                        <span class="badge {{ $detail->service_type === 'takeaway' ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $detail->service_type_label }}</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($order->status === 'pending')
                                        <input type="number" class="form-control form-control-sm text-center qty-input"
                                               min="0" value="{{ $detail->quantity }}"
                                               data-item-key="detail-{{ $detail->id }}">
                                    @else
                                        <span class="badge bg-primary fs-6">{{ $detail->quantity }}</span>
                                    @endif
                                </td>
                                <td class="text-end align-middle">
                                    @if($order->status === 'pending')
                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end price-input"
                                               value="{{ number_format($detail->unit_price, 2, '.', '') }}"
                                               data-item-key="detail-{{ $detail->id }}">
                                    @else
                                        Bs. {{ number_format($detail->unit_price, 2) }}
                                    @endif
                                </td>
                                <td class="text-end align-middle">
                                    <strong class="row-subtotal">Bs. {{ number_format($detail->subtotal, 2) }}</strong>
                                </td>
                                <td class="align-middle">
                                    @if($order->status === 'pending')
                                        <input type="text" class="form-control form-control-sm note-input"
                                               value="{{ $detail->notes }}" data-item-key="detail-{{ $detail->id }}">
                                    @else
                                        <span class="text-muted">{{ $detail->notes ?? '-' }}</span>
                                    @endif
                                </td>
                                @if($order->status === 'pending')
                                <td class="text-end align-middle">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                                            data-item-key="detail-{{ $detail->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($order->status === 'pending')
                <hr>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Agregar producto</label>
                        <select class="form-select form-select-sm" id="addProductSelect">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} (Bs. {{ number_format($product->price, 2) }}){{ (int) $product->stock < 10 ? ' - Stock: ' . $product->stock : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control form-control-sm" id="addProductQty" min="1" value="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select form-select-sm" id="addProductServiceType">
                            <option value="dine_in">En mesa</option>
                            <option value="takeaway">Para llevar</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-sm btn-primary w-100" id="addProductBtn">
                            <i class="fas fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end flex-wrap gap-2">
                    <a href="{{ route('cashier.print-kitchen-order', ['id' => $order->id, 'scope' => 'main']) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-print me-1"></i>Imprimir cocina
                    </a>
                    @if($canPrintAdded)
                        <a href="{{ route('cashier.print-kitchen-order', ['id' => $order->id, 'scope' => 'added']) }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-layer-group me-1"></i>Imprimir recién agregados
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                            <i class="fas fa-layer-group me-1"></i>Imprimir recién agregados
                        </button>
                    @endif
                    <button type="button" class="btn btn-success btn-sm" id="saveOrderChanges">
                        <i class="fas fa-save me-1"></i>Guardar cambios
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @if(!empty($auditEntries) && $auditEntries->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Cambios</h5>
            </div>
            <div class="card-body">
                <div class="audit-scroll">
                    @foreach($auditEntries as $entry)
                        <div class="audit-entry">
                            <div class="d-flex justify-content-between gap-3">
                                <strong>{{ $entry['title'] }}</strong>
                                <small class="text-muted">{{ $entry['date'] }}</small>
                            </div>
                            <div class="text-muted small">
                                {{ $entry['user'] }}
                            </div>
                            @if(!empty($entry['details']))
                                <ul class="audit-detail-list">
                                    @foreach($entry['details'] as $detail)
                                        <li>{{ $detail }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <small class="text-muted d-block mt-2">Sin detalles adicionales.</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Subtotal:</span>
                    <strong id="summarySubtotal">Bs. {{ number_format($order->subtotal, 2) }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <h5><strong>Total:</strong></h5>
                    <h4><strong class="text-success" id="summaryTotal">Bs. {{ number_format($order->total, 2) }}</strong></h4>
                </div>

                @if($order->status === 'pending')
                <form action="{{ route('cashier.process-order', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Metodo de pago</label>
                        <select name="payment_method" class="form-select" id="paymentMethodSelect" required>
                            <option value="cash">Efectivo</option>
                            <option value="qr">QR</option>
                            <option value="mixed">Efectivo + QR</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            Si el pago es solo en efectivo o solo por QR, se cobrara automaticamente el total del pedido.
                        </small>
                    </div>
                    <div class="row g-2 mb-3" id="mixedAmountGroup" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label">Pago en efectivo</label>
                            <input type="number" name="cash_paid_amount" step="0.01" min="0" class="form-control" id="cashPaidAmountInput" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pago por QR</label>
                            <input type="number" name="qr_paid_amount" step="0.01" min="0" class="form-control" id="qrPaidAmountInput" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <small class="text-muted">La suma de ambos montos debe ser igual al total del pedido.</small>
                        </div>
                    </div>
                    <a href="{{ route('cashier.print-receipt', $order->id) }}"
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-print me-2"></i>Imprimir
                    </a>
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-check-circle me-2"></i>Procesar y Cobrar
                    </button>
                </form>
                <form action="{{ route('cashier.cancel-order', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('¿Cancelar este pedido?')">
                        <i class="fas fa-times me-2"></i>Cancelar Pedido
                    </button>
                </form>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Este pedido ya fue procesado
                </div>
                <div class="mb-3 small">
                    <div><strong>Metodo de pago:</strong> {{ $order->payment_method === 'cash' ? 'Efectivo' : ($order->payment_method === 'mixed' ? 'Efectivo + QR' : 'QR') }}</div>
                    @if($order->payment_method === 'cash')
                        <div><strong>Efectivo:</strong> Bs. {{ number_format($order->cash_paid_amount ?? $order->total ?? 0, 2) }}</div>
                    @elseif($order->payment_method === 'qr')
                        <div><strong>QR:</strong> Bs. {{ number_format($order->qr_paid_amount ?? $order->total ?? 0, 2) }}</div>
                    @elseif($order->payment_method === 'mixed')
                        <div><strong>Efectivo:</strong> Bs. {{ number_format($order->cash_paid_amount ?? 0, 2) }}</div>
                        <div><strong>QR:</strong> Bs. {{ number_format($order->qr_paid_amount ?? 0, 2) }}</div>
                    @endif
                </div>
                <a href="{{ route('cashier.print-receipt', $order->id) }}" 
                   class="btn btn-primary w-100">
                    <i class="fas fa-print me-2"></i>Imprimir Boleta
                </a>
                <form action="{{ route('cashier.revert-order', $order->id) }}" method="POST" class="mt-2">
                    @csrf
                    <div class="mb-2">
                        <textarea name="reason" class="form-control" rows="2" required
                                  placeholder="Motivo de la reversión"></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-warning w-100"
                            onclick="return confirm('¿Revertir la venta a pendiente?')">
                        <i class="fas fa-undo me-2"></i>Revertir Venta
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cashierFoodNotesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list me-2"></i>Indicaciones del producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong id="cashierFoodNotesProductName"></strong>
                    <span class="text-muted d-block small">Marca las indicaciones para este alimento.</span>
                </p>
                <div class="d-grid gap-2">
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="SIN PLATANO">
                        <span class="form-check-label">SIN PLATANO</span>
                    </label>
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="SIN CHOLO">
                        <span class="form-check-label">SIN CHOLO</span>
                    </label>
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="SIN CEBOLLA">
                        <span class="form-check-label">SIN CEBOLLA</span>
                    </label>
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="SIN CILANTRO">
                        <span class="form-check-label">SIN CILANTRO</span>
                    </label>
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="SIN LECHUGA">
                        <span class="form-check-label">SIN LECHUGA</span>
                    </label>
                    <label class="form-check border rounded p-3 cashier-food-note-option">
                        <input class="form-check-input cashier-food-note-input" type="checkbox" value="COMPLETO" id="cashierFoodNoteCompleteOption">
                        <span class="form-check-label">COMPLETO</span>
                    </label>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-uppercase">Tipo de servicio</label>
                    <select class="form-select form-select-sm" id="cashierFoodServiceType">
                        <option value="dine_in">En mesa</option>
                        <option value="takeaway">Para llevar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmCashierFoodNotes">
                    <i class="fas fa-check me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@if($order->status === 'pending')
@php
    $cashierEditableItems = $order->details->map(function ($detail) {
        return [
            'item_key' => 'detail-' . $detail->id,
            'product_id' => (int) $detail->product_id,
            'product_name' => $detail->product?->name ?? 'Producto',
            'image_url' => $detail->product?->image_url,
            'quantity' => (int) $detail->quantity,
            'unit_price' => (float) $detail->unit_price,
            'notes' => $detail->notes ?? '',
            'service_type' => $detail->service_type ?? 'dine_in',
            'service_type_label' => $detail->service_type_label,
        ];
    })->values();
@endphp
@section('scripts')
<script>
const productsCatalog = @json($products);
@include('orders._editor-utils')
const orderEditorUtils = window.orderEditorUtils;
const cashierFoodNotesModal = new bootstrap.Modal(document.getElementById('cashierFoodNotesModal'));
let currentItems = @json($cashierEditableItems);
let cashierDraftItemCounter = 0;
let pendingFoodProduct = null;
let isSavingOrderChanges = false;

function nextCashierItemKey() {
    cashierDraftItemCounter += 1;
    return `draft-${cashierDraftItemCounter}`;
}

function findCashierItem(itemKey) {
    return currentItems.find(item => item.item_key === itemKey);
}

function collectItems() {
    return currentItems.map(item => ({
        product_id: item.product_id,
        quantity: Math.max(0, parseInt(item.quantity, 10) || 0),
        unit_price: Number(item.unit_price || 0),
        notes: item.notes || '',
        service_type: item.service_type || 'dine_in'
    }));
}

function setSaveOrderChangesState(isSaving) {
    const saveButton = $('#saveOrderChanges');

    if (!saveButton.length) {
        return;
    }

    if (isSaving) {
        saveButton
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
        return;
    }

    saveButton
        .prop('disabled', false)
        .html('<i class="fas fa-save me-1"></i>Guardar cambios');
}

function recalcTotals() {
    const subtotal = currentItems.reduce((sum, item) => {
        return sum + ((parseInt(item.quantity, 10) || 0) * (Number(item.unit_price) || 0));
    }, 0);

    $('#summarySubtotal').text('Bs. ' + subtotal.toFixed(2));
    $('#summaryTotal').text('Bs. ' + subtotal.toFixed(2));
}

function renderCashierItems() {
    const tbody = $('#cashierOrderItemsBody');
    if (!tbody.length) {
        recalcTotals();
        return;
    }

    const rows = currentItems.map(item => {
        const rowSubtotal = (parseInt(item.quantity, 10) || 0) * (Number(item.unit_price) || 0);
        const serviceType = item.service_type || 'dine_in';
        const imageUrl = item.image_url || 'https://via.placeholder.com/80?text=?';

        return `
            <tr data-item-key="${item.item_key}" data-product-id="${item.product_id}">
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${imageUrl}"
                             class="product-detail-image me-3"
                             onerror="this.src='https://via.placeholder.com/80?text=?'">
                        <div>
                            <strong>${item.product_name}</strong>
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <select class="form-select form-select-sm service-type-input" data-item-key="${item.item_key}">
                        <option value="dine_in" ${serviceType === 'dine_in' ? 'selected' : ''}>En mesa</option>
                        <option value="takeaway" ${serviceType === 'takeaway' ? 'selected' : ''}>Para llevar</option>
                    </select>
                </td>
                <td class="text-center align-middle">
                    <input type="number" class="form-control form-control-sm text-center qty-input"
                           min="0" value="${parseInt(item.quantity, 10) || 0}"
                           data-item-key="${item.item_key}">
                </td>
                <td class="text-end align-middle">
                    <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end price-input"
                           value="${(Number(item.unit_price) || 0).toFixed(2)}"
                           data-item-key="${item.item_key}">
                </td>
                <td class="text-end align-middle">
                    <strong class="row-subtotal">Bs. ${rowSubtotal.toFixed(2)}</strong>
                </td>
                <td class="align-middle">
                    <input type="text" class="form-control form-control-sm note-input"
                           value="${item.notes || ''}" data-item-key="${item.item_key}">
                </td>
                <td class="text-end align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item"
                            data-item-key="${item.item_key}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    tbody.html(rows);
    recalcTotals();
}

function addOrUpdateCashierItem(newItem) {
    orderEditorUtils.mergeOrderItem(currentItems, newItem, {
        includeUnitPriceInSignature: true,
        createItemKey: () => nextCashierItemKey(),
    });
}

function isFoodProduct(product) {
    return orderEditorUtils.isFoodProduct(product);
}

function resetCashierFoodNotesModal() {
    pendingFoodProduct = null;
    $('#cashierFoodNotesProductName').text('');
    $('.cashier-food-note-input').prop('checked', false);
    $('#cashierFoodServiceType').val($('#addProductServiceType').val() || 'dine_in');
    syncCashierFoodNoteStyles();
}

function getSelectedCashierFoodNotes() {
    return orderEditorUtils.getCheckedValues('.cashier-food-note-input');
}

function getSelectedCashierFoodServiceType() {
    return orderEditorUtils.normalizeServiceType($('#cashierFoodServiceType').val());
}

function syncCashierFoodNoteStyles() {
    orderEditorUtils.syncToggleCardState('.cashier-food-note-option', '.cashier-food-note-input');
}

$(document).on('input change', '.qty-input', function() {
    const item = findCashierItem($(this).data('item-key'));
    if (!item) {
        return;
    }

    item.quantity = Math.max(0, parseInt($(this).val(), 10) || 0);
    const rowSubtotal = item.quantity * (Number(item.unit_price) || 0);
    $(this).closest('tr').find('.row-subtotal').text('Bs. ' + rowSubtotal.toFixed(2));
    recalcTotals();
});

$(document).on('input change', '.price-input', function() {
    const item = findCashierItem($(this).data('item-key'));
    if (!item) {
        return;
    }

    item.unit_price = Math.max(0, Number($(this).val()) || 0);
    const rowSubtotal = (parseInt(item.quantity, 10) || 0) * item.unit_price;
    $(this).closest('tr').find('.row-subtotal').text('Bs. ' + rowSubtotal.toFixed(2));
    recalcTotals();
});

$(document).on('input change', '.note-input', function() {
    const item = findCashierItem($(this).data('item-key'));
    if (!item) {
        return;
    }

    item.notes = $(this).val() || '';
});

$(document).on('change', '.service-type-input', function() {
    const item = findCashierItem($(this).data('item-key'));
    if (!item) {
        return;
    }

    item.service_type = $(this).val() || 'dine_in';
});

$(document).on('click', '.remove-item', function() {
    const itemKey = String($(this).data('item-key'));
    currentItems = currentItems.filter(item => item.item_key !== itemKey);
    renderCashierItems();
});

$('#cashierFoodNotesModal').on('hidden.bs.modal', function() {
    resetCashierFoodNotesModal();
});

$('.cashier-food-note-input').on('change', function() {
    const isComplete = this.value === 'COMPLETO';

    if (isComplete && this.checked) {
        $('.cashier-food-note-input').not(this).prop('checked', false);
    }

    if (!isComplete && this.checked) {
        $('#cashierFoodNoteCompleteOption').prop('checked', false);
    }

    syncCashierFoodNoteStyles();
});

$('#confirmCashierFoodNotes').on('click', function() {
    if (!pendingFoodProduct) {
        return;
    }

    addOrUpdateCashierItem({
        item_key: nextCashierItemKey(),
        product_id: pendingFoodProduct.product_id,
        product_name: pendingFoodProduct.product_name,
        image_url: pendingFoodProduct.image_url || '',
        quantity: pendingFoodProduct.quantity,
        unit_price: pendingFoodProduct.unit_price,
        notes: getSelectedCashierFoodNotes(),
        service_type: getSelectedCashierFoodServiceType(),
        service_type_label: orderEditorUtils.getServiceTypeLabel(getSelectedCashierFoodServiceType())
    });

    cashierFoodNotesModal.hide();
    renderCashierItems();
});

$('#addProductBtn').on('click', function() {
    const productId = parseInt($('#addProductSelect').val(), 10);
    const qty = parseInt($('#addProductQty').val(), 10);
    if (!qty || qty <= 0) return;

    const product = productsCatalog.find(p => p.id === productId);
    if (!product) {
        alert('Selecciona un producto valido.');
        return;
    }

    const stock = Number(product.stock || 0);
    if (stock <= 0) {
        alert(`${product.name} está agotado.`);
        return;
    }

    if (isFoodProduct(product)) {
        pendingFoodProduct = {
            product_id: productId,
            product_name: product.name || 'Producto',
            image_url: product.image_url || '',
            quantity: qty,
            unit_price: Number(product.price || 0),
        };
        $('#cashierFoodNotesProductName').text(pendingFoodProduct.product_name);
        $('.cashier-food-note-input').prop('checked', false);
        $('#cashierFoodServiceType').val($('#addProductServiceType').val() || 'dine_in');
        syncCashierFoodNoteStyles();
        cashierFoodNotesModal.show();
        return;
    }

    addOrUpdateCashierItem({
        item_key: nextCashierItemKey(),
        product_id: productId,
        product_name: product.name || 'Producto',
        image_url: product.image_url || '',
        quantity: qty,
        unit_price: Number(product.price || 0),
        notes: '',
        service_type: $('#addProductServiceType').val() || 'dine_in',
        service_type_label: orderEditorUtils.getServiceTypeLabel($('#addProductServiceType').val() || 'dine_in')
    });

    renderCashierItems();
});

$('#saveOrderChanges').on('click', function() {
    if (isSavingOrderChanges) {
        return;
    }

    isSavingOrderChanges = true;
    setSaveOrderChangesState(true);

    $.ajax({
        url: '{{ route("cashier.update-order-items", $order->id) }}',
        method: 'POST',
        headers: {
            'Accept': 'application/json',
        },
        data: {
            _token: '{{ csrf_token() }}',
            table_id: $('#editTableSelect').val(),
            waiter_id: $('#editWaiter').val(),
            items: collectItems()
        },
        success: function() {
            location.reload();
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message
                || Object.values(xhr.responseJSON?.errors || {}).flat().join('\n')
                || 'Error desconocido';
            alert('Error al actualizar: ' + message);
            isSavingOrderChanges = false;
            setSaveOrderChangesState(false);
        }
    });
});

renderCashierItems();

function syncPaymentFields() {
    const method = $('#paymentMethodSelect').val();
    const isMixed = method === 'mixed';
    $('#mixedAmountGroup').toggle(isMixed);
    $('#cashPaidAmountInput').prop('required', isMixed);
    $('#qrPaidAmountInput').prop('required', isMixed);
    if (!isMixed) {
        $('#cashPaidAmountInput, #qrPaidAmountInput').val('');
    }
}

$('#paymentMethodSelect').on('change', function() {
    syncPaymentFields();
});

syncPaymentFields();
</script>
@endsection
@endif








