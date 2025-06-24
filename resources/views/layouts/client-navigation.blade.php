<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('dashboard.index') }}">
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Sites</span>
            </a>
        </li>


        <li class="nav-item">
            <a class="nav-link {{ request()->is('client/ledger') ? 'active' : '' }}" href="{{ url('client/ledger') }}">
                <i class="menu-icon fa-solid fa-book"></i>
                <span class="menu-title">Ledger</span>
            </a>
        </li>


    </ul>


</nav>
