<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">

    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <p class="navbar-brand brand-logo" href="index.html">
                {{-- SiteMaster --}}
                {{-- <img src="assets/images/logo.svg" alt="logo" /> --}}
            </p>
            <p class="navbar-brand brand-logo-mini" href="index.html">
                {{-- SiteMaster --}}
                {{-- <img src="assets/images/logo-mini.svg" alt="logo" /> --}}
            </p>
        </div>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-top">


        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text text-info">Welcome <span
                        class="text-info fw-bold">{{ ucfirst(auth()->user()->name) }}</span></h1>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">

            <li class="nav-item dropdown">
                <a class="nav-link count-indicator {{ $notifications ? 'text-danger' : 'text-black' }}"
                    id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                    <i class="icon-bell"> </i>

                    @if ($notifications)
                        <span>
                            {{ $notifications->count() }}
                        </span>
                    @endif

                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0"
                    aria-labelledby="notificationDropdown">

                    @foreach ($notifications as $key => $notification)
                        <a class="dropdown-item preview-item py-3">

                            <div class="preview-item-content">
                                <h6 class="preview-subject fw-normal text-dark mb-1">
                                    {{ $notification->data['message'] }}
                                </h6>

                            </div>
                        </a>
                    @endforeach

                </div>
            </li>



            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                <a class="nav-link d-flex align-items-center" id="UserDropdown" href="#" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <div class="position-relative">
                        <!-- User avatar (replace with your actual avatar implementation) -->
                        <div class="avatar avatar-sm rounded-circle bg-info text-white d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <!-- Online status indicator (optional) -->
                        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle"
                            style="width: 10px; height: 10px; border: 2px solid white;"></span>
                    </div>
                  
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <p class="mb-1 mt-3 fw-semibold">{{ ucfirst(auth()->user()->name) }}</p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->email }}</p>
                    </div>

                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="dropdown-item-icon mdi mdi-account-outline text-info me-2"></i>
                        My Profile
                    </a>

                    <a class="dropdown-item" href="{{ route('admin.viewAllNotifications') }}">
                        <i class="dropdown-item-icon mdi mdi-bell-ring-outline text-info me-2"></i>
                        {{ __('View Notifications') }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">

                        @csrf



                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="dropdown-item-icon mdi mdi-power text-info me-2"></i>
                            {{ __('Log Out') }}
                        </a>



                    </form>




                </div>
            </li>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>

    </div>
</nav>
