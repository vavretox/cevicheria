<?php $__env->startSection('title', 'Almacén de Bebidas'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .warehouse-summary-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .warehouse-summary-number {
        font-size: 1.9rem;
        font-weight: 800;
        color: #0f172a;
    }

    .warehouse-entry-card {
        border: 1px solid #dbeafe;
        border-radius: 18px;
        background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
    }

    .warehouse-exit-card {
        border: 1px solid #fecaca;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff1f2 0%, #ffffff 100%);
    }

    .stock-pill {
        min-width: 88px;
        display: inline-flex;
        justify-content: center;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1"><i class="fas fa-warehouse me-2"></i>Almacén de Bebidas</h2>
        <div class="text-muted">Entradas y salidas de inventario solo para productos de la categoría Bebidas.</div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.warehouse.beverages.print')); ?>" class="btn btn-dark" target="_blank">
            <i class="fas fa-print me-2"></i>Imprimir historial
        </a>
        <a href="<?php echo e(route('admin.products')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-box me-2"></i>Ver productos
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="warehouse-summary-card p-3 h-100">
            <div class="text-muted small mb-2">Productos de bebidas</div>
            <div class="warehouse-summary-number"><?php echo e($beverageProducts->count()); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="warehouse-summary-card p-3 h-100">
            <div class="text-muted small mb-2">Unidades en stock</div>
            <div class="warehouse-summary-number"><?php echo e(number_format($totalBeverageUnits)); ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="warehouse-summary-card p-3 h-100">
            <div class="text-muted small mb-2">Valor referencial de venta</div>
            <div class="warehouse-summary-number">Bs. <?php echo e(number_format($inventorySaleValue, 2)); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="warehouse-summary-card p-3 h-100">
            <div class="text-muted small mb-2">Entradas registradas</div>
            <div class="warehouse-summary-number"><?php echo e(number_format($entryUnits)); ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="warehouse-summary-card p-3 h-100">
            <div class="text-muted small mb-2">Salidas registradas</div>
            <div class="warehouse-summary-number"><?php echo e(number_format($exitUnits)); ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card warehouse-entry-card">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar entrada</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.warehouse.beverages.entries.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Producto bebida</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Selecciona una bebida</option>
                            <?php $__currentLoopData = $beverageProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product->id); ?>" <?php echo e(old('product_id') == $product->id ? 'selected' : ''); ?>>
                                    <?php echo e($product->name); ?> | Stock actual: <?php echo e($product->stock); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de entrada</label>
                        <select name="entry_type" class="form-select" id="entryTypeSelect" required>
                            <option value="unit" <?php echo e(old('entry_type', 'unit') === 'unit' ? 'selected' : ''); ?>>Unidad</option>
                            <option value="box" <?php echo e(old('entry_type') === 'box' ? 'selected' : ''); ?>>Caja</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="quantity" min="1" class="form-control" value="<?php echo e(old('quantity', 1)); ?>" required>
                        </div>
                        <div class="col-md-6" id="unitsPerBoxGroup" style="display:none;">
                            <label class="form-label">Unidades por caja</label>
                            <input type="number" name="units_per_box" min="1" class="form-control" id="unitsPerBoxInput" value="<?php echo e(old('units_per_box')); ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" id="purchasePriceLabel">Precio de compra por unidad</label>
                        <input type="number" name="purchase_price" min="0" step="0.01" class="form-control" value="<?php echo e(old('purchase_price')); ?>" required>
                        <small class="text-muted d-block mt-1" id="purchasePriceHelp">
                            Ingresa el costo de compra por cada unidad que entra al almacén.
                        </small>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Observación</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Proveedor, lote, comentario interno..."><?php echo e(old('notes')); ?></textarea>
                    </div>
                    <div class="mt-3 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar entrada
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card warehouse-exit-card mt-4">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fas fa-arrow-up-from-bracket me-2"></i>Registrar salida</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('admin.warehouse.beverages.exits.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Producto bebida</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Selecciona una bebida</option>
                            <?php $__currentLoopData = $beverageProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($product->id); ?>">
                                    <?php echo e($product->name); ?> | Stock actual: <?php echo e($product->stock); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de salida</label>
                        <select name="entry_type" class="form-select" id="exitTypeSelect" required>
                            <option value="unit">Unidad</option>
                            <option value="box">Caja</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="quantity" min="1" class="form-control" value="1" required>
                        </div>
                        <div class="col-md-6" id="exitUnitsPerBoxGroup" style="display:none;">
                            <label class="form-label">Unidades por caja</label>
                            <input type="number" name="units_per_box" min="1" class="form-control" id="exitUnitsPerBoxInput">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Motivo de salida</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Venta externa, rotura, devolución, consumo interno..." required></textarea>
                    </div>
                    <div class="mt-3 d-grid">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-minus-circle me-2"></i>Guardar salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wine-bottle me-2"></i>Stock de bebidas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precios</th>
                                <th>Stock actual</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $beverageProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($product->name); ?></div>
                                        <div class="text-muted small"><?php echo e($product->description ?: 'Sin descripción'); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-success">Venta: Bs. <?php echo e(number_format($product->price, 2)); ?></div>
                                        <div class="small text-muted">
                                            Última compra:
                                            <?php if($product->latestBeverageEntry): ?>
                                                Bs. <?php echo e(number_format((float) $product->latestBeverageEntry->purchase_price, 2)); ?>

                                            <?php else: ?>
                                                Sin registro
                                            <?php endif; ?>
                                        </div>
                                        <?php if($product->latestBeverageEntry): ?>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm mt-2 edit-purchase-price-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPurchasePriceModal"
                                                data-entry-id="<?php echo e($product->latestBeverageEntry->id); ?>"
                                                data-product-name="<?php echo e($product->name); ?>"
                                                data-purchase-price="<?php echo e(number_format((float) $product->latestBeverageEntry->purchase_price, 2, '.', '')); ?>"
                                            >
                                                <i class="fas fa-pen-to-square me-1"></i>Acción
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge stock-pill <?php echo e($product->stock > 24 ? 'bg-success' : ($product->stock > 0 ? 'bg-warning text-dark' : 'bg-danger')); ?>">
                                            <?php echo e($product->stock); ?> unid.
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($product->active ? 'bg-primary' : 'bg-secondary'); ?>">
                                            <?php echo e($product->active ? 'Activo' : 'Inactivo'); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No hay productos en la categoría Bebidas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Historial de movimientos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Movimiento</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Unidades</th>
                                <th>Detalle</th>
                                <th>Registró</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($movement->created_at->format('d/m/Y H:i')); ?></td>
                                    <td>
                                        <span class="badge <?php echo e($movement->movement_type === 'exit' ? 'bg-danger' : 'bg-success'); ?>">
                                            <?php echo e($movement->movement_type_label); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo e($movement->product?->name ?? 'Producto'); ?></div>
                                        <?php if($movement->notes): ?>
                                            <div class="text-muted small"><?php echo e($movement->notes); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo e($movement->entry_type === 'box' ? 'bg-info text-dark' : 'bg-secondary'); ?>">
                                            <?php echo e($movement->entry_type_label); ?>

                                        </span>
                                        <div class="small text-muted mt-1">
                                            <?php echo e($movement->quantity); ?>

                                            <?php if($movement->entry_type === 'box'): ?>
                                                caja(s) de <?php echo e($movement->units_per_box); ?> unid.
                                            <?php else: ?>
                                                unidad(es)
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="fw-semibold <?php echo e($movement->movement_type === 'exit' ? 'text-danger' : 'text-success'); ?>">
                                        <?php echo e($movement->movement_type === 'exit' ? '-' : '+'); ?><?php echo e($movement->total_units); ?> unid.
                                    </td>
                                    <td>
                                        <?php if($movement->movement_type === 'entry'): ?>
                                            <div>Compra: Bs. <?php echo e(number_format($movement->purchase_price, 2)); ?></div>
                                            <div class="small text-muted">Total: Bs. <?php echo e(number_format($movement->total_cost, 2)); ?></div>
                                        <?php else: ?>
                                            <div class="text-muted small">Salida manual de stock</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($movement->user?->name ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Aún no hay movimientos registrados en el almacén de bebidas.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPurchasePriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="editPurchasePriceForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen-to-square me-2"></i>Modificar precio de compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="editPurchasePriceProductName" readonly>
                    </div>
                    <div>
                        <label class="form-label">Precio de la última compra</label>
                        <div class="input-group">
                            <span class="input-group-text">Bs.</span>
                            <input
                                type="number"
                                name="purchase_price"
                                min="0"
                                step="0.01"
                                class="form-control"
                                id="editPurchasePriceInput"
                                required
                            >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function syncWarehouseEntryType() {
    const type = document.getElementById('entryTypeSelect').value;
    const isBox = type === 'box';
    const unitsPerBoxGroup = document.getElementById('unitsPerBoxGroup');
    const unitsPerBoxInput = document.getElementById('unitsPerBoxInput');
    const purchasePriceLabel = document.getElementById('purchasePriceLabel');
    const purchasePriceHelp = document.getElementById('purchasePriceHelp');

    unitsPerBoxGroup.style.display = isBox ? '' : 'none';
    unitsPerBoxInput.required = isBox;

    purchasePriceLabel.textContent = isBox ? 'Precio de compra por caja' : 'Precio de compra por unidad';
    purchasePriceHelp.textContent = isBox
        ? 'Ingresa cuánto costó cada caja. El sistema convertirá eso a unidades para el stock.'
        : 'Ingresa el costo de compra por cada unidad que entra al almacén.';
}

function syncWarehouseExitType() {
    const type = document.getElementById('exitTypeSelect').value;
    const isBox = type === 'box';
    const unitsPerBoxGroup = document.getElementById('exitUnitsPerBoxGroup');
    const unitsPerBoxInput = document.getElementById('exitUnitsPerBoxInput');

    unitsPerBoxGroup.style.display = isBox ? '' : 'none';
    unitsPerBoxInput.required = isBox;
}

document.getElementById('entryTypeSelect').addEventListener('change', syncWarehouseEntryType);
document.getElementById('exitTypeSelect').addEventListener('change', syncWarehouseExitType);
syncWarehouseEntryType();
syncWarehouseExitType();

document.querySelectorAll('.edit-purchase-price-btn').forEach((button) => {
    button.addEventListener('click', function () {
        const entryId = this.dataset.entryId;
        const productName = this.dataset.productName || '';
        const purchasePrice = this.dataset.purchasePrice || '';
        const actionTemplate = <?php echo json_encode(route('admin.warehouse.beverages.entries.update-price', '__ENTRY__'), 512) ?>;

        document.getElementById('editPurchasePriceForm').action = actionTemplate.replace('__ENTRY__', entryId);
        document.getElementById('editPurchasePriceProductName').value = productName;
        document.getElementById('editPurchasePriceInput').value = purchasePrice;
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/warehouse/beverages.blade.php ENDPATH**/ ?>