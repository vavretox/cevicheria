<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CashSessionController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\WaiterController;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    
    // Rutas del Administrador
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Usuarios
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // Categorías
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])->name('categories.delete');

        // Mesas
        Route::get('/tables', [AdminController::class, 'tables'])->name('tables');
        Route::get('/tables/{id}/activity', [AdminController::class, 'tableActivity'])->name('tables.activity');
        Route::post('/tables', [AdminController::class, 'storeTable'])->name('tables.store');
        Route::put('/tables/{id}', [AdminController::class, 'updateTable'])->name('tables.update');
        Route::put('/tables/{id}/reservation', [AdminController::class, 'updateTableReservation'])->name('tables.reservation');
        Route::delete('/tables/{id}', [AdminController::class, 'deleteTable'])->name('tables.delete');
        
        // Productos
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');
        Route::get('/warehouse/beverages', [AdminController::class, 'beverageWarehouse'])->name('warehouse.beverages');
        Route::post('/warehouse/beverages/entries', [AdminController::class, 'storeBeverageEntry'])->name('warehouse.beverages.entries.store');
        Route::put('/warehouse/beverages/entries/{entry}/purchase-price', [AdminController::class, 'updateBeverageEntryPurchasePrice'])->name('warehouse.beverages.entries.update-price');
        Route::post('/warehouse/beverages/exits', [AdminController::class, 'storeBeverageExit'])->name('warehouse.beverages.exits.store');
        Route::get('/warehouse/beverages/print', [AdminController::class, 'beverageWarehousePrint'])->name('warehouse.beverages.print');
        
        // Reportes
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/print', [AdminController::class, 'reportsPrint'])->name('reports.print');
        Route::get('/reports/beverages/gain-print', [AdminController::class, 'reportsBeverageGainPrint'])->name('reports.beverages.gain-print');
        Route::get('/reports/thermal-print', [AdminController::class, 'reportsThermalPrint'])->name('reports.thermal-print');
        Route::get('/reports/export', [AdminController::class, 'reportsExport'])->name('reports.export');
        Route::get('/reports/pdf', [AdminController::class, 'reportsPdf'])->name('reports.pdf');
        Route::get('/reports/xlsx', [AdminController::class, 'reportsXlsx'])->name('reports.xlsx');
        // Top Productos queda fuera durante estabilizacion.
        // Se reactiva cuando retomemos ese modulo.
        Route::get('/cash-sessions', [CashSessionController::class, 'adminIndex'])->name('cash-sessions');
        Route::get('/cash-sessions/print', [CashSessionController::class, 'adminPrint'])->name('cash-sessions.print');
        Route::get('/manual', [ManualController::class, 'admin'])->name('manual');
    });
    
    // Rutas del Cajero
    Route::middleware(['role:cajero,admin'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
        Route::get('/tables', [CashierController::class, 'tables'])->name('tables');
        Route::get('/cash-sessions', [CashSessionController::class, 'cashierIndex'])->name('cash-sessions');
        Route::get('/cash-sessions/print', [CashSessionController::class, 'cashierPrint'])->name('cash-sessions.print');
        Route::post('/cash-sessions/open', [CashSessionController::class, 'open'])->name('cash-sessions.open');
        Route::post('/cash-sessions/{id}/close', [CashSessionController::class, 'close'])->name('cash-sessions.close');
        Route::post('/order/create', [CashierController::class, 'createOrder'])->name('create-order');
        Route::get('/order/{id}', [CashierController::class, 'showOrder'])->name('show-order');
        Route::get('/order/{id}/summary', [CashierController::class, 'orderSummary'])->name('order-summary');
        Route::post('/orders/{id}/items', [CashierController::class, 'updateOrderItems'])->name('update-order-items');
        Route::post('/orders/{id}/cancel', [CashierController::class, 'cancelOrder'])->name('cancel-order');
        Route::post('/orders/{id}/revert', [CashierController::class, 'revertOrder'])->name('revert-order');
        Route::post('/order/{id}/process', [CashierController::class, 'processOrder'])->name('process-order');
        Route::get('/order/{id}/kitchen-print', [CashierController::class, 'printKitchenOrder'])->name('print-kitchen-order');
        Route::get('/order/{id}/receipt', [CashierController::class, 'printReceipt'])->name('print-receipt');
        Route::get('/order/{id}/download', [CashierController::class, 'downloadReceipt'])->name('download-receipt');
        Route::get('/sales', [CashierController::class, 'sales'])->name('sales');
        Route::get('/stock', [CashierController::class, 'stock'])->name('stock');
        Route::get('/sales/print', [CashierController::class, 'salesPrint'])->name('sales.print');
        Route::get('/manual', [ManualController::class, 'cashier'])->name('manual');
    });
    
    // Rutas del Mesero
    Route::middleware(['role:mesero,admin'])->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/dashboard', [WaiterController::class, 'dashboard'])->name('dashboard');
        Route::post('/order/create', [WaiterController::class, 'createOrder'])->name('create-order');
        Route::get('/orders', [WaiterController::class, 'myOrders'])->name('orders');
        Route::get('/orders/{id}', [WaiterController::class, 'orderDetails'])->name('order-details');
        Route::get('/orders/{id}/print/{scope?}', [WaiterController::class, 'printOrder'])->name('print-order');
        Route::post('/orders/{id}/items', [WaiterController::class, 'updateOrderItems'])->name('update-order-items');
        Route::post('/orders/{id}/cancel', [WaiterController::class, 'cancelOrder'])->name('cancel-order');
        Route::get('/manual', [ManualController::class, 'waiter'])->name('manual');
    });
});
