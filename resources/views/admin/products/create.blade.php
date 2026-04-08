?@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('styles')
<style>
    .image-preview {
        max-width: 300px;
        max-height: 300px;
        border-radius: 12px;
        margin-top: 10px;
        display: none;
    }
</style>
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    <h2><i class="fas fa-plus-circle me-2"></i>Nuevo Producto</h2>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Producto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre del Producto *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría *</label>
                            <select name="category_id" class="form-select" id="categorySelect" required>
                                <option value="">Seleccionar...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            data-supports-infinite-stock="{{ $category->supportsInfiniteStock() ? '1' : '0' }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio (Bs. ) *</label>
                            <input type="number" name="price" class="form-control" 
                                   step="0.01" min="0" value="{{ old('price') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Inicial *</label>
                            <input type="number" name="stock" class="form-control" id="stockInput"
                                   min="0" value="{{ old('stock', 0) }}" required>
                            <small class="text-muted" id="stockHelp">Ingresa la cantidad disponible.</small>
                        </div>

                        <div class="col-md-4 mb-3 d-none" id="unlimitedStockWrapper">
                            <label class="form-label d-block">Disponibilidad</label>
                            <div class="form-check mt-2">
                                <input type="hidden" name="unlimited_stock" value="0">
                                <input type="checkbox" name="unlimited_stock" value="1" class="form-check-input" id="unlimitedStockCheckbox"
                                       {{ old('unlimited_stock') ? 'checked' : '' }}>
                                <label class="form-check-label" for="unlimitedStockCheckbox">
                                    Stock infinito
                                </label>
                            </div>
                            <small class="text-muted">Disponible para ceviches y platos de fondo.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="active" class="form-select">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Imagen del Producto</label>
                            <input type="file" name="image" class="form-control" 
                                   accept="image/*" id="imageInput">
                            <small class="text-muted">Formatos: JPG, PNG, GIF o WEBP. Tamaño máximo: 10 MB</small>
                            <div class="mt-2">
                                <img id="imagePreview" class="image-preview">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.products') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Completa todos los campos marcados con *
                </p>
                <p class="small mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    Agrega una imagen atractiva del producto
                </p>
                <p class="small mb-2">
                    <i class="fas fa-check text-success me-2"></i>
                    El producto aparecerá inmediatamente en el sistema
                </p>
                <p class="small mb-0">
                    <i class="fas fa-check text-success me-2"></i>
                    Puedes editar la información después
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Consejos</h6>
            </div>
            <div class="card-body">
                <p class="small">
                    <strong>Nombres descriptivos:</strong> Usa nombres claros y concisos que los clientes puedan identificar fácilmente.
                </p>
                <p class="small">
                    <strong>Imágenes de calidad:</strong> Las fotos de alta calidad aumentan las ventas.
                </p>
                <p class="small mb-0">
                    <strong>Precios precisos:</strong> Verifica que los precios sean correctos antes de guardar.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const categorySelect = document.getElementById('categorySelect');
    const stockInput = document.getElementById('stockInput');
    const stockHelp = document.getElementById('stockHelp');
    const unlimitedStockWrapper = document.getElementById('unlimitedStockWrapper');
    const unlimitedStockCheckbox = document.getElementById('unlimitedStockCheckbox');

    function syncInfiniteStockUi() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const supportsInfiniteStock = selectedOption?.dataset?.supportsInfiniteStock === '1';
        const useInfiniteStock = supportsInfiniteStock && unlimitedStockCheckbox.checked;

        unlimitedStockWrapper.classList.toggle('d-none', !supportsInfiniteStock);
        stockInput.readOnly = useInfiniteStock;
        stockInput.value = useInfiniteStock ? 0 : stockInput.value;
        stockHelp.textContent = useInfiniteStock
            ? 'Este producto no descontará stock al venderse.'
            : 'Ingresa la cantidad disponible.';

        if (!supportsInfiniteStock) {
            unlimitedStockCheckbox.checked = false;
        }
    }

    categorySelect.addEventListener('change', syncInfiniteStockUi);
    unlimitedStockCheckbox.addEventListener('change', syncInfiniteStockUi);
    syncInfiniteStockUi();

    // Preview de imagen
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection






