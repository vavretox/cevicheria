?

<?php $__env->startSection('title', 'Gestión de Productos'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .product-image-small {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-box me-2"></i>Gestión de Productos</h2>
    <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Producto
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <img src="<?php echo e($product->image_url); ?>" 
                                 class="product-image-small"
                                 onerror="this.src='https://via.placeholder.com/60?text=?'">
                        </td>
                        <td>
                            <strong><?php echo e($product->name); ?></strong>
                            <?php if($product->description): ?>
                            <br><small class="text-muted"><?php echo e(Str::limit($product->description, 40)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo e($product->category->name); ?></span>
                        </td>
                        <td>
                            <strong class="text-success">Bs. <?php echo e(number_format($product->price, 2)); ?></strong>
                        </td>
                        <td>
                            <?php if($product->hasInfiniteStock()): ?>
                                <span class="badge bg-primary">Infinito</span>
                            <?php elseif($product->stock > 10): ?>
                                <span class="badge bg-success"><?php echo e($product->stock); ?></span>
                            <?php elseif($product->stock > 0): ?>
                                <span class="badge bg-warning"><?php echo e($product->stock); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo e($product->stock); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($product->active): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="<?php echo e(route('admin.products.delete', $product->id)); ?>" 
                                  method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Eliminar este producto?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted">No hay productos registrados</p>
                            <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Crear Primer Producto
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?php echo e($products->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>








<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/products/index.blade.php ENDPATH**/ ?>