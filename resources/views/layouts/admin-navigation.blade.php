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
            <a class="nav-link {{ Request::is('admin/clients') ? 'active' : '' }}"
                href="{{ url('admin/clients') }}">
                <i class="menu-icon fa fa-user"></i>
                <span class="menu-title">Clients</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/suppliers') ? 'active' : '' }}"
                href="{{ url('admin/suppliers') }}">
                <i class="menu-icon fa fa-inbox"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/items') ? 'active' : '' }}" href="{{ url('admin/items') }}">
                <i class="menu-icon fa-solid fa-boxes-stacked"></i>
                <span class="menu-title">Items</span>
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
            <a class="nav-link {{ Request::is('admin/phase') ? 'active' : '' }}" href="{{ url('admin/phase') }}">
                <i class="menu-icon fas fa-tasks me-2"></i>
                <span class="menu-title">Phase</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/payments') ? 'active' : '' }}"
                href="{{ url('admin/payments') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Payments</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/pay-verification') ? 'active' : '' }}"
                href="{{ url('admin/pay-verification') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Verify Payments</span>
            </a>
        </li>

         <li class="nav-item">
            <a class="nav-link {{ Request::is('admin/item-verification') ? 'active' : '' }}"
                href="{{ url('admin/item-verification') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Verify Items</span>
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
         <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('trash.phases') ? 'active' : '' }}"
                href="{{ route('trash.phases') }}">
                <span class="menu-title">Phases</span></a>
        </li>
    </ul>
</nav>
