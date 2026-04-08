<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}" href="{{ route('admin.products') }}">
            <i class="fas fa-box"></i> Productos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.warehouse.beverages*') ? 'active' : '' }}" href="{{ route('admin.warehouse.beverages') }}">
            <i class="fas fa-warehouse"></i> Almacén Bebidas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories') }}">
            <i class="fas fa-tags"></i> Categorías
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.tables*') ? 'active' : '' }}" href="{{ route('admin.tables') }}">
            <i class="fas fa-table-cells-large"></i> Mesas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
            <i class="fas fa-users"></i> Usuarios
        </a>
    </li>
    {{-- Top Productos oculto temporalmente. Se reactivará cuando retomemos ese módulo.
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.top-products') ? 'active' : '' }}" href="{{ route('admin.top-products') }}">
            <i class="fas fa-trophy"></i> Top Productos
        </a>
    </li>
    --}}
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.cash-sessions') ? 'active' : '' }}" href="{{ route('admin.cash-sessions') }}">
            <i class="fas fa-cash-register"></i> Caja y Arqueo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
    </li>
</ul>
