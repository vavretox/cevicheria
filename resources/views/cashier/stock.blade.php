@extends('layouts.app')

@section('title', 'Stock Productos')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('styles')
<style>
.stock-warning-banner {
    border: 1px solid #fbbf24;
    background: #fffbeb;
    color: #92400e;
    border-radius: 14px;
    padding: 12px 14px;
}

.stock-category-nav {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.stock-category-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 700;
    white-space: nowrap;
    text-decoration: none;
}

.stock-category-chip:hover {
    background: #dbeafe;
    color: #1e40af;
}

.stock-category-card {
    border-radius: 18px;
    border: 1px solid #dbe4ee;
    overflow: hidden;
}

.stock-category-header {
    padding: 16px 18px;
    background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
    border-bottom: 1px solid #e2e8f0;
}

.stock-table .badge {
    min-width: 110px;
}

.daily-stock-card {
    border: 1px dashed #f59e0b;
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
    border-radius: 14px;
}

</style>
@endsection

@section('content')
@php
    $categoryGroups = $categories
        ->map(function ($category) {
            $products = $category->activeProducts->values();

            return (object) [
                'id' => 'category-' . $category->id,
                'name' => $category->name,
                'products' => $products,
                'count' => $products->count(),
            ];
        })
        ->filter(fn ($group) => $group->count > 0)
        ->values();

    if ($uncategorizedProducts->isNotEmpty()) {
        $categoryGroups->push((object) [
            'id' => 'category-uncategorized',
            'name' => 'Sin categoría',
            'products' => $uncategorizedProducts->values(),
            'count' => $uncategorizedProducts->count(),
        ]);
    }

    $allProducts = $categoryGroups
        ->flatMap(fn ($group) => $group->products)
        ->values();

    $lowStockProducts = $allProducts
        ->filter(fn ($product) => !$product->hasInfiniteStock() && (int) $product->stock < 10)
        ->sortBy('name')
        ->values();

    $foodCategoryCodes = ['ceviches', 'platos_de_fondo'];
@endphp

<div class="mb-4">
    <h2><i class="fas fa-boxes-stacked me-2"></i>Stock de Productos</h2>
    <p class="text-muted mb-0">Consulta rápida del inventario agrupado por categorías para caja.</p>
</div>

@if($lowStockProducts->isNotEmpty())
<div class="stock-warning-banner mb-4">
    <div class="fw-semibold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Advertencia de stock bajo</div>
    <div class="small">
        @foreach($lowStockProducts as $product)
            <span class="me-3 d-inline-block">{{ $product->name }}: {{ (int) $product->stock }} unid.</span>
        @endforeach
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Categorías disponibles</h5>
            <span class="badge bg-secondary">{{ $allProducts->count() }} productos activos</span>
        </div>
        <div class="stock-category-nav">
            @foreach($categoryGroups as $group)
                <a href="#{{ $group->id }}" class="stock-category-chip">
                    <span>{{ $group->name }}</span>
                    <span class="badge bg-light text-dark">{{ $group->count }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@forelse($categoryGroups as $group)
<div class="card mb-4 stock-category-card" id="{{ $group->id }}">
    <div class="stock-category-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">{{ $group->name }}</h5>
            <p class="text-muted mb-0">Productos visibles de esta categoría para consulta rápida en caja.</p>
        </div>
        <span class="badge bg-dark">{{ $group->count }} productos</span>
    </div>
    <div class="card-body">
        @if(in_array(optional($group->products->first()?->category)->code, $foodCategoryCodes, true))
        <div class="daily-stock-card p-3 mb-4">
            <div class="fw-semibold mb-1"><i class="fas fa-calendar-day me-2"></i>Registrar stock del día</div>
            <div class="small text-muted mb-3">El cajero puede cargar o corregir el stock diario de productos alimenticios desde aquí.</div>
            <form action="{{ route('cashier.stock.food.update') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-lg-4">
                    <label class="form-label">Producto</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">Selecciona un producto</option>
                        @foreach($group->products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Stock del día</label>
                    <input type="number" name="stock" min="0" class="form-control" required>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="stock_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-lg-3">
                    <label class="form-label">Nota</label>
                    <input type="text" name="notes" class="form-control" placeholder="Apertura, reajuste, etc.">
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save"></i>
                    </button>
                </div>
            </form>
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-sm align-middle stock-table mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Stock</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="text-end">Bs. {{ number_format($product->price, 2) }}</td>
                        <td class="text-end">
                            @if($product->hasInfiniteStock())
                                Ilimitado
                            @else
                                {{ (int) $product->stock }}
                            @endif
                        </td>
                        <td class="text-center">
                            @if($product->hasInfiniteStock())
                                <span class="badge bg-info text-dark">Sin control</span>
                            @elseif((int) $product->stock <= 0)
                                <span class="badge bg-danger">Agotado</span>
                            @elseif((int) $product->stock < 10)
                                <span class="badge bg-warning text-dark">Por acabarse</span>
                            @else
                                <span class="badge bg-success">Disponible</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center text-muted py-4">
        No hay productos activos para mostrar.
    </div>
</div>
@endforelse

@if($foodStockHistories->isNotEmpty())
<div class="card stock-category-card">
    <div class="stock-category-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">Historial diario de cambios</h5>
            <p class="text-muted mb-0">Últimos registros de stock diario para ceviches y platos de fondo.</p>
        </div>
        <span class="badge bg-dark">{{ $foodStockHistories->count() }} cambios</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha stock</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Antes</th>
                        <th class="text-end">Después</th>
                        <th>Registró</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($foodStockHistories as $history)
                    <tr>
                        <td>{{ $history->stock_date?->format('d/m/Y') }}</td>
                        <td>{{ $history->product?->name ?? 'Producto' }}</td>
                        <td>{{ $history->product?->category?->name ?? '-' }}</td>
                        <td class="text-end">{{ (int) $history->stock_before }}</td>
                        <td class="text-end fw-semibold">{{ (int) $history->stock_after }}</td>
                        <td>{{ $history->user?->name ?? '-' }}</td>
                        <td>{{ $history->notes ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<small class="text-muted d-block mt-2">Referencia: por debajo de 10 unidades se considera stock bajo. Los productos sin control de stock se muestran como ilimitados.</small>
@endsection
