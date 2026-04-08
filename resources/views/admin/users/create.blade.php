@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    <h2><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h2>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" id="roleSelect" required>
                            <option value="">Seleccionar...</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="cajero" {{ old('role') === 'cajero' ? 'selected' : '' }}>Cajero</option>
                            <option value="mesero" {{ old('role') === 'mesero' ? 'selected' : '' }}>Mesero</option>
                        </select>
                    </div>

                    <div class="mb-3" id="orderChannelGroup" style="display:none;">
                        <label class="form-label">Canal del Mesero</label>
                        <select name="order_channel" class="form-select">
                            <option value="table" {{ old('order_channel', 'table') === 'table' ? 'selected' : '' }}>Atención en mesa</option>
                            <option value="delivery" {{ old('order_channel') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        </select>
                        <small class="text-muted">Usa Delivery para pedidos sin mesa desde caja.</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Roles del Sistema</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-danger"><i class="fas fa-user-shield me-2"></i>Administrador</h6>
                    <p class="small mb-0">Acceso completo al sistema. Puede gestionar usuarios, productos, categorías y ver todos los reportes.</p>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-success"><i class="fas fa-cash-register me-2"></i>Cajero</h6>
                    <p class="small mb-0">Procesa pedidos, genera boletas y accede a reportes de ventas.</p>
                </div>
                <hr>
                <div>
                    <h6 class="text-info"><i class="fas fa-concierge-bell me-2"></i>Mesero</h6>
                    <p class="small mb-0">Toma pedidos de los clientes y los envía a caja para procesamiento.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function syncOrderChannelVisibility() {
    const isWaiter = document.getElementById('roleSelect').value === 'mesero';
    document.getElementById('orderChannelGroup').style.display = isWaiter ? '' : 'none';
}

document.getElementById('roleSelect').addEventListener('change', syncOrderChannelVisibility);
syncOrderChannelVisibility();
</script>
@endsection
