<nav class="sidebar sidebar-offcanvas" id="sidebar">

    <ul class="nav">

        <li class="nav-item">
            <a class="nav-link" href="{{ url('user/dashboard') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Sites</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Request::is('user/suppliers') ? 'active' : '' }}" href="{{ url('user/suppliers') }}">
                <i class="menu-icon fa fa-inbox"></i>
                <span class="menu-title">Suppliers</span>
            </a>
        </li>

         <li class="nav-item">
            <a class="nav-link {{ Request::is('user/payments') ? 'active' : '' }}"
                href="{{ url('user/payments') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Payments</span>
            </a>
        </li>

    </ul>
    
</nav>
