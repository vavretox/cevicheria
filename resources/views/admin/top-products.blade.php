@extends('layouts.app')

@section('title', 'Productos Más Vendidos')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <h2><i class="fas fa-trophy me-2"></i>Productos Más Vendidos</h2>
    <p class="text-muted">Ranking de ventas por producto</p>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.top-products') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('admin.top-products') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Ranking</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Cantidad Vendida</th>
                        <th>Total Generado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $index => $product)
                    <tr>
                        <td>
                            @php $rank = ($topProducts->currentPage() - 1) * $topProducts->perPage() + $index + 1; @endphp
                            @if($rank === 1)
                                <i class="fas fa-trophy text-warning fa-lg"></i>
                            @elseif($rank === 2)
                                <i class="fas fa-medal text-secondary fa-lg"></i>
                            @elseif($rank === 3)
                                <i class="fas fa-medal text-danger fa-lg"></i>
                            @else
                                {{ $rank }}
                            @endif
                        </td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $product->category ? $product->category->name : '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $product->total_sold }} unidades</span>
                        </td>
                        <td>
                            <strong class="text-success">Bs. {{ number_format($product->total_generated, 2) }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay ventas en el período seleccionado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $topProducts->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

