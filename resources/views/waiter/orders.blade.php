@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('sidebar')
<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('waiter.dashboard') }}">
            <i class="fas fa-home"></i> Nuevo Pedido
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('waiter.orders') }}">
            <i class="fas fa-list"></i> Mis Pedidos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('waiter.manual') ? 'active' : '' }}" href="{{ route('waiter.manual') }}">
            <i class="fas fa-book"></i> Manual de Usuario
        </a>
    </li>
</ul>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-list me-2"></i>Mis Pedidos</h2>
    <a href="{{ route('waiter.dashboard') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Pedido
    </a>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="waiterOrdersToastContainer" style="z-index: 1100;"></div>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('waiter.orders', ['view' => 'pending']) }}"
               class="btn btn-sm {{ $currentView === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-hourglass-half me-1"></i>
                Pendientes y en proceso
                <span class="badge bg-light text-dark ms-1">{{ $pendingCount }}</span>
            </a>
            <a href="{{ route('waiter.orders', ['view' => 'completed']) }}"
               class="btn btn-sm {{ $currentView === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                <i class="fas fa-check-circle me-1"></i>
                Completados
                <span class="badge bg-light text-dark ms-1">{{ $completedCount }}</span>
            </a>
            <a href="{{ route('waiter.orders', ['view' => 'cancelled']) }}"
               class="btn btn-sm {{ $currentView === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                <i class="fas fa-ban me-1"></i>
                Cancelados
                <span class="badge bg-light text-dark ms-1">{{ $cancelledCount }}</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Mesa</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Procesado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->display_number }}</strong></td>
                        <td>
                            <span class="badge {{ $order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary') }}">
                                {{ $order->service_mode_label }}
                            </span>
                        </td>
                        <td>{{ $order->table_label }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td><strong class="text-success">Bs. {{ number_format($order->total, 2) }}</strong></td>
                        <td>
                            @if($order->status === 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($order->status === 'processing')
                                <span class="badge bg-info">En Proceso</span>
                            @elseif($order->status === 'completed')
                                <span class="badge bg-success">Completado</span>
                            @else
                                <span class="badge bg-danger">Cancelado</span>
                            @endif
                        </td>
                        <td>
                            @if($order->cashier)
                                {{ $order->cashier->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" 
                                    onclick="viewOrderDetails({{ $order->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button"
                               class="btn btn-sm btn-outline-primary"
                               title="Imprimir pedido principal"
                               onclick="directPrintOrder('{{ route('waiter.print-order', ['id' => $order->id, 'scope' => 'main']) }}')">
                                <i class="fas fa-print"></i>
                            </button>
                            @if($order->can_print_added)
                                <button type="button"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Imprimir últimos agregados"
                                   onclick="directPrintOrder('{{ route('waiter.print-order', ['id' => $order->id, 'scope' => 'added']) }}')">
                                    <i class="fas fa-layer-group"></i>
                                </button>
                            @endif
                            @if($order->status === 'pending')
                            <form action="{{ route('waiter.cancel-order', $order->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Cancelar este pedido?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">
                                @if($currentView === 'completed')
                                    No tienes pedidos completados
                                @elseif($currentView === 'cancelled')
                                    No tienes pedidos cancelados
                                @else
                                    No tienes pedidos pendientes o en proceso
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            @forelse($orders as $order)
                <article class="order-mobile-card border rounded-4 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <h6 class="mb-1 fw-bold">Pedido #{{ $order->display_number }}</h6>
                            <div class="small text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <strong class="text-success">Bs. {{ number_format($order->total, 2) }}</strong>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge {{ $order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary') }}">
                            {{ $order->service_mode_label }}
                        </span>
                        @if($order->status === 'pending')
                            <span class="badge bg-warning">Pendiente</span>
                        @elseif($order->status === 'processing')
                            <span class="badge bg-info">En Proceso</span>
                        @elseif($order->status === 'completed')
                            <span class="badge bg-success">Completado</span>
                        @else
                            <span class="badge bg-danger">Cancelado</span>
                        @endif
                    </div>

                    <div class="small mt-3">
                        <div><strong>Mesa:</strong> {{ $order->table_label }}</div>
                        <div><strong>Procesado por:</strong> {{ $order->cashier?->name ?? '-' }}</div>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-info btn-sm" onclick="viewOrderDetails({{ $order->id }})">
                            <i class="fas fa-eye me-1"></i>Ver detalle
                        </button>
                        <button type="button"
                           class="btn btn-outline-primary btn-sm"
                           onclick="directPrintOrder('{{ route('waiter.print-order', ['id' => $order->id, 'scope' => 'main']) }}')">
                            <i class="fas fa-print me-1"></i>Imprimir principal
                        </button>
                        @if($order->can_print_added)
                            <button type="button"
                               class="btn btn-outline-secondary btn-sm"
                               onclick="directPrintOrder('{{ route('waiter.print-order', ['id' => $order->id, 'scope' => 'added']) }}')">
                                <i class="fas fa-layer-group me-1"></i>Imprimir agregados
                            </button>
                        @endif
                        @if($order->status === 'pending')
                            <form action="{{ route('waiter.cancel-order', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm w-100"
                                        onclick="return confirm('¿Cancelar este pedido?')">
                                    <i class="fas fa-times me-1"></i>Cancelar pedido
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">
                        @if($currentView === 'completed')
                            No tienes pedidos completados
                        @elseif($currentView === 'cancelled')
                            No tienes pedidos cancelados
                        @else
                            No tienes pedidos pendientes o en proceso
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="foodNotesModal" tabindex="-1" aria-hidden="true">
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
                    <strong id="foodNotesProductName"></strong>
                    <span class="text-muted d-block small">Marca las indicaciones para este alimento.</span>
                </p>
                <div class="d-grid gap-2">
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="SIN PLATANO">
                        <span class="form-check-label">SIN PLATANO</span>
                    </label>
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="SIN CHOLO">
                        <span class="form-check-label">SIN CHOLO</span>
                    </label>
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="SIN CEBOLLA">
                        <span class="form-check-label">SIN CEBOLLA</span>
                    </label>
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="SIN CILANTRO">
                        <span class="form-check-label">SIN CILANTRO</span>
                    </label>
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="SIN LECHUGA">
                        <span class="form-check-label">SIN LECHUGA</span>
                    </label>
                    <label class="form-check border rounded p-3 food-note-option">
                        <input class="form-check-input food-note-input" type="checkbox" value="COMPLETO" id="foodNoteCompleteOption">
                        <span class="form-check-label">COMPLETO</span>
                    </label>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-uppercase">Tipo de servicio</label>
                    <select class="form-select form-select-sm" id="foodServiceType">
                        <option value="dine_in">En mesa</option>
                        <option value="takeaway">Para llevar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmFoodNotes">
                    <i class="fas fa-check me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<iframe id="orderPrintFrame" title="Ticket de impresión" class="d-none"></iframe>
@endsection

@section('styles')
<style>
    .order-mobile-card {
        background: #fff;
    }

    @media (max-width: 767.98px) {
        #orderDetailsModal .modal-dialog {
            margin: 0.75rem;
        }

        #orderDetailsModal .modal-content {
            border-radius: 1rem;
        }

        .order-item-card {
            border: 1px solid #e9ecef;
            border-radius: 0.9rem;
            padding: 0.85rem;
            background: #fff;
        }
    }
</style>
@endsection

@section('scripts')
<script>
const productsCatalog = @json($products);
@include('orders._editor-utils')
const orderEditorUtils = window.orderEditorUtils;
let currentOrder = null;
let currentItems = [];
let pendingFoodProduct = null;
const foodNotesModal = new bootstrap.Modal(document.getElementById('foodNotesModal'));
const orderPrintFrame = document.getElementById('orderPrintFrame');
let currentOrderPrintUrl = '';
let orderPrintLoaded = false;
let orderPrintLoadTimeoutId = null;
let shouldAutoPrintOrder = false;

function createCurrentItemKey(productId, notes = '', serviceType = 'dine_in') {
    return orderEditorUtils.buildItemSignature(productId, notes, serviceType);
}

function getProductStock(product) {
    return Number(product?.stock ?? 0);
}

function getSelectableProductsForEdit() {
    return productsCatalog.filter(product => getProductStock(product) > 0);
}

function renderOrderDetails() {
    if (!currentOrder) {
        return;
    }

    const canEdit = currentOrder.can_edit;
    const selectableProducts = getSelectableProductsForEdit();
    const addProductDisabled = selectableProducts.length === 0;
    const addProductOptions = selectableProducts.length > 0
        ? selectableProducts.map(p => `<option value="${p.id}">${p.name} (Bs. ${Number(p.price).toFixed(2)})${getProductStock(p) < 10 ? ` - Stock: ${getProductStock(p)}` : ''}</option>`).join('')
        : '<option value="">No hay productos con stock disponible</option>';
    const selectedTableId = Number(currentOrder.pending_table_id || currentOrder.table_id || 0);
    const tableOptions = (currentOrder.available_tables || []).map(table => `
        <option value="${table.id}" ${selectedTableId === Number(table.id) ? 'selected' : ''}>${table.name}</option>
    `).join('');
    let rows = '';
    let mobileCards = '';
    let subtotalSum = 0;

    currentItems.forEach((item) => {
        const subtotal = item.quantity * item.unit_price;
        subtotalSum += subtotal;
        rows += `
            <tr data-item-key="${item.item_key}">
                <td>${item.product_name}</td>
                <td>Bs. ${item.unit_price.toFixed(2)}</td>
                <td>
                    ${canEdit
                        ? `<input type="number" class="form-control form-control-sm qty-input" min="0" value="${item.quantity}">`
                        : `<span>${item.quantity}</span>`
                    }
                </td>
                <td><strong>Bs. ${subtotal.toFixed(2)}</strong></td>
                ${canEdit ? `<td class="text-end"><button class="btn btn-sm btn-outline-danger remove-item-btn"><i class="fas fa-trash"></i></button></td>` : ''}
            </tr>
            <tr class="table-light">
                <td colspan="${canEdit ? 5 : 4}">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small><strong>Tipo:</strong> ${item.service_type_label || (item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa')}</small>
                        <span class="badge ${item.service_type === 'takeaway' ? 'bg-warning text-dark' : 'bg-secondary'}">
                            ${item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa'}
                        </span>
                    </div>
                </td>
            </tr>
            ${item.notes ? `
                <tr class="table-light">
                    <td colspan="${canEdit ? 5 : 4}">
                        <small><strong>Indicaciones:</strong> ${item.notes}</small>
                    </td>
                </tr>
            ` : ''}
        `;

        mobileCards += `
            <div class="order-item-card mb-2" data-item-key="${item.item_key}">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <strong>${item.product_name}</strong>
                    <strong>Bs. ${subtotal.toFixed(2)}</strong>
                </div>
                <div class="small text-muted mt-1">Unitario: Bs. ${item.unit_price.toFixed(2)}</div>
                <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
                    <span class="small">Cantidad</span>
                    ${canEdit
                        ? `<input type="number" class="form-control form-control-sm qty-input" min="0" value="${item.quantity}" style="max-width: 88px;">`
                        : `<span class="fw-semibold">${item.quantity}</span>`
                    }
                </div>
                <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
                    <small><strong>Tipo:</strong> ${item.service_type_label || (item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa')}</small>
                    <span class="badge ${item.service_type === 'takeaway' ? 'bg-warning text-dark' : 'bg-secondary'}">
                        ${item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa'}
                    </span>
                </div>
                ${item.notes ? `<div class="small mt-2"><strong>Indicaciones:</strong> ${item.notes}</div>` : ''}
                ${canEdit ? `
                    <div class="mt-2 text-end">
                        <button class="btn btn-sm btn-outline-danger remove-item-btn"><i class="fas fa-trash me-1"></i>Quitar</button>
                    </div>
                ` : ''}
            </div>
        `;
    });

    const isMobileView = window.matchMedia('(max-width: 767.98px)').matches;
    const itemsDesktopMarkup = `
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        ${canEdit ? '<th class="text-end">Quitar</th>' : ''}
                    </tr>
                </thead>
                <tbody>
                    ${rows || `<tr><td colspan="${canEdit ? 5 : 4}" class="text-center text-muted">Sin productos</td></tr>`}
                </tbody>
            </table>
        </div>
    `;
    const itemsMobileMarkup = `
        <div>
            ${mobileCards || '<div class="text-center text-muted py-3">Sin productos</div>'}
        </div>
    `;

    const content = `
        <div class="mb-3 d-flex flex-wrap gap-3">
            <div><strong>Pedido:</strong> #${currentOrder.display_number || currentOrder.id}</div>
            <div><strong>Tipo:</strong> ${currentOrder.service_mode_label || (currentOrder.service_mode === 'takeaway' ? 'Para llevar' : 'En mesa')}</div>
            <div><strong>Mesa actual:</strong> ${currentOrder.table_number || '-'}</div>
            <div><strong>Estado:</strong> ${currentOrder.status}</div>
            <div><strong>Fecha:</strong> ${currentOrder.created_at}</div>
        </div>
        ${canEdit ? `
            <div class="mb-3 p-3 rounded-4 border" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-color: #fdba74 !important;">
                <label class="form-label fw-bold text-uppercase small mb-2" style="color: #9a3412;">Cambiar mesa</label>
                <div class="small mb-2" style="color: #9a3412;">
                    Selecciona aquí otra mesa disponible para transferir el pedido.
                </div>
                <select class="form-select form-select-sm" id="editTableSelect">
                    <option value="">Selecciona una mesa</option>
                    ${tableOptions}
                </select>
            </div>
        ` : ''}
        ${isMobileView ? itemsMobileMarkup : itemsDesktopMarkup}
        <div class="d-flex justify-content-end gap-3">\n            <div><strong>Subtotal:</strong> Bs. ${subtotalSum.toFixed(2)}</div>\n            <div><strong>Total:</strong> Bs. ${subtotalSum.toFixed(2)}</div>\n        </div>
        ${canEdit ? `
            <hr>
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Agregar producto</label>
                    <select class="form-select form-select-sm" id="addProductSelect" ${addProductDisabled ? 'disabled' : ''}>
                        ${addProductOptions}
                    </select>
                    ${addProductDisabled ? '<small class="text-danger d-block mt-1">Todos los productos están agotados.</small>' : ''}
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control form-control-sm" id="addProductQty" min="1" value="1" ${addProductDisabled ? 'disabled' : ''}>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-primary w-100" id="addProductBtn" ${addProductDisabled ? 'disabled' : ''}>
                        <i class="fas fa-plus me-1"></i>Agregar
                    </button>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button class="btn btn-success btn-sm" id="saveOrderChanges">
                    <i class="fas fa-save me-1"></i>Guardar cambios
                </button>
            </div>
        ` : ''}
    `;

    $('#orderDetailsContent').html(content);

    if (canEdit) {
        $('#editTableSelect').off('change').on('change', function() {
            currentOrder.pending_table_id = $(this).val();
        });

        $('#orderDetailsContent .remove-item-btn').off('click').on('click', function() {
            const container = $(this).closest('[data-item-key]');
            const itemKey = String(container.data('item-key'));
            currentItems = currentItems.filter(i => i.item_key !== itemKey);
            renderOrderDetails();
        });

        $('#orderDetailsContent .qty-input').off('change').on('change', function() {
            const container = $(this).closest('[data-item-key]');
            const itemKey = String(container.data('item-key'));
            const item = currentItems.find(i => i.item_key === itemKey);
            if (!item) {
                return;
            }

            const qty = parseInt($(this).val(), 10);
            item.quantity = isNaN(qty) ? 0 : Math.max(0, qty);
            renderOrderDetails();
        });

        $('#addProductBtn').off('click').on('click', function() {
            const productId = parseInt($('#addProductSelect').val(), 10);
            const qty = parseInt($('#addProductQty').val(), 10);
            if (!qty || qty <= 0) {
                return;
            }

            const product = productsCatalog.find(p => p.id === productId);
            if (!product) {
                alert('Selecciona un producto válido.');
                return;
            }

            const stock = getProductStock(product);
            if (stock <= 0) {
                alert(`${product.name} está agotado.`);
                return;
            }

            if (qty > stock) {
                alert(`No hay suficiente stock de ${product.name}. Disponible: ${stock}.`);
                return;
            }

            if (stock < 10) {
                alert(`Advertencia: quedan pocas unidades de ${product.name} (stock: ${stock}).`);
            }

            if (isFoodProduct(product)) {
                pendingFoodProduct = {
                    product_id: productId,
                    product_name: product ? product.name : 'Producto',
                    unit_price: product ? Number(product.price) : 0,
                    quantity: qty
                };
                $('#foodNotesProductName').text(pendingFoodProduct.product_name);
                $('.food-note-input').prop('checked', false);
                syncFoodNoteStyles();
                foodNotesModal.show();
                return;
            }

            addOrUpdateCurrentItem({
                product_id: productId,
                product_name: product ? product.name : 'Producto',
                unit_price: product ? Number(product.price) : 0,
                quantity: qty,
                notes: '',
                service_type: 'dine_in',
                service_type_label: orderEditorUtils.getServiceTypeLabel('dine_in')
            });

            renderOrderDetails();
        });

        $('#addProductSelect').off('change').on('change', function() {
            const selectedId = parseInt($(this).val(), 10);
            const selectedProduct = productsCatalog.find(p => p.id === selectedId);
            if (!selectedProduct) {
                return;
            }

            const stock = getProductStock(selectedProduct);
            if (stock <= 0) {
                alert(`${selectedProduct.name} está agotado.`);
            } else if (stock < 10) {
                alert(`Advertencia: ${selectedProduct.name} está por acabarse (stock: ${stock}).`);
            }
        });

        $('#saveOrderChanges').off('click').on('click', function() {
            $('#orderDetailsContent .qty-input').each(function() {
                const container = $(this).closest('[data-item-key]');
                const itemKey = String(container.data('item-key'));
                const qty = parseInt($(this).val(), 10);
                const item = currentItems.find(i => i.item_key === itemKey);
                if (item) {
                    item.quantity = isNaN(qty) ? 0 : qty;
                }
            });

            $.ajax({
                url: '{{ route("waiter.update-order-items", 0) }}'.replace('/0/', '/' + currentOrder.id + '/'),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    table_id: currentOrder.pending_table_id || $('#editTableSelect').val(),
                    items: currentItems.map(i => ({
                        product_id: i.product_id,
                        quantity: i.quantity,
                        notes: i.notes || '',
                        service_type: i.service_type || 'dine_in'
                    }))
                },
                success: function() {
                    const modalEl = document.getElementById('orderDetailsModal');
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) {
                        instance.hide();
                    }
                    window.location.href = '{{ route("waiter.orders") }}';
                },
                error: function(xhr) {
                    alert('Error al actualizar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                }
            });
        });
    }
}

function isFoodProduct(product) {
    return orderEditorUtils.isFoodProduct(product);
}

function addOrUpdateCurrentItem(newItem) {
    orderEditorUtils.mergeOrderItem(currentItems, newItem, {
        createItemKey: (item) => createCurrentItemKey(item.product_id, item.notes, item.service_type),
    });
}

function resetFoodNotesModal() {
    pendingFoodProduct = null;
    $('#foodNotesProductName').text('');
    $('.food-note-input').prop('checked', false);
    $('#foodServiceType').val('dine_in');
    syncFoodNoteStyles();
}

function getSelectedFoodNotes() {
    return orderEditorUtils.getCheckedValues('.food-note-input');
}

function getSelectedFoodServiceType() {
    return orderEditorUtils.normalizeServiceType($('#foodServiceType').val());
}

function syncFoodNoteStyles() {
    orderEditorUtils.syncToggleCardState('.food-note-option', '.food-note-input');
}

$('#foodNotesModal').on('hidden.bs.modal', function() {
    resetFoodNotesModal();
});

if (orderPrintFrame) {
    orderPrintFrame.addEventListener('load', function() {
        orderPrintLoaded = true;
        clearOrderPrintLoadTimeout();

        if (shouldAutoPrintOrder) {
            setTimeout(function() {
                printOrderTicket(true);
            }, 200);
        }
    });

    orderPrintFrame.addEventListener('error', function() {
        handleOrderPrintLoadError();
    });
}

$('.food-note-input').on('change', function() {
    const isComplete = this.value === 'COMPLETO';

    if (isComplete && this.checked) {
        $('.food-note-input').not(this).prop('checked', false);
    }

    if (!isComplete && this.checked) {
        $('#foodNoteCompleteOption').prop('checked', false);
    }

    syncFoodNoteStyles();
});

$('#confirmFoodNotes').on('click', function() {
    if (!pendingFoodProduct) {
        return;
    }

    addOrUpdateCurrentItem({
        product_id: pendingFoodProduct.product_id,
        product_name: pendingFoodProduct.product_name,
        unit_price: pendingFoodProduct.unit_price,
        quantity: pendingFoodProduct.quantity,
        notes: getSelectedFoodNotes(),
        service_type: getSelectedFoodServiceType(),
        service_type_label: orderEditorUtils.getServiceTypeLabel(getSelectedFoodServiceType())
    });

    foodNotesModal.hide();
    renderOrderDetails();
});

function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();

    $('#orderDetailsContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);

    $.getJSON('{{ route("waiter.order-details", 0) }}'.replace('/0', '/' + orderId), function(data) {
        currentOrder = data;
        currentOrder.pending_table_id = data.table_id;
        currentItems = data.details.map(d => ({
            item_key: createCurrentItemKey(d.product_id, d.notes || '', d.service_type || 'dine_in'),
            product_id: d.product_id,
            product_name: d.product_name,
            unit_price: Number(d.unit_price),
            quantity: d.quantity,
            notes: d.notes || '',
            service_type: d.service_type || 'dine_in',
            service_type_label: d.service_type_label || orderEditorUtils.getServiceTypeLabel(d.service_type)
        }));
        renderOrderDetails();
    }).fail(function() {
        $('#orderDetailsContent').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No se pudo cargar el detalle del pedido.
            </div>
        `);
    });
}

function directPrintOrder(printUrl) {
    currentOrderPrintUrl = printUrl;
    orderPrintLoaded = false;
    shouldAutoPrintOrder = true;
    startOrderPrintLoadTimeout();
    if (orderPrintFrame) {
        orderPrintFrame.src = printUrl;
    }
    showToast('info', 'Preparando ticket para impresión...');
}

function printOrderTicket(isAuto = false) {
    if (!orderPrintFrame || !orderPrintFrame.contentWindow || !orderPrintLoaded) {
        if (!isAuto) {
            showToast('warning', 'La vista de impresión aún no está lista.');
        }
        return;
    }

    try {
        orderPrintFrame.contentWindow.focus();
        orderPrintFrame.contentWindow.print();
        shouldAutoPrintOrder = false;

        if (isAuto) {
            showToast('info', 'Se abrió la impresión del ticket de cocina.');
        }
    } catch (error) {
        if (isAuto) {
            showToast('warning', 'No se pudo abrir la impresión automática del ticket.');
        } else {
            showToast('danger', 'No se pudo abrir la impresión.');
        }
    }
}

function startOrderPrintLoadTimeout() {
    clearOrderPrintLoadTimeout();
    orderPrintLoadTimeoutId = setTimeout(function() {
        if (!orderPrintLoaded) {
            handleOrderPrintLoadError();
        }
    }, 8000);
}

function clearOrderPrintLoadTimeout() {
    if (orderPrintLoadTimeoutId) {
        clearTimeout(orderPrintLoadTimeoutId);
        orderPrintLoadTimeoutId = null;
    }
}

function handleOrderPrintLoadError() {
    clearOrderPrintLoadTimeout();
    shouldAutoPrintOrder = false;
    showToast('warning', 'No se pudo cargar la vista de impresión del pedido.');
}

function showToast(type, message) {
    const container = document.getElementById('waiterOrdersToastContainer');
    if (!container) {
        return;
    }

    const toneMap = {
        success: 'text-bg-success',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info',
    };

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center border-0 ${toneMap[type] || 'text-bg-secondary'}`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    `;

    container.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}
</script>
@endsection









