@extends('layouts.app')

@section('title', 'Panel Mesero')

@section('sidebar')
<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('waiter.dashboard') }}">
            <i class="fas fa-home"></i> Nuevo Pedido
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('waiter.orders') }}">
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

@section('styles')
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

    .product-card.is-out-of-stock {
        opacity: 0.62;
        border-color: #ef4444;
        background: #f8fafc;
    }

    .product-card.is-out-of-stock .product-name,
    .product-card.is-out-of-stock .product-price {
        color: #9ca3af;
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

    .stock-warning-banner {
        border: 1px solid #fbbf24;
        background: #fffbeb;
        color: #92400e;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .stock-chip-low {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 8px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #fdba74;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .category-filters-scroll {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .category-filters-scroll .btn {
        white-space: nowrap;
    }

    .mobile-cart-spacer {
        display: none;
    }

    .mobile-cart-sticky {
        display: none;
    }

    @media (max-width: 991.98px) {
        .mobile-cart-spacer {
            display: block;
            height: 88px;
        }

        .mobile-cart-sticky {
            display: block;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1040;
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -8px 30px rgba(15, 23, 42, 0.12);
            padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
        }
    }
</style>
@endsection

@section('content')
@php
    $trackedStockProducts = $categories
        ->flatMap(fn ($category) => $category->activeProducts);

    $lowStockProducts = $trackedStockProducts
        ->filter(fn ($product) => (int) $product->stock < 10)
        ->sortBy('name')
        ->values();
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-shopping-cart me-2"></i>Nuevo Pedido</h2>
    <div>
        <span class="badge bg-success fs-6">
            <i class="fas fa-user me-2"></i>{{ Auth::user()->name }}
        </span>
    </div>
</div>

@if($lowStockProducts->isNotEmpty())
<div class="stock-warning-banner mb-4">
    <div class="fw-semibold mb-1"><i class="fas fa-triangle-exclamation me-2"></i>Advertencia: productos con stock crítico</div>
    <div class="small">
        @foreach($lowStockProducts as $product)
            <span class="me-3 d-inline-block">{{ $product->name }}: {{ $product->stock }} unid.</span>
        @endforeach
    </div>
</div>
@endif

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
                        <button class="btn btn-outline-secondary" type="button" id="clearProductSearch" title="Limpiar búsqueda">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabs de Categorías -->
                <ul class="nav nav-pills mb-4" id="categoryTabs" role="tablist">
                    @foreach($categories as $index => $category)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link category-tab-btn {{ $index === 0 ? 'active' : '' }}" 
                                id="cat-{{ $category->id }}-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#cat-{{ $category->id }}" 
                                type="button">
                            {{ $category->name }}
                            <span class="badge bg-light text-dark ms-2">{{ $category->activeProducts->count() }}</span>
                        </button>
                    </li>
                    @endforeach
                </ul>

                <!-- Contenido de Categorías -->
                <div class="tab-content" id="categoryTabsContent">
                    @foreach($categories as $index => $category)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                         id="cat-{{ $category->id }}">
                        <div class="row g-3">
                            @forelse($category->activeProducts as $product)
                            <div class="col-md-4 col-lg-3">
                                <div class="card product-card {{ (int) $product->stock <= 0 ? 'is-out-of-stock' : '' }}" 
                                     data-product-id="{{ $product->id }}"
                                     data-product-name="{{ $product->name }}"
                                     data-product-description="{{ $product->description ?? '' }}"
                                     data-product-price="{{ $product->price }}"
                                     data-category-code="{{ $product->category->code ?? '' }}"
                                     data-stock="{{ (int) $product->stock }}"
                                     data-low-stock="{{ (int) $product->stock < 10 ? '1' : '0' }}"
                                     data-out-of-stock="{{ (int) $product->stock <= 0 ? '1' : '0' }}"
                                     data-is-ceviche="{{ str_contains(Str::lower($product->category->code ?? ''), 'cevich') ? '1' : '0' }}">
                                    <img src="{{ $product->image_url }}" 
                                         class="product-image" 
                                         alt="{{ $product->name }}"
                                         onerror="this.src='https://via.placeholder.com/300x180?text=Sin+Imagen'">
                                    <div class="product-info text-center">
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="product-price">Bs. {{ number_format($product->price, 2) }}</div>
                                        @if((int) $product->stock < 10)
                                            <div class="stock-chip-low">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                {{ (int) $product->stock <= 0 ? 'Agotado' : 'Stock bajo: ' . (int) $product->stock }}
                                            </div>
                                        @endif
                                        @if($product->description)
                                        <small class="text-muted d-block mt-2">{{ Str::limit($product->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay productos disponibles en esta categoría
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
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
                    @if($availableTables->isEmpty())
                        <small class="text-danger d-block mt-2">No hay mesas disponibles. Crea o habilita mesas desde administración.</small>
                    @else
                        <small class="text-muted d-block mt-2">Usa el selector para ver el tablero completo de mesas.</small>
                    @endif
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

                <button id="sendOrder" class="btn btn-success w-100 mb-2" {{ $availableTables->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane me-2"></i>Enviar Pedido
                </button>
                <button id="clearOrder" class="btn btn-outline-danger w-100">
                    <i class="fas fa-trash me-2"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="mobile-cart-spacer"></div>
<div class="mobile-cart-sticky">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <div class="small text-muted">Resumen rápido</div>
            <div class="fw-bold"><span id="mobileItemsCount">0</span> item(s)</div>
        </div>
        <div class="text-end">
            <div class="small text-muted">Total</div>
            <div class="fw-bold text-danger" id="mobileTotalAmount">Bs. 0.00</div>
        </div>
    </div>
    <div class="d-grid gap-2">
        <button type="button" id="mobileGoToSummary" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-receipt me-1"></i>Ver resumen
        </button>
        <button type="button" id="mobileSendOrder" class="btn btn-success btn-sm" {{ $availableTables->isEmpty() ? 'disabled' : '' }}>
            <i class="fas fa-paper-plane me-1"></i>Enviar pedido
        </button>
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
                    @foreach($tableBoard as $table)
                        @php
                            $status = $table->ui_status;
                            $selectable = in_array($status, ['available', 'reserved'], true);
                        @endphp
                        <button
                            type="button"
                            class="table-card {{ $status }} {{ $selectable ? '' : 'is-disabled' }}"
                            data-table-id="{{ $table->id }}"
                            data-table-name="{{ $table->name }}"
                            data-selectable="{{ $selectable ? '1' : '0' }}"
                        >
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong>{{ $table->name }}</strong>
                                <span class="table-card-status">
                                    @if($status === 'available')
                                        Libre
                                    @elseif($status === 'reserved')
                                        Reservada
                                    @elseif($status === 'occupied')
                                        Ocupada
                                    @else
                                        Cerrada
                                    @endif
                                </span>
                            </div>
                            @if($table->zone)
                                <div class="small text-muted mb-1">{{ $table->zone }}</div>
                            @endif
                            @if($table->isReserved())
                                <div class="small text-muted">{{ $table->reservation_name }}</div>
                                <div class="small text-muted">{{ $table->reservation_at?->format('d/m H:i') }}</div>
                            @endif
                        </button>
                    @endforeach
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

<div class="modal fade" id="kitchenPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-print me-2"></i>Impresión de cocina
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success mb-3">
                    <i class="fas fa-circle-check me-2"></i>Pedido creado correctamente.
                    <div class="small mt-1" id="kitchenPrintStatus">Cargando vista para imprimir...</div>
                </div>
                <div class="ratio ratio-4x3 border rounded overflow-hidden">
                    <iframe id="kitchenPrintFrame" title="Comanda de cocina"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" class="btn btn-outline-primary" id="retryKitchenLoad" style="display: none;">
                    <i class="fas fa-rotate-right me-1"></i>Reintentar cargar
                </button>
                <button type="button" class="btn btn-success" id="printKitchenNow" disabled>
                    <i class="fas fa-print me-1"></i>Imprimir cocina
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="appToastContainer" style="z-index: 1100;"></div>
@endsection

@section('scripts')
<script>
let orderItems = [];
let pendingFoodProduct = null;
let isSubmittingOrder = false;
let shouldReloadAfterKitchenModalClose = false;
let currentKitchenPrintUrl = '';
let kitchenFrameLoaded = false;
let kitchenLoadTimeoutId = null;
let shouldAutoPrintKitchen = false;
let autoCloseKitchenModalTimeoutId = null;
const cevicheOptionsModal = new bootstrap.Modal(document.getElementById('cevicheOptionsModal'));
const tablePickerModal = new bootstrap.Modal(document.getElementById('tablePickerModal'));
const confirmOrderModal = new bootstrap.Modal(document.getElementById('confirmOrderModal'));
const kitchenPrintModal = new bootstrap.Modal(document.getElementById('kitchenPrintModal'));
const kitchenPrintFrame = document.getElementById('kitchenPrintFrame');

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
        const isLowStock = String($(this).data('low-stock') || '0') === '1';
        const isOutOfStock = String($(this).data('out-of-stock') || '0') === '1';
        const currentStock = parseInt($(this).data('stock') || 0, 10);
        const orderedQty = getCurrentOrderedQtyByProduct(productId);
        const availableQty = Math.max(0, (Number.isNaN(currentStock) ? 0 : currentStock) - orderedQty);

        if (isOutOfStock || availableQty <= 0) {
            showToast('danger', `${productName} está agotado. Ya no hay stock disponible.`);
            return;
        }

        if (isLowStock) {
            showToast('warning', `Quedan pocos platos de ${productName}. Stock disponible: ${availableQty} unidad(es).`);
        }

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
        openConfirmOrderModal();
    });

    $('#confirmOrderPrint').click(function() {
        if (isSubmittingOrder) {
            return;
        }
        submitOrderAndPrint();
    });

    $('#printKitchenNow').on('click', function() {
        printKitchenTicket();
    });

    $('#retryKitchenLoad').on('click', function() {
        retryKitchenPrintLoad();
    });

    if (kitchenPrintFrame) {
        kitchenPrintFrame.addEventListener('load', function() {
            kitchenFrameLoaded = true;
            clearKitchenLoadTimeout();
            $('#kitchenPrintStatus').text('Vista lista. Puedes imprimir la comanda.');
            $('#printKitchenNow').prop('disabled', false);
            $('#retryKitchenLoad').hide();

            if (shouldAutoPrintKitchen) {
                setTimeout(function() {
                    printKitchenTicket(true);
                }, 200);
            }
        });

        kitchenPrintFrame.addEventListener('error', function() {
            handleKitchenFrameLoadError();
        });
    }

    $('#kitchenPrintModal').on('hidden.bs.modal', function() {
        clearKitchenLoadTimeout();
        clearAutoCloseKitchenModalTimeout();
        kitchenFrameLoaded = false;
        currentKitchenPrintUrl = '';
        shouldAutoPrintKitchen = false;
        $('#retryKitchenLoad').hide();

        if (kitchenPrintFrame) {
            kitchenPrintFrame.src = 'about:blank';
        }

        if (shouldReloadAfterKitchenModalClose) {
            shouldReloadAfterKitchenModalClose = false;
            location.reload();
        }
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

    $('#clearProductSearch').on('click', function() {
        $('#productSearchInput').val('');
        filterProducts('');
        $('#productSearchInput').trigger('focus');
    });

    $('#quickCategoryFilters').on('click', '.quick-category-btn', function() {
        const categoryTarget = String($(this).data('category-target') || '');
        activateQuickCategory(categoryTarget);
    });

    $('#mobileGoToSummary').on('click', function() {
        const summaryCard = document.querySelector('.order-summary');
        if (summaryCard) {
            summaryCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    $('#mobileSendOrder').on('click', function() {
        openConfirmOrderModal();
    });

    $('#categoryTabs button[data-bs-toggle="pill"]').on('shown.bs.tab', function() {
        const tabTarget = String($(this).data('bs-target') || '').replace('#', '');
        syncQuickCategorySelection(tabTarget);
    });

    updateStickyCartSummary();
});

function filterProducts(searchTerm) {
    const normalizedTerm = String(searchTerm || '').trim().toLowerCase();
    const searchWords = normalizedTerm === ''
        ? []
        : normalizedTerm.split(/\s+/).filter(Boolean);

    let firstTabWithResults = null;
    let totalMatches = 0;

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
                totalMatches += 1;
            }
        });

        if (!firstTabWithResults && visibleCards > 0) {
            firstTabWithResults = $(this).attr('id');
        }

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

    if (normalizedTerm !== '' && firstTabWithResults) {
        activateCategoryTab(firstTabWithResults);
    } else if (normalizedTerm === '') {
        $('.tab-pane .product-search-empty').remove();
    }

    return totalMatches;
}

function activateCategoryTab(targetId) {
    const tabButton = document.querySelector(`#${targetId}-tab`);
    if (tabButton) {
        bootstrap.Tab.getOrCreateInstance(tabButton).show();
    }
}

function activateQuickCategory(categoryTarget) {
    if (!categoryTarget) {
        return;
    }

    $('.quick-category-btn').removeClass('active');
    $(`.quick-category-btn[data-category-target="${categoryTarget}"]`).addClass('active');

    if (categoryTarget === 'all') {
        const firstTabId = $('#categoryTabs button[data-bs-toggle="pill"]').first().data('bs-target');
        if (firstTabId) {
            activateCategoryTab(String(firstTabId).replace('#', ''));
        }
        return;
    }

    activateCategoryTab(categoryTarget);
}

function syncQuickCategorySelection(tabTarget) {
    if (!tabTarget) {
        return;
    }

    $('.quick-category-btn').removeClass('active');
    const exactButton = $(`.quick-category-btn[data-category-target="${tabTarget}"]`);
    if (exactButton.length) {
        exactButton.addClass('active');
    }
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
    const stockLimit = getStockLimitForProduct(productId);
    const currentQtyByProduct = getCurrentOrderedQtyByProduct(productId);

    if (Number.isInteger(stockLimit) && currentQtyByProduct >= stockLimit) {
        showToast('danger', `${productName} ya no tiene stock disponible para este pedido.`);
        return;
    }

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

function getCurrentOrderedQtyByProduct(productId) {
    return orderItems
        .filter(item => Number(item.product_id) === Number(productId))
        .reduce((sum, item) => sum + Number(item.quantity || 0), 0);
}

function getStockLimitForProduct(productId) {
    const card = $(`.product-card[data-product-id="${productId}"]`).first();
    if (!card.length) {
        return null;
    }

    const stock = parseInt(card.data('stock'), 10);
    return Number.isNaN(stock) ? null : stock;
}

function removeFromOrder(itemKey) {
    orderItems = orderItems.filter(item => item.item_key !== itemKey);
    renderOrderItems();
    updateTotals();
}

function updateQuantity(itemKey, quantity) {
    const item = orderItems.find(item => item.item_key === itemKey);
    if (item) {
        const requestedQty = parseInt(quantity, 10);
        if (Number.isNaN(requestedQty) || requestedQty <= 0) {
            removeFromOrder(itemKey);
            return;
        }

        const stockLimit = getStockLimitForProduct(item.product_id);
        if (Number.isInteger(stockLimit)) {
            const qtyFromOtherRows = getCurrentOrderedQtyByProduct(item.product_id) - Number(item.quantity || 0);
            const maxForThisRow = Math.max(0, stockLimit - qtyFromOtherRows);

            if (requestedQty > maxForThisRow) {
                if (maxForThisRow <= 0) {
                    showToast('danger', `${item.name} está agotado para este pedido.`);
                    removeFromOrder(itemKey);
                    return;
                }

                item.quantity = maxForThisRow;
                showToast('warning', `${item.name}: solo puedes llegar hasta ${maxForThisRow} unidad(es) en este pedido.`);
                renderOrderItems();
                updateTotals();
                return;
            }
        }

        item.quantity = requestedQty;
        renderOrderItems();
        updateTotals();
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
        $('#mobileSendOrder').prop('disabled', true);
        updateStickyCartSummary();
        return;
    }

    $('#sendOrder').prop('disabled', false);
    $('#mobileSendOrder').prop('disabled', false);
    let html = '';

    orderItems.forEach(item => {
        const stockLimit = getStockLimitForProduct(item.product_id);
        const maxReached = Number.isInteger(stockLimit) && item.quantity >= stockLimit;
        const qtyMaxAttr = Number.isInteger(stockLimit) ? `max="${stockLimit}"` : '';
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
                           value="${item.quantity}" min="1" ${qtyMaxAttr}
                           onchange="updateQuantity('${item.item_key}', this.value)">
                    <button class="btn btn-sm btn-outline-secondary" ${maxReached ? 'disabled' : ''} ${maxReached ? 'title="Stock límite alcanzado"' : ''}
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
    updateStickyCartSummary();
}

function updateTotals() {
    const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const total = subtotal;

    $('#subtotal').text('Bs. ' + subtotal.toFixed(2));
    $('#total').text('Bs. ' + total.toFixed(2));
    $('#mobileTotalAmount').text('Bs. ' + total.toFixed(2));
}

function updateStickyCartSummary() {
    const itemsCount = orderItems.reduce((sum, item) => sum + item.quantity, 0);
    $('#mobileItemsCount').text(itemsCount);
    $('#mobileSendOrder').prop('disabled', itemsCount === 0 || {{ $availableTables->isEmpty() ? 'true' : 'false' }});
}

function openConfirmOrderModal() {
    if (isSubmittingOrder) {
        return;
    }

    const tableId = $('#tableSelect').val();
    if (!tableId) {
        showToast('warning', 'Por favor selecciona una mesa.');
        return;
    }

    if (orderItems.length === 0) {
        showToast('warning', 'Agrega productos al pedido.');
        return;
    }

    renderConfirmOrderSummary();
    confirmOrderModal.show();
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
    if (isSubmittingOrder) {
        return;
    }

    const tableId = $('#tableSelect').val();
    isSubmittingOrder = true;

    setSubmittingState(true);

    $.ajax({
        url: '{{ route("waiter.create-order") }}',
        method: 'POST',
        data: {
            table_id: tableId,
            items: orderItems
        },
        success: function(response) {
            const printUrl = '{{ route("waiter.print-order", ["id" => "__ORDER_ID__", "scope" => "main"]) }}'
                .replace('__ORDER_ID__', response.order_id);

            shouldReloadAfterKitchenModalClose = true;
            $('#printKitchenNow').prop('disabled', true);
            $('#retryKitchenLoad').hide();
            $('#kitchenPrintStatus').text('Cargando vista para imprimir...');
            kitchenFrameLoaded = false;
            currentKitchenPrintUrl = printUrl;
            shouldAutoPrintKitchen = true;
            startKitchenLoadTimeout();
            if (kitchenPrintFrame) {
                kitchenPrintFrame.src = printUrl;
            }

            confirmOrderModal.hide();
            clearOrder();
            kitchenPrintModal.show();
            showToast('success', 'Pedido creado. Prepara la impresión de cocina.');
        },
        error: function(xhr) {
            showToast('danger', 'Error al crear el pedido: ' + (xhr.responseJSON?.message || 'Error desconocido'));
        },
        complete: function() {
            isSubmittingOrder = false;
            setSubmittingState(false);
            if (orderItems.length > 0) {
                $('#sendOrder').prop('disabled', false);
                $('#mobileSendOrder').prop('disabled', false);
            }
        }
    });
}

function printKitchenTicket(isAuto = false) {
    if (!kitchenPrintFrame || !kitchenPrintFrame.contentWindow || !kitchenFrameLoaded) {
        if (!isAuto) {
            showToast('warning', 'La comanda aún no está lista. Intenta nuevamente.');
        }
        return;
    }

    try {
        kitchenPrintFrame.contentWindow.focus();
        kitchenPrintFrame.contentWindow.print();
        shouldAutoPrintKitchen = false;
        if (isAuto) {
            showToast('info', 'Se abrió la impresión automática de cocina.');
            scheduleKitchenModalAutoClose();
        }
    } catch (error) {
        if (isAuto) {
            showToast('warning', 'No se pudo imprimir automáticamente. Usa "Imprimir cocina".');
        } else {
            showToast('danger', 'No se pudo abrir la impresión. Usa "Reintentar cargar".');
        }
    }
}

function clearOrder() {
    orderItems = [];
    $('#tableSelect').val('');
    $('#tableBoard .table-card').removeClass('selected');
    $('#selectedTableLabel').text('Ninguna mesa seleccionada');
    renderOrderItems();
    updateTotals();
    updateStickyCartSummary();
}

function setSubmittingState(isLoading) {
    if (isLoading) {
        $('#confirmOrderPrint')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i>Enviando...');
        $('#sendOrder, #mobileSendOrder').prop('disabled', true);
        return;
    }

    $('#confirmOrderPrint')
        .prop('disabled', false)
        .html('<i class="fas fa-print me-1"></i>Imprimir');
}

function startKitchenLoadTimeout() {
    clearKitchenLoadTimeout();
    kitchenLoadTimeoutId = setTimeout(function() {
        if (!kitchenFrameLoaded) {
            handleKitchenFrameLoadError();
        }
    }, 8000);
}

function clearKitchenLoadTimeout() {
    if (kitchenLoadTimeoutId) {
        clearTimeout(kitchenLoadTimeoutId);
        kitchenLoadTimeoutId = null;
    }
}

function handleKitchenFrameLoadError() {
    clearKitchenLoadTimeout();
    $('#kitchenPrintStatus').text('No se pudo cargar automáticamente la comanda. Reintenta la carga.');
    $('#retryKitchenLoad').show();
    $('#printKitchenNow').prop('disabled', true);
    showToast('warning', 'No se pudo cargar la vista de impresión. Usa "Reintentar cargar".');
}

function retryKitchenPrintLoad() {
    if (!currentKitchenPrintUrl || !kitchenPrintFrame) {
        return;
    }

    kitchenFrameLoaded = false;
    shouldAutoPrintKitchen = true;
    $('#printKitchenNow').prop('disabled', true);
    $('#retryKitchenLoad').hide();
    $('#kitchenPrintStatus').text('Reintentando carga de comanda...');
    startKitchenLoadTimeout();
    kitchenPrintFrame.src = currentKitchenPrintUrl;
}

function scheduleKitchenModalAutoClose() {
    clearAutoCloseKitchenModalTimeout();
    autoCloseKitchenModalTimeoutId = setTimeout(function() {
        const modalEl = document.getElementById('kitchenPrintModal');
        if (modalEl && modalEl.classList.contains('show')) {
            kitchenPrintModal.hide();
        }
    }, 2500);
}

function clearAutoCloseKitchenModalTimeout() {
    if (autoCloseKitchenModalTimeoutId) {
        clearTimeout(autoCloseKitchenModalTimeoutId);
        autoCloseKitchenModalTimeoutId = null;
    }
}

function showToast(type, message) {
    const container = document.getElementById('appToastContainer');
    if (!container) {
        return;
    }

    const toneMap = {
        success: 'text-bg-success',
        warning: 'text-bg-warning',
        danger: 'text-bg-danger',
        info: 'text-bg-primary',
    };
    const tone = toneMap[type] || toneMap.info;
    const toastId = `toast-${Date.now()}-${Math.floor(Math.random() * 9999)}`;
    const toastHtml = `
        <div id="${toastId}" class="toast ${tone} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    toast.show();
}
</script>
@endsection
