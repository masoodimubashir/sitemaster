<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
     

        <li class="nav-item nav-category">Management</li>

        <!-- Sites -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/dashboard*') ? 'active' : '' }}" href="{{ url('user/dashboard') }}">
                <i class="fas fa-building menu-icon"></i>
                <span class="menu-title">Sites</span>
            </a>
        </li>

        <!-- Suppliers -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/suppliers*') ? 'active' : '' }}" href="{{ url('user/suppliers') }}">
                <i class="fas fa-truck menu-icon"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>

        <!-- Items -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/items*') ? 'active' : '' }}" href="{{ url('user/items') }}">
                <i class="fas fa-boxes-stacked menu-icon"></i>
                <span class="menu-title">Items</span>
            </a>
        </li>

        <!-- Attendance -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/wager-attendance*') ? 'active' : '' }}" href="{{ url('user/wager-attendance') }}">
                <i class="fas fa-clipboard-user menu-icon"></i>
                <span class="menu-title">Attendance</span>
            </a>
        </li>

        <li class="nav-item nav-category">Verifications</li>

        <!-- Verify Items -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/item-verification*') ? 'active' : '' }}" href="{{ url('user/item-verification') }}">
                <i class="fas fa-clipboard-check menu-icon"></i>
                <span class="menu-title">Verify Items</span>
            </a>
        </li>

          <!-- Verify Payments -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('user/pay-verification*') ? 'active' : '' }}" href="{{ url('user/pay-verification') }}">
                <i class="fas fa-file-invoice-dollar menu-icon"></i>
                <span class="menu-title">Verify Payments</span>
            </a>
        </li>
    </ul>
</nav>