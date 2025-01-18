<nav class="sidebar sidebar-offcanvas" id="sidebar">

    <ul class="nav">

         <li class="nav-item">
            <a class="nav-link {{ Request::is('user/clients') ? 'active' : '' }}"
                href="{{ url('user/clients') }}">
                <i class="menu-icon fa fa-user"></i>
                <span class="menu-title">Clients</span>
            </a>
        </li>

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
            <a class="nav-link {{ Request::is('user/items') ? 'active' : '' }}" href="{{ url('user/items') }}">
                <i class="menu-icon fa-solid fa-boxes-stacked"></i>
                <span class="menu-title">Items</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ Request::is('user/payments') ? 'active' : '' }}" href="{{ url('user/payments') }}">
                <i class="menu-icon fa fa-indian-rupee"></i>
                <span class="menu-title">Ledger</span>
            </a>
        </li>

        
     

        <li class="nav-item">
            <a class="nav-link {{ Request::is('user/wager-attendance') ? 'active' : '' }}"
                href="{{ url('user/wager-attendance') }}">
                <i class="menu-icon fa-solid fa-calendar-days"></i>
                <span class="menu-title">Attendance</span>
            </a>
        </li>


        

    </ul>

</nav>
