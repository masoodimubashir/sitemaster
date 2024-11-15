@php
    use App\Models\User;

    if (auth()->user()->role_name === 'admin' || auth()->user()->role_name === 'site_engineer') {
        # code...

        $notifications = auth()
            ->user()
            ->unreadNotifications()
            ->where('notifiable_type', User::class)
            ->get();
    }

@endphp

<!DOCTYPE html>
<html lang="en">

<head lang="{{ str_replace('_', '-', app()->getLocale()) }}">


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- CDN Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />



    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/select.dataTables.min.css') }}">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- endinject -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

    {{-- ChartJs  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .second a:hover {
            color: rgb(0, 183, 255) !important;
        }


        .second .active-2 {
            color: #00AAB7 !important;
            border-bottom: 3px solid #00AAB7 !important;
            padding-bottom: 11px !important;


        }

        .second span:hover {
            padding-bottom: 11px !important;
            border-bottom: 3px solid rgb(0, 183, 255) !important;

        }

        .second .breadcrumb>li+li:before {
            content: "" !important;
        }

        .second .breadcrumb {
            /* padding: 10px; */
            font-size: 14px;
            color: #aaa !important;
            letter-spacing: 2px;
            border-radius: 5px !important;
        }

        .second>>.fa,
        i {
            color: rgb(0, 183, 255) !important;
            font-size: 10px;
        }

        .second>>.fa-angle-double-right {
            color: #aaa !important;
        }


        .second .first {
            background-color: white !important;
        }


        .second a {
            text-decoration: none !important;
            color: #aaa !important;
        }

        .second a:focus,
        a:active {
            outline: none !important;
            box-shadow: none !important;
        }


        .second .fa-caret-right,
        .fa-angle-double-right {
            font-size: 20px !important;
        }

        .second .fa-caret-right {
            vertical-align: middle;
        }

        .second img {
            vertical-align: bottom;
            opacity: 0.3;
        }

        .second .four ol {
            background-color: rgb(51, 0, 80) !important;
        }

        @media (max-width: 767px) {
            .second .breadcrumb {
                font-size: 10px;

            }

            .second .breadcrumb-item+.breadcrumb-item {
                padding-left: 0;

            }

            .second .fa {
                font-size: 9px !important;
            }


            .second .breadcrumb {
                letter-spacing: 1px !important;
            }

            .second .breadcrumb>* div {
                max-width: 60px;
            }

            .second .active-2 {
                border-bottom: none !important;

            }


        }
    </style>

</head>

<body class="with-welcome-text">


    @if (auth()->user()->role_name === 'admin')
        @include('components.admin-topbar')
    @elseif (auth()->user()->role_name === 'site_engineer')
        @include('components.user-topbar')
    @else
        @include('components.client-topbar')
    @endif



    <!-- partial -->
    <div class="container-fluid page-body-wrapper">

        @if (auth()->user()->role_name === 'admin')
            @include('layouts.admin-navigation')
        @elseif (auth()->user()->role_name === 'site_engineer')
            @include('layouts.user-navigation')
        @else
            @include('layouts.client-navigation')
        @endif
        <!-- partial:partials/_sidebar.html -->

        <!-- partial -->
        <div class="main-panel">

            <div class="content-wrapper">


                {{ $slot }}

            </div>
            <!-- content-wrapper ends -->
            <!-- partial:partials/_footer.html -->
            <footer class="footer">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Developed By <a
                            href="https://www.bootstrapdash.com/" target="_blank">Py.Sync Pvt Ltd</a></span>
                    <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright © 2023. All
                        rights reserved.</span>
                </div>
            </footer>
            <!-- partial -->
        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->


    <!-- plugins:js -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

    <!-- endinject -->


    <script src="{{ asset('assets/js/dashboard.js') }}"></script>

    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/todolist.js') }}"></script>
    <!-- endinject -->

    <!-- Custom js for this page-->
    <script src="{{ asset('assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js')}}"></script> -->
    <!-- End custom js for this page-->
</body>

</html>
