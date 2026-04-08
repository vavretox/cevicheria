@extends('layouts.app')

@section('title', 'Caja y Arqueo')

@section('sidebar')
@include('cashier._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2><i class="fas fa-cash-register me-2"></i>Caja y Arqueo</h2>
            <p class="text-muted mb-0">Abre tu caja, procesa ventas y realiza el cierre con arqueo al final del turno.</p>
        </div>
        <a href="{{ route('cashier.cash-sessions.print') }}" class="btn btn-outline-dark" target="_blank" rel="noopener">
            <i class="fas fa-print me-2"></i>Imprimir
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        @if($currentSession)
        <div class="card border-warning">
            <div class="card-header bg-warning-subtle">
                <h5 class="mb-0"><i class="fas fa-lock-open me-2"></i>Caja Abierta</h5>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Apertura:</strong> Bs. {{ number_format($currentSession->opening_amount, 2) }}</div>
                <div class="mb-2"><strong>Abierta desde:</strong> {{ $currentSession->opened_at?->format('d/m/Y H:i') }}</div>
                <div class="mb-2"><strong>Ventas del turno:</strong> Bs. {{ number_format($currentSession->sales_total, 2) }}</div>
                <div class="mb-3"><strong>Esperado actual:</strong> Bs. {{ number_format($currentSession->expected_balance, 2) }}</div>
                @if($currentSession->opening_note)
                    <div class="alert alert-light small">{{ $currentSession->opening_note }}</div>
                @endif

                <form method="POST" action="{{ route('cashier.cash-sessions.close', $currentSession->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Monto contado al cierre</label>
                        <input type="number" step="0.01" min="0" name="counted_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación de cierre</label>
                        <textarea name="closing_note" class="form-control" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-lock me-2"></i>Cerrar Caja y Hacer Arqueo
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card border-success">
            <div class="card-header bg-success-subtle">
                <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>Abrir Caja</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cashier.cash-sessions.open') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Monto inicial</label>
                        <input type="number" step="0.01" min="0" name="opening_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación de apertura</label>
                        <textarea name="opening_note" class="form-control" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-play me-2"></i>Abrir Caja
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Sesiones</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Apertura</th>
                                <th>Ventas</th>
                                <th>Esperado</th>
                                <th>Contado</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                            <tr>
                                <td>
                                    <span class="badge {{ $session->status === 'open' ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                        {{ $session->status === 'open' ? 'Abierta' : 'Cerrada' }}
                                    </span>
                                </td>
                                <td>
                                    <div>Bs. {{ number_format($session->opening_amount, 2) }}</div>
                                    <small class="text-muted">{{ $session->opened_at?->format('d/m H:i') }}</small>
                                </td>
                                <td>Bs. {{ number_format($session->sales_total, 2) }}</td>
                                <td>Bs. {{ number_format($session->expected_balance, 2) }}</td>
                                <td>{{ $session->counted_amount !== null ? 'Bs. ' . number_format($session->counted_amount, 2) : '-' }}</td>
                                <td class="{{ ($session->difference_amount ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $session->difference_amount !== null ? 'Bs. ' . number_format($session->difference_amount, 2) : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aún no tienes sesiones de caja registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
