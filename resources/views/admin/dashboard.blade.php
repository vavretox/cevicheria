?@extends('layouts.app')

@section('title', 'Panel Administrador')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Panel de Administración</h2>
    <p class="text-muted">Resumen general del sistema</p>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Productos</h6>
                        <h2 class="mb-0">{{ $totalProducts }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-box fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Usuarios</h6>
                        <h2 class="mb-0">{{ $totalUsers }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Ventas Hoy</h6>
                        <h2 class="mb-0">Bs. {{ number_format($todaySales, 0) }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Ventas Mes</h6>
                        <h2 class="mb-0">Bs. {{ number_format($monthSales, 0) }}</h2>
                    </div>
                    <div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Accesos Rápidos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-plus-circle me-2"></i>Nuevo Producto
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="fas fa-tag me-2"></i>Nueva Categoría
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.tables') }}" class="btn btn-outline-warning w-100 mb-2">
                            <i class="fas fa-table-cells-large me-2"></i>Gestionar Mesas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pedidos Recientes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pedidos Recientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Cajero</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->display_number }}</strong></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $order->table_label }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->cashier ? $order->cashier->name : '-' }}</td>
                        <td><strong class="text-success">Bs. {{ number_format($order->total, 2) }}</strong></td>
                        <td>
                            @if($order->status === 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($order->status === 'completed')
                                <span class="badge bg-success">Completado</span>
                            @else
                                <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No hay pedidos recientes
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva Categoría -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection







