
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item nav-category">Tabs</li>

        <li class="nav-item ">
            <a class="nav-link" href="{{ route('users.index') }}">
                <i class="fa-solid fa-helmet-safety menu-icon"></i>
                <span class="menu-title">Site Engineers</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link" href="{{ url('/admin/clients') }}">
                <i class="menu-icon fa fa-user"></i>
                <span class="menu-title">Clients</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('admin/suppliers') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('admin/suppliers') }}">
                <i class="menu-icon fa fa-inbox"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/items') }}">
                <i class="menu-icon fa-solid fa-boxes-stacked"></i>
                <span class="menu-title">Items</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ route('sites.index') }}">
                <i class="menu-icon fa fa-building"></i>
                <span class="menu-title">Site</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link" href="{{ url('admin/phase') }}">
                <i class="menu-icon fas fa-tasks me-2"></i>
                <span class="menu-title">Phase</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ url('admin/wager-attendance') }}">
                <i class="menu-icon fa-solid fa-calendar-days"></i>
                <span class="menu-title">Attendance</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ url('admin/payments') }}">
                <i class="menu-icon fa-solid fa-book"></i>
                <span class="menu-title">Ledger</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ url('admin/manage-payment') }}">
                <i class="menu-icon fa-solid fa-book"></i>
                <span class="menu-title">Payment Manager</span>
            </a>
        </li>

        <li class="nav-item nav-category">Verifications</li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ url('admin/pay-verification') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Verify Payments</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ url('admin/item-verification') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Verify Items</span>
            </a>
        </li>

        <li class="nav-item nav-category">Trash</li>

        <li class="nav-item">
            <a class="nav-link"
                href="{{ route('trash.suppliers') }}">
                <span class="menu-title">Suppliers</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link"
                href="{{ route('trash.sites') }}">
                <span class="menu-title">Sites</span></a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link"
                href="{{ route('trash.phases') }}">
                <span class="menu-title">Phases</span></a>
        </li>
    </ul>
</nav>