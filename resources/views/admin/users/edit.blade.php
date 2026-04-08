@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('sidebar')
@include('admin._sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    <h2><i class="fas fa-user-edit me-2"></i>Editar Usuario</h2>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Usuario</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <small class="text-muted">Deja en blanco para mantener la contraseña actual</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select name="role" class="form-select" id="roleSelect" required>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="cajero" {{ old('role', $user->role) === 'cajero' ? 'selected' : '' }}>Cajero</option>
                            <option value="mesero" {{ old('role', $user->role) === 'mesero' ? 'selected' : '' }}>Mesero</option>
                        </select>
                    </div>

                    <div class="mb-3" id="orderChannelGroup" style="display:none;">
                        <label class="form-label">Canal del Mesero</label>
                        <select name="order_channel" class="form-select">
                            <option value="table" {{ old('order_channel', $user->order_channel ?? 'table') === 'table' ? 'selected' : '' }}>Atención en mesa</option>
                            <option value="delivery" {{ old('order_channel', $user->order_channel) === 'delivery' ? 'selected' : '' }}>Delivery</option>
                        </select>
                        <small class="text-muted">Solo aplica para usuarios con rol mesero.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="active" class="form-select">
                            <option value="1" {{ $user->active ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !$user->active ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Usuario</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Creado:</strong><br>{{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p class="small mb-2"><strong>Última actualización:</strong><br>{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                <hr>
                <p class="small mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Si cambias la contraseña, el usuario deberá usar la nueva contraseña en su próximo inicio de sesión.
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Roles del Sistema</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <h6 class="text-danger small"><i class="fas fa-user-shield me-2"></i>Administrador</h6>
                    <p class="small mb-0">Acceso completo</p>
                </div>
                <hr>
                <div class="mb-2">
                    <h6 class="text-success small"><i class="fas fa-cash-register me-2"></i>Cajero</h6>
                    <p class="small mb-0">Procesa pedidos y ventas</p>
                </div>
                <hr>
                <div>
                    <h6 class="text-info small"><i class="fas fa-concierge-bell me-2"></i>Mesero</h6>
                    <p class="small mb-0">Toma pedidos</p>
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
