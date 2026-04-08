?@extends('layouts.app')

@section('title', 'Gestión de Categorías')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Gestión de Categorías</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="fas fa-plus me-2"></i>Nueva Categoría
    </button>
</div>

<div class="row">
    @forelse($categories as $category)
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="card-title mb-0">{{ $category->name }}</h5>
                    <span class="badge {{ $category->active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $category->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                
                @if($category->description)
                <p class="card-text text-muted small">{{ $category->description }}</p>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="badge bg-primary">
                        <i class="fas fa-box me-1"></i>
                        {{ $category->products_count }} productos
                    </span>
                    <div>
                        <button
                            class="btn btn-sm btn-warning"
                            data-category-id="{{ $category->id }}"
                            data-category-name="{{ $category->name }}"
                            data-category-description="{{ $category->description }}"
                            onclick="editCategory(this)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.categories.delete', $category->id) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
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
    @empty
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
    @endforelse
</div>

<div class="mt-3">
    {{ $categories->links() }}
</div>

<!-- Modal Crear Categoría -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
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
                @csrf
                @method('PUT')
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
@endsection

@section('scripts')
<script>
function editCategory(button) {
    const id = button.dataset.categoryId;
    const name = button.dataset.categoryName || '';
    const description = button.dataset.categoryDescription || '';
    const form = document.getElementById('editCategoryForm');
    form.action = '{{ url("admin/categories") }}/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>
@endsection



