?

<?php $__env->startSection('title', 'Gestión de Categorías'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Gestión de Categorías</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="fas fa-plus me-2"></i>Nueva Categoría
    </button>
</div>

<div class="row">
    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0"><?php echo e($category->name); ?></h5>
                    <span class="badge <?php echo e($category->active ? 'bg-success' : 'bg-secondary'); ?>">
                        <?php echo e($category->active ? 'Activo' : 'Inactivo'); ?>

                    </span>
                </div>
                
                <?php if($category->description): ?>
                <p class="card-text text-muted small"><?php echo e($category->description); ?></p>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="badge bg-primary">
                        <i class="fas fa-box me-1"></i>
                        <?php echo e($category->products_count); ?> productos
                    </span>
                    <div>
                        <button
                            class="btn btn-sm btn-warning"
                            data-category-id="<?php echo e($category->id); ?>"
                            data-category-name="<?php echo e($category->name); ?>"
                            data-category-description="<?php echo e($category->description); ?>"
                            onclick="editCategory(this)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="<?php echo e(route('admin.categories.delete', $category->id)); ?>" 
                              method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar esta categoría?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                <h5>No hay categorías registradas</h5>
                <p class="text-muted">Crea tu primera categoría para organizar tus productos</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fas fa-plus me-2"></i>Crear Primera Categoría
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="mt-3">
    <?php echo e($categories->links()); ?>

</div>

<!-- Modal Crear Categoría -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.categories.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
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

<!-- Modal Editar Categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function editCategory(button) {
    const id = button.dataset.categoryId;
    const name = button.dataset.categoryName || '';
    const description = button.dataset.categoryDescription || '';
    const form = document.getElementById('editCategoryForm');
    form.action = '<?php echo e(url("admin/categories")); ?>/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>
<?php $__env->stopSection(); ?>




<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/cevicheria-pos/resources/views/admin/categories/index.blade.php ENDPATH**/ ?>