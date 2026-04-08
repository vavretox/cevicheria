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

.stock-table .badge {
    min-width: 96px;
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
    <h2><i class="fas fa-boxes-stacked me-2"></i>Stock de Productos</h2>
    <p class="text-muted mb-0">Consulta rápida del inventario visible para caja.</p>
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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Listado de stock</h5>
        <span class="badge bg-secondary">{{ $products->count() }} productos</span>
    </div>
    <div class="card-body">
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
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="text-end">Bs. {{ number_format($product->price, 2) }}</td>
                        <td class="text-end">{{ (int) $product->stock }}</td>
                        <td class="text-center">
                            @if((int) $product->stock <= 0)
                                <span class="badge bg-danger">Agotado</span>
                            @elseif((int) $product->stock < 10)
                                <span class="badge bg-warning text-dark">Por acabarse</span>
                            @else
                                <span class="badge bg-success">Disponible</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No hay productos activos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <small class="text-muted d-block mt-2">Referencia: por debajo de 10 unidades se considera stock bajo.</small>
    </div>
</div>
@endsection
