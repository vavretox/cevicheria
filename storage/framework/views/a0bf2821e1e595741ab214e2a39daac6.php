?

<?php $__env->startSection('title', 'Panel Mesero'); ?>

<?php $__env->startSection('sidebar'); ?>
<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" href="<?php echo e(route('waiter.dashboard')); ?>">
            <i class="fas fa-home"></i> Nuevo Pedido
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('waiter.orders')); ?>">
            <i class="fas fa-list"></i> Mis Pedidos
        </a>
    </li>
</ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
        height: 100%;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    .product-card.selected {
        border-color: #27ae60;
        background-color: #e8f8f0;
    }

    .product-image {
        width: 100%;
        height: 380px;
        object-fit: contain;
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        padding: 20px;
        border-radius: 8px 8px 0 0;
    }

    .product-info {
        padding: 18px 15px 16px;
    }

    .product-name {
        font-weight: bold;
        font-size: 1.1rem;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .product-price {
        font-size: 1.3rem;
        color: #e74c3c;
        font-weight: bold;
    }

    .category-tab {
        margin-bottom: 20px;
    }

    .order-summary {
        position: sticky;
        top: 20px;
    }

    .order-item {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-control button {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 50%;
    }

    .quantity-control input {
        width: 60px;
        text-align: center;
    }

    .order-item-notes {
        margin-top: 8px;
        padding: 8px 10px;
        background: #fff;
        border-radius: 6px;
        border: 1px dashed #d7dee4;
    }

    .ceviche-option {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 12px 14px;
        transition: all 0.2s ease;
    }

    .ceviche-option.active {
        border-color: #27ae60;
        background: #eefaf3;
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

    .selected-table-summary {
        border: 1px solid #dbe4ee;
        border-radius: 12px;
        padding: 12px 14px;
        background: #f8fafc;
    }

    .service-switch {
        background: #fff7ed;
        border: 1px solid #fdba74;
        border-radius: 12px;
        padding: 10px 12px;
    }

    .confirm-summary-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #f8fafc;
    }

    .product-search-box {
        max-width: 420px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-shopping-cart me-2"></i>Nuevo Pedido</h2>
    <div>
        <span class="badge bg-success fs-6">
            <i class="fas fa-user me-2"></i><?php echo e(Auth::user()->name); ?>

        </span>
    </div>
</div>

<div class="row">
    <!-- Productos -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Selecciona los Productos</h5>
            </div>
            <div class="card-body">
                <div class="product-search-box mb-4">
                    <label class="form-label fw-semibold" for="productSearchInput">Buscar producto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="productSearchInput"
                            placeholder="Escribe nombre o descripción..."
                            autocomplete="off"
                        >
                    </div>
                </div>

                <!-- Tabs de Categorías -->
                <ul class="nav nav-pills mb-4" id="categoryTabs" role="tablist">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo e($index === 0 ? 'active' : ''); ?>" 
                                id="cat-<?php echo e($category->id); ?>-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#cat-<?php echo e($category->id); ?>" 
                                type="button">
                            <?php echo e($category->name); ?>

                            <span class="badge bg-light text-dark ms-2"><?php echo e($category->activeProducts->count()); ?></span>
                        </button>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <!-- Contenido de Categorías -->
                <div class="tab-content" id="categoryTabsContent">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="tab-pane fade <?php echo e($index === 0 ? 'show active' : ''); ?>" 
                         id="cat-<?php echo e($category->id); ?>">
                        <div class="row g-3">
                            <?php $__empty_1 = true; $__currentLoopData = $category->activeProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card product-card" 
                                     data-product-id="<?php echo e($product->id); ?>"
                                     data-product-name="<?php echo e($product->name); ?>"
                                     data-product-description="<?php echo e($product->description ?? ''); ?>"
                                     data-product-price="<?php echo e($product->price); ?>"
                                     data-category-code="<?php echo e($product->category->code ?? ''); ?>"
                                     data-is-ceviche="<?php echo e(str_contains(Str::lower($product->category->code ?? ''), 'cevich') ? '1' : '0'); ?>">
                                    <img src="<?php echo e($product->image_url); ?>" 
                                         class="product-image" 
                                         alt="<?php echo e($product->name); ?>"
                                         onerror="this.src='https://via.placeholder.com/300x180?text=Sin+Imagen'">
                                    <div class="product-info text-center">
                                        <div class="product-name"><?php echo e($product->name); ?></div>
                                        <div class="product-price">Bs. <?php echo e(number_format($product->price, 2)); ?></div>
                                        <?php if($product->description): ?>
                                        <small class="text-muted d-block mt-2"><?php echo e(Str::limit($product->description, 50)); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay productos disponibles en esta categoría
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen del Pedido -->
    <div class="col-lg-4">
        <div class="card order-summary">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-table me-2"></i>Mesa</label>
                    <input type="hidden" id="tableSelect" value="">
                    <div class="selected-table-summary d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="small text-muted">Mesa seleccionada</div>
                            <div id="selectedTableLabel" class="fw-bold">Ninguna mesa seleccionada</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tablePickerModal">
                            <i class="fas fa-table-cells-large me-1"></i>Seleccionar mesa
                        </button>
                    </div>
                    <?php if($availableTables->isEmpty()): ?>
                        <small class="text-danger d-block mt-2">No hay mesas disponibles. Crea o habilita mesas desde administración.</small>
                    <?php else: ?>
                        <small class="text-muted d-block mt-2">Usa el selector para ver el tablero completo de mesas.</small>
                    <?php endif; ?>
                </div>

                <div id="orderItems" class="mb-3">
                    <div class="text-center text-muted py-4" id="emptyOrder">
                        <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                        <p>Selecciona productos para comenzar</p>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <strong>Subtotal:</strong>
                    <span id="subtotal">Bs. 0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <h5><strong>Total:</strong></h5>
                    <h5><strong id="total" class="text-danger">Bs. 0.00</strong></h5>
                </div>

                <button id="sendOrder" class="btn btn-success w-100 mb-2" <?php echo e($availableTables->isEmpty() ? 'disabled' : ''); ?>>
                    <i class="fas fa-paper-plane me-2"></i>Enviar Pedido
                </button>
                <button id="clearOrder" class="btn btn-outline-danger w-100">
                    <i class="fas fa-trash me-2"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tablePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-table-cells-large me-2"></i>Seleccionar mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-board" id="tableBoard">
                    <?php $__currentLoopData = $tableBoard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $status = $table->ui_status;
                            $selectable = in_array($status, ['available', 'reserved'], true);
                        ?>
                        <button
                            type="button"
                            class="table-card <?php echo e($status); ?> <?php echo e($selectable ? '' : 'is-disabled'); ?>"
                            data-table-id="<?php echo e($table->id); ?>"
                            data-table-name="<?php echo e($table->name); ?>"
                            data-selectable="<?php echo e($selectable ? '1' : '0'); ?>"
                        >
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong><?php echo e($table->name); ?></strong>
                                <span class="table-card-status">
                                    <?php if($status === 'available'): ?>
                                        Libre
                                    <?php elseif($status === 'reserved'): ?>
                                        Reservada
                                    <?php elseif($status === 'occupied'): ?>
                                        Ocupada
                                    <?php else: ?>
                                        Cerrada
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if($table->zone): ?>
                                <div class="small text-muted mb-1"><?php echo e($table->zone); ?></div>
                            <?php endif; ?>
                            <?php if($table->isReserved()): ?>
                                <div class="small text-muted"><?php echo e($table->reservation_name); ?></div>
                                <div class="small text-muted"><?php echo e($table->reservation_at?->format('d/m H:i')); ?></div>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <small class="text-muted d-block mt-3">Verde: libre, amarillo: reservada, azul: ocupada, gris: cerrada.</small>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cevicheOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list-check me-2"></i>Opciones del plato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong id="cevicheModalProductName"></strong>
                    <span class="text-muted d-block small">Selecciona cómo saldrá este alimento.</span>
                </p>
                <div class="d-grid gap-2 mb-3" id="cevicheNotesGroup" style="display:none !important;">
                    <label class="form-check ceviche-option">
                        <input class="form-check-input ceviche-option-input" type="checkbox" value="SIN CEBOLLA">
                        <span class="form-check-label">SIN CEBOLLA</span>
                    </label>
                    <label class="form-check ceviche-option">
                        <input class="form-check-input ceviche-option-input" type="checkbox" value="SIN CILANTRO">
                        <span class="form-check-label">SIN CILANTRO</span>
                    </label>
                    <label class="form-check ceviche-option">
                        <input class="form-check-input ceviche-option-input" type="checkbox" value="SIN LECHUGA">
                        <span class="form-check-label">SIN LECHUGA</span>
                    </label>
                    <label class="form-check ceviche-option">
                        <input class="form-check-input ceviche-option-input" type="checkbox" value="COMPLETO" id="cevicheCompleteOption">
                        <span class="form-check-label">COMPLETO</span>
                    </label>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small text-uppercase">Tipo de servicio</label>
                    <select class="form-select form-select-sm" id="cevicheServiceType">
                        <option value="dine_in">En mesa</option>
                        <option value="takeaway">Para llevar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmCevicheOptions">
                    <i class="fas fa-check me-1"></i>Agregar al pedido
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>Resumen del pedido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <div class="small text-muted">Mesa</div>
                        <div class="fw-bold" id="confirmOrderTable">-</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Total</div>
                        <div class="fw-bold text-danger" id="confirmOrderTotal">Bs. 0.00</div>
                    </div>
                </div>
                <div id="confirmOrderItems"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" id="confirmOrderPrint">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
let orderItems = [];
let pendingFoodProduct = null;
const cevicheOptionsModal = new bootstrap.Modal(document.getElementById('cevicheOptionsModal'));
const tablePickerModal = new bootstrap.Modal(document.getElementById('tablePickerModal'));
const confirmOrderModal = new bootstrap.Modal(document.getElementById('confirmOrderModal'));

function createOrderItemKey(productId, notes = '', serviceType = 'dine_in') {
    return `${productId}::${String(notes || '').trim().toLowerCase()}::${serviceType}`;
}

$(document).ready(function() {
    // Click en producto
    $('.product-card').click(function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = parseFloat($(this).data('product-price'));
        const categoryCode = String($(this).data('category-code') || '');
        const isCeviche = String($(this).data('is-ceviche') || '0') === '1';

        handleProductSelection(productId, productName, productPrice, categoryCode, isCeviche);
    });

    $('#tableBoard .table-card').on('click', function() {
        if ($(this).data('selectable') !== 1 && $(this).data('selectable') !== '1') {
            return;
        }

        $('#tableBoard .table-card').removeClass('selected');
        $(this).addClass('selected');
        $('#tableSelect').val($(this).data('table-id'));
        $('#selectedTableLabel').text($(this).data('table-name'));
        tablePickerModal.hide();
    });

    $('#confirmCevicheOptions').click(function() {
        if (!pendingFoodProduct) {
            return;
        }

        addToOrder(
            pendingFoodProduct.productId,
            pendingFoodProduct.productName,
            pendingFoodProduct.productPrice,
            getSelectedCevicheNotes(),
            getSelectedCevicheServiceType()
        );

        resetCevicheModal();
        cevicheOptionsModal.hide();
    });

    $('#cevicheOptionsModal').on('hidden.bs.modal', function() {
        resetCevicheModal();
    });

    // Enviar pedido
    $('#sendOrder').click(function() {
        const tableId = $('#tableSelect').val();

        if (!tableId) {
            alert('Por favor selecciona una mesa');
            return;
        }

        if (orderItems.length === 0) {
            alert('Agrega productos al pedido');
            return;
        }

        renderConfirmOrderSummary();
        confirmOrderModal.show();
    });

    $('#confirmOrderPrint').click(function() {
        submitOrderAndPrint();
    });

    // Limpiar pedido
    $('#clearOrder').click(function() {
        if (confirm('¿Estás seguro de limpiar el pedido?')) {
            clearOrder();
        }
    });

    $('#productSearchInput').on('input', function() {
        filterProducts($(this).val());
    });
});

function filterProducts(searchTerm) {
    const normalizedTerm = String(searchTerm || '').trim().toLowerCase();
    const searchWords = normalizedTerm === ''
        ? []
        : normalizedTerm.split(/\s+/).filter(Boolean);

    $('.tab-pane').each(function() {
        let visibleCards = 0;

        $(this).find('.col-md-4.col-lg-3').each(function() {
            const card = $(this).find('.product-card');
            const productName = String(card.data('product-name') || '').toLowerCase();
            const productDescription = String(card.data('product-description') || '').toLowerCase();
            const searchableText = `${productName} ${productDescription}`.trim();
            const matches = searchWords.length === 0
                || searchWords.every(word => searchableText.includes(word));

            $(this).toggle(matches);
            if (matches) {
                visibleCards += 1;
            }
        });

        const emptyState = $(this).find('.product-search-empty');
        if (normalizedTerm !== '' && visibleCards === 0) {
            if (emptyState.length === 0) {
                $(this).find('.row.g-3').append(`
                    <div class="col-12 product-search-empty">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-search me-2"></i>
                            No se encontraron productos con esa búsqueda.
                        </div>
                    </div>
                `);
            }
        } else {
            emptyState.remove();
        }
    });
}

function handleProductSelection(productId, productName, productPrice, categoryCode = '', isCeviche = false) {
    if (!requiresFoodOptions(categoryCode)) {
        addToOrder(productId, productName, productPrice);
        return;
    }

    pendingFoodProduct = { productId, productName, productPrice, categoryCode, isCeviche };
    $('#cevicheModalProductName').text(productName);
    $('#cevicheNotesGroup').toggle(isCeviche);
    cevicheOptionsModal.show();
}

function requiresFoodOptions(categoryCode) {
    return String(categoryCode || '') !== 'bebidas';
}

function getSelectedCevicheNotes() {
    if (!pendingFoodProduct || !pendingFoodProduct.isCeviche) {
        return '';
    }

    return $('.ceviche-option-input:checked').map(function() {
        return this.value;
    }).get().join(', ');
}

function getSelectedCevicheServiceType() {
    return $('#cevicheServiceType').val() || 'dine_in';
}

function resetCevicheModal() {
    pendingFoodProduct = null;
    $('#cevicheModalProductName').text('');
    $('#cevicheNotesGroup').hide();
    $('.ceviche-option-input').prop('checked', false);
    $('#cevicheServiceType').val('dine_in');
    syncCevicheOptionStyles();
}

function syncCevicheOptionStyles() {
    $('.ceviche-option').each(function() {
        const input = $(this).find('.ceviche-option-input');
        $(this).toggleClass('active', input.is(':checked'));
    });
}

function addToOrder(productId, productName, productPrice, notes = '', serviceType = 'dine_in') {
    const itemKey = createOrderItemKey(productId, notes, serviceType);
    const existingItem = orderItems.find(item => item.item_key === itemKey);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        orderItems.push({
            item_key: itemKey,
            product_id: productId,
            name: productName,
            price: productPrice,
            quantity: 1,
            notes: notes || '',
            service_type: serviceType
        });
    }

    renderOrderItems();
    updateTotals();
}

function removeFromOrder(itemKey) {
    orderItems = orderItems.filter(item => item.item_key !== itemKey);
    renderOrderItems();
    updateTotals();
}

function updateQuantity(itemKey, quantity) {
    const item = orderItems.find(item => item.item_key === itemKey);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromOrder(itemKey);
        } else {
            renderOrderItems();
            updateTotals();
        }
    }
}

function renderOrderItems() {
    const container = $('#orderItems');
    
    if (orderItems.length === 0) {
        container.html(`
            <div class="text-center text-muted py-4" id="emptyOrder">
                <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                <p>Selecciona productos para comenzar</p>
            </div>
        `);
        $('#sendOrder').prop('disabled', true);
        return;
    }

    $('#sendOrder').prop('disabled', false);
    let html = '';

    orderItems.forEach(item => {
        html += `
            <div class="order-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong>${item.name}</strong>
                    <button class="btn btn-sm btn-danger" onclick="removeFromOrder('${item.item_key}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="quantity-control mb-2">
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity('${item.item_key}', ${item.quantity - 1})">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="form-control form-control-sm" 
                           value="${item.quantity}" min="1"
                           onchange="updateQuantity('${item.item_key}', this.value)">
                    <button class="btn btn-sm btn-outline-secondary" 
                            onclick="updateQuantity('${item.item_key}', ${item.quantity + 1})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between">
                    <small>Bs. ${item.price.toFixed(2)} c/u</small>
                    <strong class="text-success">Bs. ${(item.price * item.quantity).toFixed(2)}</strong>
                </div>
                <div class="mt-2">
                    <span class="badge ${item.service_type === 'takeaway' ? 'bg-warning text-dark' : 'bg-secondary'}">
                        ${item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa'}
                    </span>
                </div>
                ${item.notes ? `<div class="order-item-notes"><small><strong>Indicaciones:</strong> ${item.notes}</small></div>` : ''}
            </div>
        `;
    });

    container.html(html);
}

function updateTotals() {
    const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = subtotal;

    $('#subtotal').text('Bs. ' + subtotal.toFixed(2));
    $('#total').text('Bs. ' + total.toFixed(2));
}

function renderConfirmOrderSummary() {
    const tableName = $('#selectedTableLabel').text() || 'Sin mesa';
    const total = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    $('#confirmOrderTable').text(tableName);
    $('#confirmOrderTotal').text('Bs. ' + total.toFixed(2));

    const itemsHtml = orderItems.map(item => `
        <div class="confirm-summary-item">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-bold">${item.name}</div>
                    <div class="small text-muted">${item.quantity} x Bs. ${item.price.toFixed(2)}</div>
                    <div class="mt-2">
                        <span class="badge ${item.service_type === 'takeaway' ? 'bg-warning text-dark' : 'bg-secondary'}">
                            ${item.service_type === 'takeaway' ? 'Para llevar' : 'En mesa'}
                        </span>
                    </div>
                    ${item.notes ? `<div class="small mt-2"><strong>Indicaciones:</strong> ${item.notes}</div>` : ''}
                </div>
                <div class="fw-bold text-success">Bs. ${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        </div>
    `).join('');

    $('#confirmOrderItems').html(itemsHtml);
}

function submitOrderAndPrint() {
    const tableId = $('#tableSelect').val();
    const printWindow = window.open('', '_blank');

    $('#confirmOrderPrint').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Enviando...');

    $.ajax({
        url: '<?php echo e(route("waiter.create-order")); ?>',
        method: 'POST',
        data: {
            table_id: tableId,
            items: orderItems
        },
        success: function(response) {
            const printUrl = '<?php echo e(route("waiter.print-order", ["id" => "__ORDER_ID__", "scope" => "main"])); ?>'
                .replace('__ORDER_ID__', response.order_id);

            if (printWindow) {
                printWindow.location = printUrl;
            } else {
                window.open(printUrl, '_blank');
            }

            confirmOrderModal.hide();
            clearOrder();
            location.reload();
        },
        error: function(xhr) {
            if (printWindow) {
                printWindow.close();
            }
            alert('Error al crear el pedido: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        },
        complete: function() {
            $('#confirmOrderPrint').prop('disabled', false).html('<i class="fas fa-print me-1"></i>Imprimir');
        }
    });
}

function clearOrder() {
    orderItems = [];
    $('#tableSelect').val('');
    $('#tableBoard .table-card').removeClass('selected');
    $('#selectedTableLabel').text('Ninguna mesa seleccionada');
    renderOrderItems();
    updateTotals();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/waiter/dashboard.blade.php ENDPATH**/ ?>