<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item nav-category">Tabs</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                href="{{ route('users.index') }}">
                <i class="menu-icon fa fa-user-o"></i>
                <span class="menu-title">Site Engineers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}"
                href="{{ route('clients.index') }}">
                <i class="menu-icon fa fa-user"></i>
                <span class="menu-title">Clients</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('suppliers.index') ? 'active' : '' }}"
                href="{{ route('suppliers.index') }}">
                <i class="menu-icon fa fa-inbox"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('sites.index') ? 'active' : '' }}"
                href="{{ route('sites.index') }}">
                <i class="menu-icon fa fa-building"></i>
                <span class="menu-title">Site</span>
            </a>
        </li>
         <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('payments.index') ? 'active' : '' }}"
                href="{{ route('payments.index') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Payments</span>
            </a>
        </li>
        <li class="nav-item nav-category">Trash</li>

        <li class="nav-item">
            <a class="nav-link" {{ request()->routeIs('trash.suppliers') ? 'active' : '' }}
                href="{{ route('trash.suppliers') }}">
                <span class="menu-title">Suppliers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('trash.sites') ? 'active' : '' }}"
                href="{{ route('trash.sites') }}">
                <span class="menu-title">Sites</span></a>
        </li>
    </ul>
</nav>
