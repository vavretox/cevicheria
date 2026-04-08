<ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.dashboard') ? 'active' : '' }}" href="{{ route('cashier.dashboard') }}">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.tables') ? 'active' : '' }}" href="{{ route('cashier.tables') }}">
            <i class="fas fa-table-cells-large"></i> Mesas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.cash-sessions*') ? 'active' : '' }}" href="{{ route('cashier.cash-sessions') }}">
            <i class="fas fa-cash-register"></i> Caja y Arqueo
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.sales') ? 'active' : '' }}" href="{{ route('cashier.sales') }}">
            <i class="fas fa-chart-line"></i> Ventas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.stock') ? 'active' : '' }}" href="{{ route('cashier.stock') }}">
            <i class="fas fa-boxes-stacked"></i> Stock Productos
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cashier.manual') ? 'active' : '' }}" href="{{ route('cashier.manual') }}">
            <i class="fas fa-book"></i> Manual de Usuario
        </a>
    </li>
</ul>
