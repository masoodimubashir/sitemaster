<x-app-layout>

    <div class="row">
        <div class="col-sm-12">
            <div class="row mb-4">
                <div class="col-sm-12">
                    <div class="statistics-details d-flex align-items-center gap-3">
                        <div class="bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-info fw-bold">Sites Open</p>
                            <h3 class="rate-percentage text-info">{{ $opened_sites }}</h3>
                        </div>
                        <div class="bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-danger fw-bold">Closed Sites</p>
                            <h3 class="rate-percentage text-danger">{{ $closed_sites }}</h3>
                        </div>
                        {{-- <div class="bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-info fw-bold">Total Due</p>
                            <h3 class="rate-percentage text-info">{{ Number::currency($finalTotal, 'INR') }}</h3>
                            </p>
                        </div> --}}
                        {{-- <div class="d-none d-md-block bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-info fw-bold">Paid</p>
                            <h3 class="rate-percentage text-info">2m:35s</h3>
                        </div> --}}
                        {{-- <div class="d-none d-md-block bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-info fw-bold">Balance</p>
                            <h3 class="rate-percentage text-info">68.8</h3>
                            </p>
                        </div> --}}
                        {{-- <div class="d-none d-md-block bg-white px-2 py-3 rounded">
                            <p class="statistics-title text-info fw-bold">Profit</p>
                            <h3 class="rate-percentage text-info">2m:35s</h3>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-8 d-flex flex-column">
                    <div class="row flex-grow">
                        <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="d-sm-flex justify-content-between align-items-start">
                                        <div>
                                            <h4 class="card-title card-title-dash">Payment History</h4>
                                        </div>
                                        <div>
                                            <a class="btn btn-sm btn-info text-white"
                                                href="{{ url('/admin/payments') }}">All Payments</a>
                                        </div>
                                    </div>
                                    <div class="table-responsive  mt-1">
                                        <table class="table select-table">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <div class="form-check form-check-flat mt-0">
                                                            <label class="form-check-label">
                                                                <input type="checkbox" class="form-check-input"
                                                                    aria-checked="false" id="check-all"><i
                                                                    class="input-helper"></i></label>
                                                        </div>
                                                    </th>
                                                    <th>Created At</th>
                                                    <th>Bill</th>
                                                    <th>Amount</th>
                                                    <th>Site</th>
                                                    <th>Supplier</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @php
                                                    $count = 0; // Initialize a counter
                                                @endphp

                                                @for ($i = 0; $i < count($data); $i++)
                                                    @if ($data[$i]['category'] === 'Payments')
                                                        @if ($count < 7)
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check form-check-flat mt-0">
                                                                        <label class="form-check-label">
                                                                            <input type="checkbox"
                                                                                class="form-check-input"
                                                                                aria-checked="false">
                                                                            <i class="input-helper"></i>
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    {{ \Carbon\Carbon::parse($data[$i]['created_at'])->format('d-M-Y') }}
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex">
                                                                        <img src="{{ asset($data[$i]['screenshot']) }}"
                                                                            alt="">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <h6>{{ Number::currency($data[$i]['amount'], 'INR') }}
                                                                    </h6>
                                                                </td>
                                                                <td>
                                                                    <div>
                                                                        <div
                                                                            class="d-flex justify-content-between align-items-center mb-1 max-width-progress-wrap">
                                                                            <p class="text-success">
                                                                                {{ ucwords($data[$i]['site']) }}</p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div>{{ ucwords($data[$i]['supplier']) }}</div>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $count++; // Increment the counter
                                                            @endphp
                                                        @endif
                                                    @endif
                                                @endfor




                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex flex-column">
                    <div class="row flex-grow">
                        <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body pb-0">

                                    <div class="row">

                                        <div class="text-black row text-center">

                                            <h4 class="card-title card-title-dash  mb-4 col-6">
                                                Monthy Payments
                                            </h4>

                                            <div class="col-6">
                                                <p class=" mb-1">
                                                    Total Amount : {{ Number::currency($paid, 'INR') }}
                                                </p>
                                            </div>

                                        </div>

                                        <div>

                                            <div class="status-summary-chart-wrapper pb-4">

                                                {!! $payment_chart->container() !!}

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-0">

                                                {!! $balance_paid_chart->container() !!}

                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="d-flex justify-content-between align-items-center">

                                                {!! $cost_profit_chart->container() !!}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-8 d-flex flex-column">
                    {{-- <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <h4 class="card-title card-title-dash">Monthly Payments Overview</h4>

                                    <div class="chartjs-bar-wrapper mt-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    {{-- <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded table-darkBGImg">
                                <div class="card-body">
                                    <div class="col-sm-8">
                                        <h3 class="text-white upgrade-info mb-0"> Enhance
                                            your <span class="fw-bold">Campaign</span> for
                                            better outreach </h3>
                                        <a href="#" class="btn btn-info upgrade-btn">Upgrade
                                            Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            {{-- <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="d-sm-flex justify-content-between align-items-start">
                                        <div>
                                            <h4 class="card-title card-title-dash">
                                                Performance Line Chart</h4>
                                            <h5 class="card-subtitle card-subtitle-dash">
                                                Lorem Ipsum is simply dummy text of the
                                                printing</h5>
                                        </div>
                                        <div id="performanceLine-legend"></div>
                                    </div>
                                    <div class="chartjs-wrapper mt-4 row">
                                        <canvas id="performanceLine" width=""></canvas>
                                        <div class="col-6">

                                        </div>
                                        <div class="col-6">

                                        </div>

                                    </div>
                                </div>
                            </div> --}}

                        </div>
                    </div>
                    <div class="row flex-grow">
                        <div class="col-md-6 col-lg-6 grid-margin stretch-card">
                            <div class="card card-rounded p-4">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="card-title card-title-dash">Suppliers</h4>
                                    <a class="btn btn-info btn-sm text-white mb-0 me-0"
                                        href="{{ url('/admin/suppliers/create') }}">
                                        <i class="mdi mdi-account-plus me-1"></i>
                                    </a>
                                </div>
                                <div class="list align-items-center border-bottom py-2">
                                    {{--
                                    @for ($d = 0; $d < 6; $d++)
                                        @if ($d['category'] === 'Suppliers')
                                            <ul class="bullet-line-list ">

                                                <li>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            {{ ucwords($d['suppliers']->name) }}
                                                        </div>
                                                        <p>{{ \Carbon\Carbon::parse($d['suppliers']->created_at)->format('d-M-Y') }}
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        @endif
                                    @endfor --}}

                                    @for ($d = 0; $d < 5; $d++)
                                        @if (isset($data[$d]) && $data[$d]['category'] === 'Suppliers')
                                            <ul class="bullet-line-list ">

                                                <li>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            {{ $data[$d]['suppliers']->name }}

                                                        </div>
                                                        <p>{{ \Carbon\Carbon::parse($data[$d]['suppliers']->created_at)->format('d-M-Y') }}
                                                        </p>
                                                    </div>
                                                </li>
                                            </ul>
                                        @endif
                                    @endfor


                                    <div class="list align-items-center pt-3">
                                        <div class="wrapper w-100">
                                            <p class="mb-0">
                                                <a href="{{ url('/admin/suppliers') }}"
                                                    class="fw-bold text-primary">Show all
                                                    <i class="mdi mdi-arrow-right ms-2"></i></a>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="card-title card-title-dash">Clients</h4>
                                        <a class="btn btn-info btn-sm text-white mb-0 me-0"
                                            href="{{ url('/admin/clients/create') }}">
                                            <i class="mdi mdi-account-plus me-1"></i>
                                        </a>
                                    </div>
                                    <ul class="bullet-line-list ">

                                        @foreach ($data as $d)
                                            @if ($d['category'] === 'Clients')
                                                <li>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="text-light-green">
                                                                {{ ucwords($d['clients']->name) }}
                                                            </span>
                                                        </div>
                                                        <p>{{ \Carbon\Carbon::parse($d['clients']->created_at)->format('D-m') }}
                                                        </p>
                                                    </div>
                                                </li>
                                            @endif
                                        @endforeach

                                    </ul>
                                    <div class="list align-items-center pt-3">
                                        <div class="wrapper w-100">
                                            <p class="mb-0">
                                                <a href="{{ url('/admin/clients') }}"
                                                    class="fw-bold text-primary">Show all
                                                    <i class="mdi mdi-arrow-right ms-2"></i></a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex flex-column">
                    <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h4 class="card-title card-title-dash">Notification Center</h4>

                                                <div class="add-items d-flex mb-0">
                                                    <!-- <input type="text" class="form-control todo-list-input" placeholder="What do you need to do today?"> -->
                                                    <a href="{{ route('admin.markAllAsRead') }}"
                                                        class="btn btn-info text-white btn-sm">
                                                        Mark All Read
                                                    </a>

                                                    <a href="{{ route('admin.viewAllNotifications') }}"
                                                        class="btn btn-sm btn-primary">
                                                        View All
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="list-wrapper">
                                                <ul class="todo-list todo-list-rounded">
                                                    @if (count($notifications))
                                                        @foreach ($notifications as $key => $notification)
                                                            @if ($key <= 2)
                                                                <li class="d-block">
                                                                    <div class="form-check w-100">
                                                                        <label class="form-check-label">
                                                                            <input class="checkbox" type="checkbox">
                                                                            Notify To: {{ $notification->type }}
                                                                            <i class="input-helper rounded"></i>
                                                                        </label>
                                                                        <div class="d-flex mt-2">
                                                                            <div class="ps-4 text-small me-3">
                                                                                {{ ucwords($notification->data['message']) }}

                                                                            </div>
                                                                            <div
                                                                                class="badge badge-opacity-info text-black fw-bold me-3">
                                                                                {{ \Carbon\Carbon::parse($notification->created_at)->format('D-M-Y') }}
                                                                            </div>
                                                                            <i
                                                                                class="mdi mdi-flag ms-2 flag-color"></i>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <h6 class="text-danger">No Notifications Awialable....</h6>
                                                    @endif


                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h4 class="card-title card-title-dash">
                                                        Payment Chart</h4>
                                                </div>
                                                <div>
                                                    <div class="dropdown">
                                                        <button
                                                            class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0"
                                                            type="button" id="dropdownMenuButton3"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false"> Month
                                                            Wise </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="dropdownMenuButton3">
                                                            <h6 class="dropdown-header">
                                                                week Wise</h6>
                                                            <a class="dropdown-item" href="#">Year
                                                                Wise</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                ....
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    {{-- <div class="row flex-grow">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h4 class="card-title card-title-dash">
                                                        Top Performer</h4>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div
                                                    class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                                    <div class="d-flex">
                                                        <img class="img-sm rounded-10"
                                                            src="assets/images/faces/face1.jpg" alt="profile">
                                                        <div class="wrapper ms-3">
                                                            <p class="ms-1 mb-1 fw-bold">
                                                                Brandon Washington</p>
                                                            <small class="text-muted mb-0">162543</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted text-small"> 1h
                                                        ago </div>
                                                </div>
                                                <div
                                                    class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                                    <div class="d-flex">
                                                        <img class="img-sm rounded-10"
                                                            src="assets/images/faces/face2.jpg" alt="profile">
                                                        <div class="wrapper ms-3">
                                                            <p class="ms-1 mb-1 fw-bold">
                                                                Wayne Murphy</p>
                                                            <small class="text-muted mb-0">162543</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted text-small"> 1h
                                                        ago </div>
                                                </div>
                                                <div
                                                    class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                                    <div class="d-flex">
                                                        <img class="img-sm rounded-10"
                                                            src="assets/images/faces/face3.jpg" alt="profile">
                                                        <div class="wrapper ms-3">
                                                            <p class="ms-1 mb-1 fw-bold">
                                                                Katherine Butler</p>
                                                            <small class="text-muted mb-0">162543</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted text-small"> 1h
                                                        ago </div>
                                                </div>
                                                <div
                                                    class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                                    <div class="d-flex">
                                                        <img class="img-sm rounded-10"
                                                            src="assets/images/faces/face4.jpg" alt="profile">
                                                        <div class="wrapper ms-3">
                                                            <p class="ms-1 mb-1 fw-bold">
                                                                Matthew Bailey</p>
                                                            <small class="text-muted mb-0">162543</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted text-small"> 1h
                                                        ago </div>
                                                </div>
                                                <div
                                                    class="wrapper d-flex align-items-center justify-content-between pt-2">
                                                    <div class="d-flex">
                                                        <img class="img-sm rounded-10"
                                                            src="assets/images/faces/face5.jpg" alt="profile">
                                                        <div class="wrapper ms-3">
                                                            <p class="ms-1 mb-1 fw-bold">
                                                                Rafell John</p>
                                                            <small class="text-muted mb-0">Alaska,
                                                                USA</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted text-small"> 1h
                                                        ago </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- {!! $site_chart->script() !!} --}}

    {!! $payment_chart->script() !!}
    {!! $balance_paid_chart->script() !!}
    {!! $cost_profit_chart->script() !!}
</x-app-layout>
