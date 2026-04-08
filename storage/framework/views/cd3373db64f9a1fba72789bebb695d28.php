?

<?php $__env->startSection('title', 'Mis Pedidos'); ?>

<?php $__env->startSection('sidebar'); ?>
<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('waiter.dashboard')); ?>">
            <i class="fas fa-home"></i> Nuevo Pedido
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="<?php echo e(route('waiter.orders')); ?>">
            <i class="fas fa-list"></i> Mis Pedidos
        </a>
    </li>
</ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-list me-2"></i>Mis Pedidos</h2>
    <a href="<?php echo e(route('waiter.dashboard')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Pedido
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
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
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><strong>#<?php echo e($order->display_number); ?></strong></td>
                        <td>
                            <span class="badge <?php echo e($order->service_mode === 'takeaway' ? 'bg-warning text-dark' : ($order->service_mode === 'mixed' ? 'bg-info text-dark' : 'bg-secondary')); ?>">
                                <?php echo e($order->service_mode_label); ?>

                            </span>
                        </td>
                        <td><?php echo e($order->table_number); ?></td>
                        <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                        <td><strong class="text-success">Bs. <?php echo e(number_format($order->total, 2)); ?></strong></td>
                        <td>
                            <?php if($order->status === 'pending'): ?>
                                <span class="badge bg-warning">Pendiente</span>
                            <?php elseif($order->status === 'processing'): ?>
                                <span class="badge bg-info">En Proceso</span>
                            <?php elseif($order->status === 'completed'): ?>
                                <span class="badge bg-success">Completado</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($order->cashier): ?>
                                <?php echo e($order->cashier->name); ?>

                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" 
                                    onclick="viewOrderDetails(<?php echo e($order->id); ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="<?php echo e(route('waiter.print-order', ['id' => $order->id, 'scope' => 'main'])); ?>"
                               class="btn btn-sm btn-outline-primary"
                               target="_blank"
                               title="Imprimir pedido principal">
                                <i class="fas fa-print"></i>
                            </a>
                            <?php if($order->can_print_added): ?>
                                <a href="<?php echo e(route('waiter.print-order', ['id' => $order->id, 'scope' => 'added'])); ?>"
                                   class="btn btn-sm btn-outline-secondary"
                                   target="_blank"
                                   title="Imprimir últimos agregados">
                                    <i class="fas fa-layer-group"></i>
                                </a>
                            <?php endif; ?>
                            <?php if($order->status === 'pending'): ?>
                            <form action="<?php echo e(route('waiter.cancel-order', $order->id)); ?>" 
                                  method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Cancelar este pedido?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No tienes pedidos registrados</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($orders->links()); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const productsCatalog = <?php echo json_encode($products, 15, 512) ?>;
let currentOrder = null;
let currentItems = [];
let pendingFoodProduct = null;
const foodNotesModal = new bootstrap.Modal(document.getElementById('foodNotesModal'));

function createCurrentItemKey(productId, notes = '', serviceType = 'dine_in') {
    return `${productId}::${String(notes || '').trim().toLowerCase()}::${serviceType}`;
}

function renderOrderDetails() {
    if (!currentOrder) {
        return;
    }

    const canEdit = currentOrder.can_edit;
    const selectedTableId = Number(currentOrder.pending_table_id || currentOrder.table_id || 0);
    const tableOptions = (currentOrder.available_tables || []).map(table => `
        <option value="${table.id}" ${selectedTableId === Number(table.id) ? 'selected' : ''}>${table.name}</option>
    `).join('');
    let rows = '';
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
    });

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
        <div class="d-flex justify-content-end gap-3">\n            <div><strong>Subtotal:</strong> Bs. ${subtotalSum.toFixed(2)}</div>\n            <div><strong>Total:</strong> Bs. ${subtotalSum.toFixed(2)}</div>\n        </div>
        ${canEdit ? `
            <hr>
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Agregar producto</label>
                    <select class="form-select form-select-sm" id="addProductSelect">
                        ${productsCatalog.map(p => `<option value="${p.id}">${p.name} (Bs. ${Number(p.price).toFixed(2)})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control form-control-sm" id="addProductQty" min="1" value="1">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-primary w-100" id="addProductBtn">
                        <i class="fas fa-plus me-1"></i>Agregar
                    </button>
                </div>
            </div>
            <div class="mt-3 text-end">
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
            const row = $(this).closest('tr');
            const itemKey = String(row.data('item-key'));
            currentItems = currentItems.filter(i => i.item_key !== itemKey);
            renderOrderDetails();
        });

        $('#addProductBtn').off('click').on('click', function() {
            const productId = parseInt($('#addProductSelect').val(), 10);
            const qty = parseInt($('#addProductQty').val(), 10);
            if (!qty || qty <= 0) {
                return;
            }

            const product = productsCatalog.find(p => p.id === productId);

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
                service_type_label: 'En mesa'
            });

            renderOrderDetails();
        });

        $('#saveOrderChanges').off('click').on('click', function() {
            $('#orderDetailsContent .qty-input').each(function() {
                const row = $(this).closest('tr');
                const itemKey = String(row.data('item-key'));
                const qty = parseInt($(this).val(), 10);
                const item = currentItems.find(i => i.item_key === itemKey);
                if (item) {
                    item.quantity = isNaN(qty) ? 0 : qty;
                }
            });

            $.ajax({
                url: '<?php echo e(route("waiter.update-order-items", 0)); ?>'.replace('/0/', '/' + currentOrder.id + '/'),
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
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
                    window.location.href = '<?php echo e(route("waiter.orders")); ?>';
                },
                error: function(xhr) {
                    alert('Error al actualizar: ' + (xhr.responseJSON?.message || 'Error desconocido'));
                }
            });
        });
    }
}

function isFoodProduct(product) {
    if (!product) {
        return false;
    }

    const categoryCode = String(product.category?.code || '').trim().toLowerCase();
    return categoryCode !== 'bebidas';
}

function addOrUpdateCurrentItem(newItem) {
    const serviceType = newItem.service_type || 'dine_in';
    const itemKey = createCurrentItemKey(newItem.product_id, newItem.notes, serviceType);
    const existing = currentItems.find(i => i.item_key === itemKey);

    if (existing) {
        existing.quantity += newItem.quantity;
        return;
    }

    currentItems.push({
        ...newItem,
        item_key: itemKey,
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
    return $('.food-note-input:checked').map(function() {
        return this.value;
    }).get().join(', ');
}

function getSelectedFoodServiceType() {
    return $('#foodServiceType').val() || 'dine_in';
}

function syncFoodNoteStyles() {
    $('.food-note-option').each(function() {
        const input = $(this).find('.food-note-input');
        $(this).toggleClass('border-success bg-success-subtle', input.is(':checked'));
    });
}

$('#foodNotesModal').on('hidden.bs.modal', function() {
    resetFoodNotesModal();
});

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
        service_type_label: getSelectedFoodServiceType() === 'takeaway' ? 'Para llevar' : 'En mesa'
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

    $.getJSON('<?php echo e(route("waiter.order-details", 0)); ?>'.replace('/0', '/' + orderId), function(data) {
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
            service_type_label: d.service_type_label || (d.service_type === 'takeaway' ? 'Para llevar' : 'En mesa')
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
</script>
<?php $__env->stopSection(); ?>










<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/waiter/orders.blade.php ENDPATH**/ ?>