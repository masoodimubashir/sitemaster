<x-app-layout>


    <div id="messageContainer"> </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 9999999;
            left: 0;
            top: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            margin: 15% auto;
            background-color: white;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .modal img {
            width: 100%;
            height: 400px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .d {
            position: relative;
            display: inline-block;
        }

        .dropdown-button {
            color: black;
            border: none;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            border: 1px solid lightgray;
            border-radius: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
            min-width: 160px;
            background: white;
        }

        .dropdown-content li {
            padding: 10px 20px;
            cursor: pointer;
        }

        .dropdown-content li:hover {
            background-color: lightgray;
            color: black;
            /* Ensure text is readable on hover */
        }

        .d:hover .dropdown-content {
            display: block;
        }

        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }


        .phase-header {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .stats-card {
            border: none;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .progress {
            height: 10px;
            border-radius: 5px;
        }

        .status-badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .table-custom th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .card-title-custom {
            font-size: 1.2rem;
            font-weight: 600;
            color: #51B1E1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>


    <style>
        /* Active tab styling */
        .nav-pills .nav-link {
            background: white;
            /* Default inactive color */
            color: black;
            transition: background-color 0.3s ease;
        }

        .nav-pills .nav-link.active {
            background-color: #51B1E1;
            /* Bright blue for active tab */
            color: white;
        }
    </style>

    <x-breadcrumb :names="['Sites', $site->site_name]" :urls="['client/dashboard', 'client/dashboard/' . base64_encode($site->id)]" />

    {{-- Action Buttons Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-start gap-2">

                {{-- <a href="{{ route('supplier-payments.show', [$site->id]) }}" class="btn btn-info px-4">
                    <i class="fas fa-list me-2"></i>View Payments
                </a> --}}

                {{-- <a href="{{ url('client/site/ledger', $site->id) }}" class="btn btn-info px-4">
                    <i class="fas fa-book me-2"></i>View Ledger
                </a> --}}

                <a href="{{ url('client/download-site/report', ['id' => base64_encode($site->id)]) }}"
                    class="btn btn-info px-4">
                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                </a>

                <a href="{{ url('client/site-payment/report', ['id' => base64_encode($site->id)]) }}"
                    class="btn btn-info px-4">
                    <i class="fas fa-file-invoice me-2"></i>Generate Payments
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Section --}}
    <div class="row g-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-building  text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Site Name</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_name) }}</h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-user text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Owner</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_owner_name) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">

                            <i class="fa-solid fa-phone text-info fs-3  p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">
                                <a href="tel:+91-{{ $site->contact_no }}" class="text-decoration-none">
                                    +91-{{ $site->contact_no }}
                                </a>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-map-marker-alt text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            <h5 class="mb-0">{{ ucwords($site->location) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-percent text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Service Charge</h6>
                            <h5 class="mb-0">{{ $site->service_charge }}%</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-money-bill text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Debit</h6>
                            <h5 class="mb-0">{{ Number::currency($grand_total_amount, 'INR') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-{{ $balance >= 0 ? '' : '' }} fs-3 p-2">
                            <i class="fas fa-balance-scale text-{{ $balance >= 0 ? 'info' : 'danger' }}"></i>
                        </div>
                        <div>
                            <h6 class="text-{{ $balance >= 0 ? 'info' : 'danger' }} mb-1">Balance</h6>
                            <h5 class="mb-0 text-{{ $balance >= 0 ? 'info' : 'danger' }}">
                                {{ Number::currency($balance, 'INR') }}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" fs-3 p-2">
                            <i class="fas fa-credit-card text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Credit</h6>
                            <h5 class="mb-0">{{ Number::currency($totalPaymentSuppliersAmount, 'INR') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($site)

        @if ($site->phases->count() > 0)

            <div class="card-body mt-3">

                <ul class="nav nav-pills mb-4">
                    @foreach ($site->phases as $phase_key => $phase)
                        <li class="nav-item">
                            <a class="nav-link {{ $phase_key === 0 ? 'active' : '' }}" href="#{{ $phase->id }}"
                                data-bs-toggle="tab" onclick="setActiveTab('{{ $phase->id }}')">
                                {{ $phase->phase_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content border-0 p-0">
                @foreach ($site->phases as $phase_key => $phase)
                    <div class="tab-pane fade {{ $phase_key === 0 ? 'show active' : '' }}" id="{{ $phase->id }}">

                        <div class=" mb-3 d-flex justify-content-end g-2">
                            <a href="{{ url('client' . '/download-phase/report', ['id' => base64_encode($phase->id)]) }}"
                                class="btn btn-info btn-sm text-white">
                                Generate Phase PDF
                            </a>
                        </div>

                        <div class="row">

                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-tasks"></i>
                                            Phase Total
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>..</th>
                                                        <th>Amount</th>
                                                        <th> Service Charge {{ $site->service_charge }}%</th>
                                                        <th> Total</th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <tr>

                                                        <td>
                                                            Materials
                                                        </td>
                                                        <td>
                                                            {{ $phase->construction_total_amount }}
                                                        </td>
                                                        <td>
                                                            ....
                                                        </td>
                                                        <td>
                                                            {{ $phase->construction_total_service_charge_amount }}
                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td>
                                                            Square Footage Bills
                                                        </td>

                                                        <td>
                                                            {{ $phase->square_footage_total_amount }}
                                                        </td>

                                                        <td>
                                                            ....
                                                        </td>

                                                        <td>
                                                            {{ $phase->sqft_total_service_charge_amount }}
                                                        </td>

                                                    </tr>

                                                    <tr>

                                                        <td>
                                                            Expenses
                                                        </td>

                                                        <td>
                                                            {{ $phase->daily_expenses_total_amount }}
                                                        </td>

                                                        <td>
                                                            ....
                                                        </td>

                                                        <td>
                                                            {{ $phase->daily_expense_total_service_charge_amount }}
                                                        </td>

                                                    </tr>


                                                    <tr>

                                                        <td>
                                                            Wager
                                                        </td>

                                                        <td>
                                                            {{ $phase->daily_wagers_total_amount }}
                                                        </td>

                                                        <td>
                                                            ...
                                                        </td>

                                                        <td>
                                                            {{ $phase->daily_wagers_total_service_charge_amount }}
                                                        </td>

                                                    </tr>


                                                    <tr>

                                                        <td>Sub Total</td>

                                                        <td>
                                                            {{ $phase->phase_total_amount }}
                                                        </td>

                                                        <td>
                                                            {{ $phase->phase_total_service_charge_amount }}
                                                        </td>

                                                        <td>
                                                            {{ $phase->phase_total_with_service_charge_amount }}
                                                        </td>

                                                    </tr>

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Materials Table -->
                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-boxes"></i>
                                            Materials
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>Date</th>
                                                        <th> Image </th>
                                                        <th> Name </th>
                                                        <th> Item Name </th>
                                                        <th> Price </th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- Construction Material --}}

                                                    @if (count($phase->constructionMaterialBillings))
                                                        @foreach ($phase->constructionMaterialBillings as $construction_material_billing)
                                                            <tr>

                                                                <td>
                                                                    {{ $construction_material_billing->created_at->format('d-M-Y') }}
                                                                </td>

                                                                <td>
                                                                    <img style="cursor: pointer"
                                                                        data-full="{{ asset($construction_material_billing->item_image_path) }}"
                                                                        src="{{ asset($construction_material_billing->item_image_path) }}"alt=""
                                                                        class="w-20 h-20 rounded-full gallery-image">
                                                                </td>

                                                                <td>
                                                                    <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                                        href="{{ route('suppliers.show', $construction_material_billing->supplier->id) }}">
                                                                        {{ $construction_material_billing->supplier->name ?? '' }}
                                                                    </a>
                                                                </td>

                                                                <td>
                                                                    {{ $construction_material_billing->item_name }}
                                                                </td>

                                                                <td>
                                                                    {{ $construction_material_billing->amount }}
                                                                    <br>
                                                                </td>

                                                            </tr>

                                                            @if ($loop->last)
                                                                <tr class="">
                                                                    <td colspan="4"
                                                                        class="text-right font-bold bg-info text-white fw-bold">
                                                                        Cost + Service Charge:</td>

                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white fw-bold">

                                                                        {{ $phase->construction_total_amount }}
                                                                        +
                                                                        {{ ($site->service_charge / 100) * $phase->construction_total_amount }}
                                                                        =
                                                                        {{ ($site->service_charge / 100) * $phase->construction_total_amount + $phase->construction_total_amount }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="6" class="text-center text-danger">No
                                                                Records Found..</td>
                                                        </tr>
                                                    @endif

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Labor Table -->
                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-hard-hat"></i>
                                            Square Footage Biils
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">
                                                <thead>
                                                    <tr>

                                                        <th>Date</th>

                                                        <th> Image </th>
                                                        <th> Wager type </th>
                                                        <th>Supplier Name</th>
                                                        <th> Type </th>
                                                        <th> Price </th>

                                                        <th>Multiplier</th>
                                                        <th>
                                                            Total Price
                                                        </th>

                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    {{-- Square Footage --}}



                                                    @if (count($phase->squareFootageBills))
                                                        @foreach ($phase->squareFootageBills as $sqft)
                                                            <tr>

                                                                <td>{{ $sqft->created_at->format('d-M-Y') }}
                                                                </td>

                                                                <td>
                                                                    <img style="cursor: pointer"
                                                                        data-full="{{ asset($sqft->image_path) }}"
                                                                        src="{{ asset($sqft->image_path) }}"alt=""
                                                                        class="w-20 h-20 rounded-full gallery-image">
                                                                </td>
                                                                <td>
                                                                    {{ ucwords($sqft->wager_name) }}
                                                                </td>

                                                                <td>
                                                                    <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                                        href="{{ route('suppliers.show', $sqft->supplier->id) }}">
                                                                        {{ ucwords($sqft->supplier->name) }}

                                                                    </a>

                                                                </td>


                                                                <td>
                                                                    {{ ucwords($sqft->type) }}
                                                                </td>

                                                                <td>
                                                                    {{ Number::currency($sqft->price ?? 0, 'INR') }}
                                                                </td>

                                                                <td>
                                                                    {{ $sqft->multiplier }}
                                                                </td>



                                                                <td>
                                                                    {{ $sqft->multiplier * $sqft->price }}
                                                                </td>


                                                            </tr>
                                                            @if ($loop->last)
                                                                <tr class="">
                                                                    <td colspan="7"
                                                                        class="text-right font-bold bg-info text-white fw-bold">

                                                                        Cost + Service Charge
                                                                    </td>

                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white fw-bold">

                                                                        {{ $phase->square_footage_total_amount }}

                                                                        +

                                                                        {{ ($site->service_charge / 100) * $phase->square_footage_total_amount }}

                                                                        =
                                                                        {{ ($site->service_charge / 100) * $phase->square_footage_total_amount + $phase->square_footage_total_amount }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="9" class="text-center text-danger">
                                                                No
                                                                Records Found</td>
                                                        </tr>
                                                    @endif

                                                </tbody>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Quality Checks -->
                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-clipboard-check"></i>
                                            Daily Expense
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>Date</th>
                                                        <th>Bill Photo</th>
                                                        <th> Item Name </th>
                                                        <th> Total Price </th>

                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    {{-- Square Footage --}}



                                                    @if (count($phase->dailyExpenses))
                                                        @foreach ($phase->dailyExpenses as $daily_expenses)
                                                            <tr>


                                                                <td>
                                                                    {{ $daily_expenses->created_at->format('d-M-Y') }}
                                                                </td>

                                                                <td>
                                                                    <img style="cursor: pointer"
                                                                        data-full="{{ asset($daily_expenses->bill_photo) }}"
                                                                        src="{{ asset($daily_expenses->bill_photo) }}"alt=""
                                                                        class="w-20 h-20 rounded-full gallery-image">
                                                                </td>

                                                                <td>
                                                                    {{ ucwords($daily_expenses->item_name) }}
                                                                </td>



                                                                <td>
                                                                    {{ $daily_expenses->price }}
                                                                </td>


                                                            </tr>
                                                            @if ($loop->last)
                                                                <tr>
                                                                    <td colspan="3"
                                                                        class="text-right font-bold bg-info text-white   fw-bold">
                                                                        Cost + Service Charge:</td>
                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white  fw-bold">

                                                                        {{ $phase->daily_expenses_total_amount }}
                                                                        +
                                                                        {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount }}
                                                                        =
                                                                        {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="4" class="text-center text-danger">
                                                                No Records Found
                                                            </td>
                                                        </tr>
                                                    @endif

                                                </tbody>

                                            </table>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- Progress Table -->
                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-user"></i>
                                            Daily Wager
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>Date</th>

                                                        <th> Wager Name </th>
                                                        <th>Price Per Wager</th>
                                                        <th> Total Price </th>



                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    {{-- Square Footage --}}


                                                    @if (count($phase->dailyWagers))
                                                        @foreach ($phase->dailyWagers as $daily_wager)
                                                            <tr>

                                                                <td>

                                                                    {{ $daily_wager->created_at->format('d-M-Y') }}

                                                                </td>

                                                                <td>

                                                                    {{ ucwords($daily_wager->wager_name) }}

                                                                </td>

                                                                <td>

                                                                    {{ $daily_wager->price_per_day }}

                                                                </td>

                                                                <td>

                                                                    {{ $daily_wager->wager_total }}

                                                                </td>


                                                            </tr>

                                                            @if ($loop->last)
                                                                <tr>
                                                                    <td colspan="3"
                                                                        class="text-right font-bold bg-info text-white fw-bold">
                                                                        Cost + Service Charge
                                                                    </td>

                                                                    <td colspan="2"
                                                                        class=" font-bold bg-info text-white fw-bold">

                                                                        {{ $phase->daily_wagers_total_amount }}
                                                                        +
                                                                        {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount }}
                                                                        =
                                                                        {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="3"
                                                                class="text-center text-danger fw-bold">
                                                                No
                                                                Records Found</td>
                                                            <td>
                                                        </tr>
                                                    @endif






                                                </tbody>


                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-clipboard-check"></i>
                                            Attendance
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>Date</th>

                                                        <th> No Of Persons </th>
                                                        <th>Wager Name </th>
                                                        <th>
                                                            Supplier
                                                        </th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    {{-- Square Footage --}}


                                                    @if (count($phase->wagerAttendances))
                                                        @foreach ($phase->wagerAttendances as $wager_attendance)
                                                            <tr aria-colspan="4">


                                                                <td>{{ $wager_attendance->created_at->format('d-M-Y') }}
                                                                </td>

                                                                <td>
                                                                    {{ $wager_attendance->no_of_persons }}
                                                                </td>

                                                                <td>
                                                                    {{ ucwords($wager_attendance->dailyWager->wager_name) }}
                                                                </td>

                                                                <td>

                                                                    <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                                        href="{{ route('suppliers.show', $wager_attendance->dailyWager->supplier->id) }}">
                                                                        {{ ucwords($wager_attendance->dailyWager->supplier->name ?? '') }}
                                                                </td>


                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="4" class="text-center text-danger">
                                                                No Records Found
                                                            </td>
                                                        </tr>
                                                    @endif


                                                </tbody>

                                                {{-- <thead>
                                                    <tr>
                                                        <th>Check</th>
                                                        <th>Result</th>
                                                        <th>Date</th>
                                                        <th>Inspector</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Concrete Strength</td>
                                                        <td><span class="badge bg-success status-badge">Pass</span></td>
                                                        <td>2024-02-15</td>
                                                        <td>
                                                            <img src="https://via.placeholder.com/30"
                                                                class="rounded-circle me-2" alt="Inspector">
                                                            John D.
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Steel Quality</td>
                                                        <td><span class="badge bg-warning status-badge">Pending</span>
                                                        </td>
                                                        <td>2024-02-16</td>
                                                        <td>
                                                            <img src="https://via.placeholder.com/30"
                                                                class="rounded-circle me-2" alt="Inspector">
                                                            Sarah M.
                                                        </td>
                                                    </tr>
                                                </tbody> --}}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>




                        </div>


                    </div>
                @endforeach

            </div>
        @else
            <table class=" mt-2 table table-bordered">
                <thead></thead>
                <tbody>
                    <tr>
                        <td class="text-danger fw-bold text-center">No Site Data Availiable..</td>
                    </tr>
                </tbody>
            </table>
        @endif

    @endif

    <div id="imageModal" class="modal">
        <div class="modal-content p-2">
            <div class="close-container d-flex justify-content-end">
                <span class="close">&times;</span>
            </div>
            <img id="modalImage" src="" alt="Full size image">
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>



    <script>
        // Get modal element
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        var closeBtn = document.getElementsByClassName("close")[0];

        // Get all gallery images
        var galleryImages = document.querySelectorAll(".gallery-image");

        // Add event listener to each image
        galleryImages.forEach(function(image) {
            image.addEventListener("click", function() {
                var fullImagePath = this.getAttribute("data-full");
                modalImg.src = fullImagePath;
                modal.style.display = "block"; // Show the modal
            });
        });

        // When the user clicks on <span> (close button), close the modal
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Close the modal when clicking anywhere outside of the modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>


    {{-- <form method="POST" action="/upload-photo" enctype="multipart/form-data">
        @csrf
        <input type="file" accept="image/*" capture="camera" name="photo">
        <button type="submit">Upload Photo</button>
    </form> --}}

    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get all buttons with class 'btn'
            const buttons = document.querySelectorAll('.btns');

            // Add click event listeners to each button
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    // Get the modal ID from the button's data attribute
                    const modalId = button.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);

                    // Show the modal
                    if (modal) {
                        modal.style.display = 'block';
                    }
                });
            });

            // Close modals when the close button is clicked
            const closeButtons = document.querySelectorAll('.modal .close');
            closeButtons.forEach(closeButton => {
                closeButton.addEventListener('click', () => {
                    const modal = closeButton.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Close modals when clicking outside the modal content
            window.addEventListener('click', (event) => {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });
        });

        // Get all accordion headers
        var headers = document.querySelectorAll('.accordion-header');

        // Add click event listener to each header
        headers.forEach(header => {
            header.addEventListener('click', function() {
                // Toggle the active class on the clicked header
                this.classList.toggle('active');

                // Get the content panel associated with the clicked header
                var content = this.nextElementSibling;

                // If the content panel is open, close it
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    content.classList.remove('show');
                } else {
                    // Otherwise, close all other content panels
                    document.querySelectorAll('.accordion-content').forEach(panel => {
                        panel.style.display = 'none';
                        panel.classList.remove('show');
                    });

                    // Open the clicked content panel
                    content.style.display = 'block';
                    content.classList.add('show');
                }
            });
        });
    </script> --}}

    <script>
        function setActiveTab(tabId) {
            // Remove active class from all tabs
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });

            // Add active class to the clicked tab
            const activeTab = document.querySelector(`a[href="#${tabId}"]`);
            if (activeTab) {
                activeTab.classList.add('active');
            }

            // Store the active tab in localStorage
            localStorage.setItem('activeTab', tabId);
        }

        // Restore the active tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                // Trigger click on the saved tab to restore its state
                const tabElement = document.querySelector(`a[href="#${activeTab}"]`);
                if (tabElement) {
                    tabElement.classList.add('active');
                }
            }
        });
    </script>

</x-app-layout>
