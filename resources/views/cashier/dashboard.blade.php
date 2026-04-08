@extends('layouts.app')

@section('title', 'Panel Cajero')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('styles')
<style>
    .order-card {
        cursor: pointer;
        transition: all 0.3s;
        border-left: 4px solid #f39c12;
    }

    .order-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
    }

    .stat-card {
        border-left: 4px solid;
        padding: 20px;
    }

    .stat-card.orders {
        border-left-color: #3498db;
    }

.stat-card.sales {
    border-left-color: #27ae60;
}
.quick-order {
    border: 2px dashed #e5e7eb;
}
.quick-order .form-control,
.quick-order .form-select {
    border-radius: 10px;
}
.quick-order-items .item-row {
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
}
.quick-order-items .item-row input,
.quick-order-items .item-row textarea {
    font-size: 0.85rem;
}

.table-board {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
}

.table-card {
    border: 2px solid #dfe7ef;
    border-radius: 14px;
    padding: 12px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.table-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
}

.table-card.selected {
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
}

.table-card.is-disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

#quickTableBoardSection.table-board-disabled {
    opacity: 0.55;
}

#quickTableBoardSection.table-board-disabled .table-board {
    pointer-events: none;
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
}

.table-card-status {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.quick-view-items {
    max-height: 320px;
    overflow-y: auto;
}

.order-badge-row {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.delivery-helper {
    border: 1px dashed #f59e0b;
    background: #fff7ed;
    color: #9a3412;
    border-radius: 12px;
    padding: 10px 12px;
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
    <h2><i class="fas fa-cash-register me-2"></i>Panel de Caja</h2>
    <p class="text-muted">Procesa los pedidos y genera boletas de venta</p>
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

@if(!$currentCashSession)
<div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
    <div>
        <i class="fas fa-exclamation-triangle me-2"></i>
        No tienes una caja abierta. Puedes revisar pedidos, pero para cobrar necesitas abrir caja.
    </div>
    <a href="{{ route('cashier.cash-sessions') }}" class="btn btn-sm btn-dark">
        <i class="fas fa-cash-register me-1"></i>Ir a Caja
    </a>
</div>
@endif

<!-- Estadísticas del Día -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card stat-card orders">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Pedidos Hoy</h6>
                    <h2 class="mb-0">{{ $todayOrders }}</h2>
                </div>
                <div class="text-primary">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card sales">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Ventas Hoy</h6>
                    <h2 class="mb-0 text-success">Bs. {{ number_format($todaySales, 2) }}</h2>
                </div>
                <div class="text-success">
                    <i class="fas fa-dollar-sign fa-3x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pedido Rápido -->
<div class="card mb-4 quick-order">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Pedido Rápido</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3" id="quickTableSection">
                <label class="form-label">Mesa</label>
                <input type="hidden" id="quickTable" value="">
                <div class="small text-muted" id="quickTableLabel">Selecciona una mesa desde el tablero.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mesero</label>
                <select class="form-select" id="quickWaiter">
                    <option value="">Selecciona mesero</option>
                    @foreach($waiters as $waiter)
                        <option value="{{ $waiter->id }}" data-is-delivery="{{ $waiter->isDeliveryWaiter() ? '1' : '0' }}">
                            {{ $waiter->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Producto</label>
                <select class="form-select" id="quickProduct">
                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}"
                            {{ (int) $product->stock <= 0 ? 'disabled' : '' }}
                        >
                            {{ $product->name }} (Bs. {{ number_format($product->price, 2) }})
                            @if((int) $product->stock <= 0)
                                - Agotado
                            @elseif((int) $product->stock < 10)
                                - Stock: {{ $product->stock }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">Si un producto baja de 10 unidades, se mostrará aquí como advertencia.</small>
            </div>
            <div class="col-md-1">
                <label class="form-label">Cantidad</label>
                <input type="number" min="1" class="form-control" id="quickQty" value="1">
            </div>
            <div class="col-md-2">
                <button class="btn btn-success w-100" id="quickAdd">
                    <i class="fas fa-plus me-1"></i>Agregar
                </button>
            </div>
        </div>

        <div class="delivery-helper mt-3" id="deliveryHelper" style="display:none;">
            <strong>Modo delivery:</strong> no hace falta mesa y los productos del pedido saldrán como para llevar.
        </div>

        <div class="mt-3" id="quickTableBoardSection">
            <div class="table-board" id="quickTableBoard">
                @foreach($tableBoard as $table)
                    @php
                        $status = $table->ui_status;
                        $selectable = in_array($status, ['available', 'reserved'], true);
                    @endphp
                    <button
                        type="button"
                        class="table-card {{ $status }} {{ $selectable ? '' : 'is-disabled' }}"
                        data-table-id="{{ $table->id }}"
                        data-selectable="{{ $selectable ? '1' : '0' }}"
                        {{ $selectable ? '' : 'disabled' }}
                        aria-disabled="{{ $selectable ? 'false' : 'true' }}"
                    >
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
                            <div class="small text-muted">{{ $table->reservation_name }}</div>
                            <div class="small text-muted">{{ $table->reservation_at?->format('d/m H:i') }}</div>
                        @endif
                    </button>
                @endforeach
            </div>
            @if($availableTables->isNotEmpty())
                <small class="text-muted d-block mt-2">Verde: libre, amarillo: reservada, azul: ocupada, gris: cerrada.</small>
            @endif
        </div>

        <div class="mt-4 quick-order-items" id="quickItems"></div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <div><strong>Subtotal:</strong> <span id="quickSubtotal">Bs. 0.00</span></div>
                <div><strong>Total:</strong> <span id="quickTotal">Bs. 0.00</span></div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="quickPrintKitchen" checked>
                    <label class="form-check-label" for="quickPrintKitchen">
                        Imprimir cocina al crear pedido
                    </label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" id="quickClear">
                    <i class="fas fa-trash me-1"></i>Limpiar
                </button>
                <button class="btn btn-primary" id="quickSend">
                    <i class="fas fa-paper-plane me-1"></i>Crear Pedido
                </button>
            </div>
        </div>
        @if($availableTables->isEmpty())
            <small class="text-danger d-block mt-3">No hay mesas disponibles para pedidos en mesa. Igual puedes crear pedidos con el mesero Delivery.</small>
        @endif
    </div>
</div>

<!-- Pedidos Pendientes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pedidos Pendientes</h5>
        <span class="badge bg-warning">{{ $pendingOrders->count() }} pendientes</span>
    </div>
    <div class="card-body">
        @forelse($pendingOrders as $order)
        <div class="card order-card mb-3" onclick="window.location='{{ route('cashier.show-order', $order->id) }}'">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <h4 class="mb-0 text-primary">#{{ $order->display_number }}</h4>
                        <span class="badge {{ $order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary') }}">
                            {{ $order->service_mode_label }}
                        </span>
                        <small class="text-muted">{{ $order->table_number }}</small>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Mesero:</small>
                        <strong>{{ $order->user->name }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Hora:</small>
                        <strong>{{ $order->created_at->format('H:i') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Productos:</small>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($order->details->take(3) as $detail)
                            <img src="{{ $detail->product->image_url }}" 
                                 class="product-thumbnail" 
                                 title="{{ $detail->product->name }} (x{{ $detail->quantity }})"
                                 onerror="this.src='https://via.placeholder.com/50?text=?'">
                            @endforeach
                            @if($order->details->count() > 3)
                            <div class="product-thumbnail bg-secondary text-white d-flex align-items-center justify-content-center">
                                +{{ $order->details->count() - 3 }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <h4 class="mb-0 text-success">Bs. {{ number_format($order->total, 2) }}</h4>
                        @if($order->dashboard_has_recent_update || $order->dashboard_table_change)
                        <div class="order-badge-row">
                            @if($order->dashboard_has_recent_update)
                                <span class="badge bg-info text-dark">{{ $order->dashboard_last_update_label }}</span>
                            @endif
                            @if($order->dashboard_table_change)
                                <span class="badge bg-warning text-dark" title="Cambio de mesa">
                                    <i class="fas fa-right-left me-1"></i>{{ $order->dashboard_table_change_label }}
                                </span>
                            @endif
                        </div>
                        @endif
                        <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button"
                           class="btn btn-sm btn-outline-secondary"
                           onclick="event.stopPropagation(); openQuickView({{ $order->id }})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="{{ route('cashier.show-order', $order->id) }}" 
                           class="btn btn-sm btn-primary"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-arrow-right me-1"></i>Procesar
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h5>No hay pedidos pendientes</h5>
            <p class="text-muted">Todos los pedidos han sido procesados</p>
        </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="quickOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Vista Rápida del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="quickOrderContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const quickProducts = @json($products);
let quickItems = [];
let quickItemCounter = 0;
const quickOrderModal = new bootstrap.Modal(document.getElementById('quickOrderModal'));

function getQuickProductStock(product) {
    return Number(product?.stock ?? 0);
}

function getQuickRequestedQty(productId, exceptItemKey = null) {
    return quickItems
        .filter(item => Number(item.product_id) === Number(productId) && item.item_key !== exceptItemKey)
        .reduce((sum, item) => sum + Number(item.quantity || 0), 0);
}

function updateQuickProductAvailability() {
    const $select = $('#quickProduct');
    const currentValue = String($select.val() || '');
    let firstAvailableValue = '';

    $select.find('option').each(function() {
        const stock = Number($(this).data('stock') || 0);
        const isAvailable = stock > 0;
        $(this).prop('disabled', !isAvailable);
        if (isAvailable && !firstAvailableValue) {
            firstAvailableValue = String($(this).val() || '');
        }
    });

    const selectedOptionIsDisabled = $select.find('option:selected').prop('disabled');
    if ((!currentValue || selectedOptionIsDisabled) && firstAvailableValue) {
        $select.val(firstAvailableValue);
    }

    const hasAnyAvailable = $select.find('option:not(:disabled)').length > 0;
    $('#quickProduct').prop('disabled', !hasAnyAvailable);
    $('#quickQty').prop('disabled', !hasAnyAvailable);
    $('#quickAdd').prop('disabled', !hasAnyAvailable);
}

function selectedWaiterIsDelivery() {
    const selectedOption = $('#quickWaiter option:selected');
    const flaggedDelivery = String(selectedOption.data('is-delivery')) === '1';
    const waiterLabel = String(selectedOption.text() || '').trim().toLowerCase();
    return flaggedDelivery || waiterLabel.includes('delivery');
}

function syncQuickWaiterMode() {
    const isDelivery = selectedWaiterIsDelivery();
    $('#deliveryHelper').toggle(isDelivery);
    $('#quickTableSection').toggleClass('opacity-50', isDelivery);
    $('#quickTableBoardSection').toggleClass('table-board-disabled', isDelivery);

    if (isDelivery) {
        $('#quickTable').val('');
        $('#quickTableBoard .table-card')
            .removeClass('selected')
            .prop('disabled', true)
            .attr('aria-disabled', 'true');
        $('#quickTableLabel').text('Delivery seleccionado. Mesas desactivadas: no es necesario asignar mesa.');
    } else {
        $('#quickTableBoard .table-card').each(function() {
            const selectable = String($(this).data('selectable')) === '1';
            $(this).prop('disabled', !selectable);
            $(this).attr('aria-disabled', selectable ? 'false' : 'true');
        });
        $('#quickTableLabel').text('Selecciona una mesa desde el tablero.');
    }
}

function nextQuickItemKey() {
    quickItemCounter += 1;
    return `quick-${quickItemCounter}`;
}

function renderQuickItems() {
    const container = $('#quickItems');
    if (quickItems.length === 0) {
        container.html('<div class="text-muted">Agrega productos para crear el pedido.</div>');
        $('#quickSubtotal').text('Bs. 0.00');
        $('#quickTotal').text('Bs. 0.00');
        return;
    }

    let html = '';
    let subtotal = 0;
    quickItems.forEach(item => {
        const itemTotal = item.quantity * item.price;
        subtotal += itemTotal;
        html += `
            <div class="item-row d-flex justify-content-between align-items-center">
                <div>
                    <strong>${item.name}</strong>
                    <div class="text-muted">Bs. ${item.price.toFixed(2)} c/u</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" min="1" class="form-control form-control-sm" style="width: 70px;"
                        value="${item.quantity}" data-id="${item.item_key}" data-field="qty">
                    <input type="number" min="0" step="0.01" class="form-control form-control-sm" style="width: 90px;"
                        value="${item.price.toFixed(2)}" data-id="${item.item_key}" data-field="price">
                    <input type="text" class="form-control form-control-sm" style="width: 160px;"
                        value="${item.notes || ''}" data-id="${item.item_key}" data-field="notes" placeholder="Nota">
                    <strong>Bs. ${itemTotal.toFixed(2)}</strong>
                    <button class="btn btn-sm btn-outline-danger" data-remove="${item.item_key}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    container.html(html);
    $('#quickSubtotal').text('Bs. ' + subtotal.toFixed(2));
    $('#quickTotal').text('Bs. ' + subtotal.toFixed(2));
}

function syncQtyInputs() {
    $('#quickItems [data-field]').off('change').on('change', function() {
        const id = String($(this).data('id'));
        const item = quickItems.find(i => i.item_key === id);
        if (!item) return;
        const field = $(this).data('field');
        if (field === 'qty') {
            const qty = parseInt($(this).val(), 10);
            if (!qty || qty <= 0) {
                quickItems = quickItems.filter(i => i.item_key !== id);
            } else {
                const product = quickProducts.find(p => Number(p.id) === Number(item.product_id));
                const stock = getQuickProductStock(product);
                const requestedWithoutThisItem = getQuickRequestedQty(item.product_id, item.item_key);

                if (stock <= 0) {
                    alert(`${item.name} está agotado.`);
                    quickItems = quickItems.filter(i => i.item_key !== id);
                } else if ((requestedWithoutThisItem + qty) > stock) {
                    const maxAllowedQty = Math.max(stock - requestedWithoutThisItem, 1);
                    alert(`No hay suficiente stock de ${item.name}. Disponible: ${stock}.`);
                    item.quantity = maxAllowedQty;
                } else {
                    if (stock < 10) {
                        alert(`Advertencia: ${item.name} está por acabarse (stock: ${stock}).`);
                    }
                    item.quantity = qty;
                }
            }
        } else if (field === 'price') {
            const price = parseFloat($(this).val());
            item.price = isNaN(price) ? 0 : price;
        } else if (field === 'notes') {
            item.notes = $(this).val();
        }
        renderQuickItems();
        syncQtyInputs();
        bindRemoveButtons();
    });
}

function bindRemoveButtons() {
    $('#quickItems [data-remove]').off('click').on('click', function() {
        const id = String($(this).data('remove'));
        quickItems = quickItems.filter(i => i.item_key !== id);
        renderQuickItems();
        syncQtyInputs();
        bindRemoveButtons();
    });
}

$('#quickAdd').on('click', function() {
    const productId = parseInt($('#quickProduct').val(), 10);
    const qty = parseInt($('#quickQty').val(), 10);
    if (!qty || qty <= 0) return;
    const product = quickProducts.find(p => p.id === productId);
    if (!product) return;

    const stock = getQuickProductStock(product);
    if (stock <= 0) {
        alert(`${product.name} está agotado.`);
        return;
    }

    const requestedQty = getQuickRequestedQty(productId);
    if ((requestedQty + qty) > stock) {
        alert(`No hay suficiente stock de ${product.name}. Disponible: ${stock}.`);
        return;
    }

    if (stock < 10) {
        alert(`Advertencia: quedan pocas unidades de ${product.name} (stock: ${stock}).`);
    }

    quickItems.push({
        item_key: nextQuickItemKey(),
        product_id: productId,
        name: product.name,
        price: Number(product.price),
        quantity: qty,
        notes: ''
    });
    renderQuickItems();
    syncQtyInputs();
    bindRemoveButtons();
});

$('#quickProduct').on('change', function() {
    const productId = parseInt($(this).val(), 10);
    const product = quickProducts.find(p => Number(p.id) === Number(productId));
    if (!product) {
        return;
    }

    const stock = getQuickProductStock(product);
    if (stock <= 0) {
        alert(`${product.name} está agotado.`);
        return;
    }

    if (stock < 10) {
        alert(`Advertencia: ${product.name} está por acabarse (stock: ${stock}).`);
    }
});

$('#quickClear').on('click', function() {
    quickItems = [];
    $('#quickTable').val('');
    $('#quickTableBoard .table-card').removeClass('selected');
    renderQuickItems();
    syncQuickWaiterMode();
});

$('#quickSend').on('click', function() {
    const isDelivery = selectedWaiterIsDelivery();
    const tableId = $('#quickTable').val();
    const waiterId = $('#quickWaiter').val();
    const shouldPrintKitchen = $('#quickPrintKitchen').is(':checked');
    let printWindow = null;

    if (!isDelivery && !tableId) {
        alert('Selecciona una mesa');
        return;
    }
    if (!waiterId) {
        alert('Selecciona un mesero');
        return;
    }
    if (quickItems.length === 0) {
        alert('Agrega productos al pedido');
        return;
    }
    if (shouldPrintKitchen) {
        printWindow = window.open('', '_blank');
    }
    $.ajax({
        url: '{{ route("cashier.create-order") }}',
        method: 'POST',
        data: {
            table_id: isDelivery ? null : (tableId || null),
            waiter_id: waiterId,
            items: quickItems
        },
        success: function(response) {
            if (shouldPrintKitchen && response.print_kitchen_url) {
                if (printWindow) {
                    printWindow.location = response.print_kitchen_url;
                } else {
                    window.open(response.print_kitchen_url, '_blank');
                }
            } else if (printWindow) {
                printWindow.close();
            }
            quickItems = [];
            $('#quickTable').val('');
            renderQuickItems();
            location.reload();
        },
        error: function(xhr) {
            if (printWindow) {
                printWindow.close();
            }
            alert('Error al crear el pedido: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        }
    });
});

$('#quickTableBoard .table-card').on('click', function() {
    if (selectedWaiterIsDelivery()) {
        return;
    }

    if ($(this).data('selectable') !== 1 && $(this).data('selectable') !== '1') {
        return;
    }

    $('#quickTableBoard .table-card').removeClass('selected');
    $(this).addClass('selected');
    $('#quickTable').val($(this).data('table-id'));
});

$('#quickWaiter').on('change', function() {
    syncQuickWaiterMode();
});

function openQuickView(orderId) {
    quickOrderModal.show();
    $('#quickOrderContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);

    $.getJSON('{{ route("cashier.order-summary", 0) }}'.replace('/0/summary', '/' + orderId + '/summary'), function(data) {
        const rows = data.details.map(detail => `
            <tr>
                <td>${detail.product_name}</td>
                <td class="text-center">${detail.quantity}</td>
                <td class="text-end">Bs. ${detail.unit_price}</td>
                <td class="text-end">Bs. ${detail.subtotal}</td>
            </tr>
            ${detail.notes ? `
                <tr class="table-light">
                    <td colspan="4"><small><strong>Notas:</strong> ${detail.notes}</small></td>
                </tr>
            ` : ''}
        `).join('');

        $('#quickOrderContent').html(`
            <div class="row g-3 mb-3">
                <div class="col-md-3"><strong>Pedido:</strong> #${data.display_number || data.id}</div>
                <div class="col-md-3"><strong>Mesa:</strong> ${data.table}</div>
                <div class="col-md-3"><strong>Mesero:</strong> ${data.waiter}</div>
                <div class="col-md-3"><strong>Estado:</strong> ${data.status}</div>
                <div class="col-md-6"><strong>Fecha:</strong> ${data.created_at}</div>
                <div class="col-md-6 text-md-end"><strong>Total:</strong> Bs. ${data.total}</div>
            </div>
            <div class="table-responsive quick-view-items">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">P. Unit</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rows || '<tr><td colspan="4" class="text-center text-muted">Sin productos</td></tr>'}
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="${'{{ route("cashier.show-order", 0) }}'.replace('/0', '/' + data.id)}" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-1"></i>Ir al detalle completo
                </a>
            </div>
        `);
    }).fail(function() {
        $('#quickOrderContent').html(`
            <div class="alert alert-danger mb-0">
                No se pudo cargar el pedido.
            </div>
        `);
    });
}

renderQuickItems();
updateQuickProductAvailability();
syncQuickWaiterMode();
</script>
@endsection







