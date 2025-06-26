<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item nav-category">Management</li>

        <!-- Site Engineers -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="fas fa-helmet-safety menu-icon"></i>
                <span class="menu-title">Site Engineers</span>
            </a>
        </li>

        <!-- Clients -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/clients*') ? 'active' : '' }}" href="{{ url('/admin/clients') }}">
                <i class="fas fa-users menu-icon"></i>
                <span class="menu-title">Clients</span>
            </a>
        </li>

        <!-- Suppliers -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/suppliers*') ? 'active' : '' }}" href="{{ url('admin/suppliers') }}">
                <i class="fas fa-truck menu-icon"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>

        <!-- Items -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/items*') ? 'active' : '' }}" href="{{ url('admin/items') }}">
                <i class="fas fa-boxes-stacked menu-icon"></i>
                <span class="menu-title">Items</span>
            </a>
        </li>

        <!-- Site -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('sites.index') ? 'active' : '' }}" href="{{ route('sites.index') }}">
                <i class="fas fa-building menu-icon"></i>
                <span class="menu-title">Sites</span>
            </a>
        </li>

        <!-- Phase -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/phase*') ? 'active' : '' }}" href="{{ url('admin/phase') }}">
                <i class="fas fa-layer-group menu-icon"></i>
                <span class="menu-title">Phases</span>
            </a>
        </li>

        <!-- Attendance -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/wager-attendance*') ? 'active' : '' }}" href="{{ url('admin/wager-attendance') }}">
                <i class="fas fa-clipboard-user menu-icon"></i>
                <span class="menu-title">Attendance</span>
            </a>
        </li>

        <li class="nav-item nav-category">Financial</li>

        <!-- Ledger -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}" href="{{ url('admin/payments') }}">
                <i class="fas fa-book menu-icon"></i>
                <span class="menu-title">Ledger</span>
            </a>
        </li>

        <!-- Payment Manager -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/manage-payment*') ? 'active' : '' }}" href="{{ url('admin/manage-payment') }}">
                <i class="fas fa-money-bill-transfer menu-icon"></i>
                <span class="menu-title">Payment Manager</span>
            </a>
        </li>

        <li class="nav-item nav-category">Verifications</li>

        <!-- Verify Payments -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/pay-verification*') ? 'active' : '' }}" href="{{ url('admin/pay-verification') }}">
                <i class="fas fa-file-invoice-dollar menu-icon"></i>
                <span class="menu-title">Verify Payments</span>
            </a>
        </li>

        <!-- Verify Items -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/item-verification*') ? 'active' : '' }}" href="{{ url('admin/item-verification') }}">
                <i class="fas fa-clipboard-check menu-icon"></i>
                <span class="menu-title">Verify Items</span>
            </a>
        </li>

        <li class="nav-item nav-category">Trash</li>

        <!-- Trash Suppliers -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('trash.suppliers') ? 'active' : '' }}" href="{{ route('trash.suppliers') }}">
                <i class="fas fa-trash-can menu-icon"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>

        <!-- Trash Sites -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('trash.sites') ? 'active' : '' }}" href="{{ route('trash.sites') }}">
                <i class="fas fa-trash-can menu-icon"></i>
                <span class="menu-title">Sites</span>
            </a>
        </li>
        
        <!-- Trash Phases -->
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('trash.phases') ? 'active' : '' }}" href="{{ route('trash.phases') }}">
                <i class="fas fa-trash-can menu-icon"></i>
                <span class="menu-title">Phases</span>
            </a>
        </li>
    </ul>
</nav>

<style>




</style>