<x-app-layout>



    <!-- Add this custom CSS to your stylesheet -->
    <style>
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

        .custom-tab {
            border: none !important;
            margin-right: 10px;
            border-radius: 20px !important;
            padding: 10px 20px !important;
            font-weight: 500;
        }

        .custom-tab.active {
            background: linear-gradient(45deg, #2196F3, #1976D2) !important;
            color: white !important;
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
            color: #1976D2;
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
        /* Basic reset and styling */


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
            right: 45%;
            z-index: 99999;
        }
    </style>






    {{-- Action Buttons Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-start gap-2">


                <a href="{{ url('user/site/payments', [$site->id]) }}" class="btn btn-info px-4">
                    <i class="fas fa-list me-2"></i>View Payments
                </a>



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




    <div id="messageContainer"> </div>


    @if ($site)

        @if ($site->phases->count() > 0)

            <div class="card-body mt-3">
                <ul class="nav nav-pills mb-4">
                    @foreach ($site->phases as $phase_key => $phase)
                        <li class="nav-item">

                            <a class="nav-link custom-tab {{ $phase_key === 0 ? 'active' : '' }}"
                                href="#{{ $phase->id }}" data-bs-toggle="tab">
                                {{ $phase->phase_name }}</a>
                        </li>
                    @endforeach

                </ul>
            </div>



            <div class="tab-content">
                @foreach ($site->phases as $phase_key => $phase)
                    <div class="tab-pane fade {{ $phase_key === 0 ? 'show active' : '' }}" id="{{ $phase->id }}">


                        <div class=" mb-3 d-flex justify-content-end g-2">

                            <a href="{{ url('client/download-phase/report', ['id' => base64_encode($phase->id)]) }}"
                                class="btn btn-info btn-sm text-white">
                                Generate Phase PDF
                            </a>

                        </div>

                        <div class="row">

                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card h-100">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-tasks text-info"></i>
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
                                                        {{-- <td>
                                                                Service Charge + Total Amount
                                                            </td> --}}

                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            Raw Materials
                                                        </td>
                                                        <td>
                                                            {{ $phase->construction_total_amount }}
                                                        </td>
                                                        <td>
                                                            ....
                                                            {{-- {{ ($site->service_charge / 100) * $phase->construction_total_amount }} --}}
                                                        </td>
                                                        <td>

                                                            {{ ($site->service_charge / 100) * $phase->construction_total_amount + $phase->construction_total_amount }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            Square Footage
                                                        </td>

                                                        <td>
                                                            {{ $phase->square_footage_total_amount }}
                                                        </td>

                                                        <td>
                                                            ....
                                                            {{-- {{ ($site->service_charge / 100) * $phase->square_footage_total_amount }} --}}
                                                        </td>

                                                        <td>

                                                            {{ ($site->service_charge / 100) * $phase->square_footage_total_amount + $phase->square_footage_total_amount }}
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

                                                            {{-- {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount }} --}}
                                                        </td>

                                                        <td>

                                                            {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount }}
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
                                                            ....
                                                            {{-- {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount }} --}}
                                                        </td>

                                                        <td>

                                                            {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount }}
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <td>Sub Total</td>

                                                        <td>
                                                            {{ Number::currency(
                                                                $phase->construction_total_amount +
                                                                    $phase->square_footage_total_amount +
                                                                    $phase->daily_expenses_total_amount +
                                                                    $phase->daily_wagers_total_amount,
                                                                'INR',
                                                            ) }}

                                                        </td>

                                                        <td>
                                                            {{ Number::currency($phase_service_charge, 'INR') }}



                                                        </td>
                                                        <td>
                                                            {{ Number::currency($grand_total_amount, 'INR') }}


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
                                            <i class="fas fa-boxes text-info"></i>
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
                                                        <th>
                                                            Edit
                                                        </th>
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
                                                                    <img src="{{ asset($construction_material_billing->item_image_path) }}"alt=""
                                                                        class="w-20 h-20 rounded-full">
                                                                </td>
                                                                <td>
                                                                    <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                                        href="{{ route('suppliers.show', [base64_encode($construction_material_billing->supplier->id)]) }}">
                                                                        <mark>
                                                                            {{ $construction_material_billing->supplier->name ?? '' }}
                                                                        </mark>
                                                                    </a>
                                                                </td>

                                                                <td>
                                                                    {{ $construction_material_billing->item_name }}
                                                                </td>

                                                                <td>
                                                                    {{ $construction_material_billing->amount }}
                                                                    <br>
                                                                </td>

                                                                <td class="space-x-4">

                                                                    <a
                                                                        href="{{ route('construction-material-billings.edit', [base64_encode($construction_material_billing->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                    </a>



                                                                </td>


                                                            </tr>

                                                            @if ($loop->last)
                                                                <tr class="">
                                                                    <td colspan="4"
                                                                        class="text-right font-bold bg-info text-white fw-bold">
                                                                        Total Cost + Cost:</td>

                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white fw-bold">
                                                                        {{ $site->service_charge }}%
                                                                        +
                                                                        {{ $phase->construction_total_amount }}
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
                                            <i class="fas fa-hard-hat text-info"></i>
                                            Labor Bills
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

                                                        <th>
                                                            Edit
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
                                                                    <img src="{{ asset($sqft->image_path) }}"alt=""
                                                                        class="w-20 h-20 rounded-full">
                                                                </td>
                                                                <td>
                                                                    {{ ucwords($sqft->wager_name) }}
                                                                </td>

                                                                <td>
                                                                    {{ ucwords($sqft->supplier->name) }}

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

                                                                <td>
                                                                    <a
                                                                        href="{{ route('square-footage-bills.edit', [base64_encode($sqft->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                    </a>


                                                                </td>
                                                            </tr>
                                                            @if ($loop->last)
                                                                <tr class="">
                                                                    <td colspan="7"
                                                                        class="text-right font-bold bg-info text-white fw-bold">

                                                                        Total Amount + Cost
                                                                    </td>

                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white fw-bold">
                                                                        {{ $site->service_charge }}%
                                                                        +
                                                                        {{ $phase->square_footage_total_amount }}
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
                                                {{-- <thead>
                                                    <tr>
                                                        <th>Role</th>
                                                        <th>Count</th>
                                                        <th>Hours</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user-hard-hat text-warning me-2"></i>
                                                                Mason
                                                            </div>
                                                        </td>
                                                        <td>5</td>
                                                        <td>40</td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success"
                                                                    style="width: 100%"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-user text-warning me-2"></i>
                                                                Helper
                                                            </div>
                                                        </td>
                                                        <td>3</td>
                                                        <td>40</td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-success"
                                                                    style="width: 100%"></div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody> --}}
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
                                            <i class="fas fa-clipboard-check text-info"></i>
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
                                                        <th>
                                                            Edit
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    {{-- Square Footage --}}



                                                    @if (count($phase->dailyExpenses))
                                                        @foreach ($phase->dailyExpenses as $daily_expenses)
                                                            <tr>


                                                                <td>{{ $daily_expenses->created_at->format('d-M-Y') }}
                                                                </td>

                                                                <td>
                                                                    <img src="{{ asset($daily_expenses->bill_photo) }}"
                                                                        alt="">
                                                                </td>

                                                                <td>
                                                                    {{ ucwords($daily_expenses->item_name) }}
                                                                </td>

                                                                <td>
                                                                    {{ $daily_expenses->price }}
                                                                </td>

                                                                <td>
                                                                    <a
                                                                        href="{{ route('daily-expenses.edit', [base64_encode($daily_expenses->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                    </a>

                                                                </td>

                                                            </tr>
                                                            @if ($loop->last)
                                                                <tr>
                                                                    <td colspan="3"
                                                                        class="text-right font-bold bg-info text-white   fw-bold">
                                                                        Total Amount:</td>
                                                                    <td colspan="2"
                                                                        class="font-bold bg-info text-white  fw-bold">
                                                                        {{ $site->service_charge }}%
                                                                        +
                                                                        {{ $phase->daily_expenses_total_amount }}
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

                            <!-- Progress Table -->
                            <div class="col-lg-12 mb-4">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <h3 class="card-title-custom mb-4">
                                            <i class="fas fa-user text-info"></i>
                                            Daily Wager
                                        </h3>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-custom">

                                                <thead>
                                                    <tr>

                                                        <th>Date</th>

                                                        <th> Wager Name </th>
                                                        <th> Price Per Day </th>
                                                        <th>
                                                            Edit
                                                        </th>


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
                                                                    <a
                                                                        href="{{ route('dailywager.edit', [base64_encode($daily_wager->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                    </a>



                                                                </td>
                                                            </tr>
                                                            @if ($loop->last)
                                                                <tr class="">
                                                                    <td colspan="2"
                                                                        class="text-right font-bold bg-info text-white fw-bold">
                                                                        Total Amount + Cost</td>
                                                                    <td colspan="2"
                                                                        class=" font-bold bg-info text-white fw-bold">
                                                                        {{ $site->service_charge }}%
                                                                        +
                                                                        {{ $phase->daily_wagers_total_amount }}
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
                                            <i class="fas fa-clipboard-check text-info"></i>
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
                                                        <th>
                                                            Edit
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
                                                                    {{ ucwords($wager_attendance->dailyWager->supplier->name ?? '') }}
                                                                </td>


                                                                <td>
                                                                    <a
                                                                        href="{{ route('daily-wager-attendance.edit', [base64_encode($wager_attendance->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                    </a>



                                                                    {{-- @if ($wager_attendance->verified_by_admin)
                                                                        <a
                                                                            href="{{ route('verifyAttendance', [$wager_attendance->id]) }}">
                                                                            <i class="fa-solid fa-x"></i>
                                                                        </a>
                                                                    @else
                                                                        <a
                                                                            href="{{ route('verifyAttendance', [$wager_attendance->id]) }}">
                                                                            <i class="fa-solid fa-check"></i>

                                                                        </a>
                                                                    @endif --}}

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


                                                    <tr>
                                                        <form class="forms-sample material-form" id="wagerAttendance">

                                                            @csrf




                                                            <td>
                                                                <!-- No Of Persons -->
                                                                <div style="">
                                                                    <input id="no_of_persons" type="number"
                                                                        name="no_of_persons"
                                                                        placeholder="No Of Persons"
                                                                        style="width: 100%; border: 0; outline: 1px solid #dee2e6; border-radius: 5px;" />

                                                                    @error('no_of_persons')
                                                                        <x-input-error :messages="$message" class="mt-2" />
                                                                    @enderror
                                                                </div>

                                                            </td>

                                                            <td>
                                                                <!-- Wager -->
                                                                <select class="form-select form-select-sm"
                                                                    id="daily_wager_id" name="daily_wager_id">
                                                                    <option value="">Select Wager
                                                                    </option>

                                                                    @foreach ($wagers as $wager)
                                                                        <option value="{{ $wager->id }}">
                                                                            {{ $wager->wager_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('daily_wager_id')
                                                                    <x-input-error :messages="$message" class="mt-2" />
                                                                @enderror
                                                            </td>

                                                            <td>

                                                                <div>
                                                                    <input id="phase_id" type="hidden"
                                                                        name="phase_id" placeholder="Phase"
                                                                        value="{{ $phase->id }}" />
                                                                    @error('phase_id')
                                                                        <x-input-error :messages="$message" class="mt-2" />
                                                                    @enderror
                                                                </div>

                                                                <div>

                                                                    <input type="date" name="date"
                                                                        class="form-control">
                                                                    @error('date')
                                                                        <x-input-error :messages="$message" class="mt-2" />
                                                                    @enderror
                                                                </div>

                                                            </td>

                                                            <td>
                                                                <button class="btn  btn-info btn-sm mt-2 text-white">
                                                                    {{ __('Make Attendance') }}
                                                                </button>
                                                            </td>


                                                        </form>
                                                    </tr>

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


                    {{-- All Models Are Here --}}
                    <div>

                        <!-- Modal 1 -->
                        <div id="modal-construction-billings{{ $phase->id }}" class="modal fade"
                            aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <form enctype="multipart/form-data" class="forms-sample material-form"
                                            id="constructionBillingForm">

                                            @csrf


                                            <!-- Amount -->
                                            <div class="form-group">
                                                <input id="amount" type="number" name="amount"
                                                    placeholder="Material Price" />

                                                @error('amount')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <!-- Item Name -->
                                                <div class="col-md-6">


                                                    <select class="form-select form-select-sm"
                                                        id="exampleFormControlSelect3" name="item_name">
                                                        <option value="">Select Item
                                                        </option>
                                                        @foreach ($items as $item)
                                                            <option value="{{ $item->item_name }}">
                                                                {{ $item->item_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('item_name')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <!-- Supplier -->
                                                <div class="col-md-6">
                                                    <select class="form-select form-select-sm"
                                                        id="exampleFormControlSelect3" name="supplier_id">
                                                        <option value="">Select Supplier
                                                        </option>
                                                        @foreach ($raw_material_providers as $supplier)
                                                            <option value="{{ $supplier->id }}">
                                                                {{ $supplier->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <!-- Phases -->
                                                <div class=" col-md-6 mt-3">
                                                    <input id="phase_id" type="hidden" name="phase_id"
                                                        placeholder="Phase" value="{{ $phase->id }}" />
                                                    @error('phase_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>
                                            </div>

                                            <!-- Item Bill Photo -->
                                            <div class="mt-3">
                                                <input class="form-control form-control-md" id="image"
                                                    type="file" name="image">
                                            </div>

                                            <div class="flex items-center justify-end mt-4">
                                                <x-primary-button class="ms-4">
                                                    {{ __('Create Billing') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Modal 2 -->
                        <div id="modal-square-footage-bills{{ $phase->id }}" class="modal fade"
                            aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body">

                                        {{-- Create Square Footage Bills --}}
                                        <form id="squareFootageBills" enctype="multipart/form-data"
                                            class="forms-sample material-form">


                                            @csrf

                                            <!-- Wager Name -->
                                            <div class="form-group">
                                                <input id="wager_name" type="text" name="wager_name" />
                                                <label for="wager_name" class="control-label" />Work
                                                Type</label><i class="bar"></i>

                                                @error('wager_name')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <!-- Price -->
                                            <div class="form-group">
                                                <input id="price" type="number" name="price" />
                                                <label for="price" class="control-label" />Price</label><i
                                                    class="bar"></i>

                                                @error('price')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <!-- Number Of Days -->
                                            <div class="form-group">
                                                <input id="multiplier" type="number" name="multiplier" />
                                                <label for="multiplier" class="control-label">Multiplier</label><i
                                                    class="bar"></i>

                                                @error('multiplier')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>



                                            <div class="row">

                                                <div class="col-md-6">
                                                    <!-- Type -->
                                                    <select class="form-select form-select-sm"
                                                        id="exampleFormControlSelect3" name="type">
                                                        <option value="">Select Type</option>
                                                        <option value="per_sqr_ft">Per Square Feet</option>
                                                        <option value="per_unit">Per Unit</option>
                                                        <option value="full_contract">Full Contract
                                                        </option>
                                                    </select>
                                                    @error('type')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <!-- Select Supplier -->
                                                    <select class="form-select form-select-sm" id="supplier_id"
                                                        name="supplier_id">
                                                        <option value="">Select Supplier</option>
                                                        @foreach ($workforce_suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}">
                                                                {{ $supplier->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <div class=" col-md-6 mt-3">
                                                    <input id="phase_id" type="hidden" name="phase_id"
                                                        placeholder="Phase" value="{{ $phase->id }}" />
                                                    @error('phase_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>
                                            </div>


                                            <!-- Image -->
                                            <div class="mt-3">
                                                <label for="image">Item Bill</label>
                                                <input class="form-control form-control-md" id="image"
                                                    type="file" name="image_path">
                                                @error('image_path')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <div class="flex items-center justify-end mt-4">

                                                <x-primary-button class="ms-4">
                                                    {{ __('Create Bill') }}
                                                </x-primary-button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal 3 -->
                        <div id="modal-daily-wager{{ $phase->id }}" class="modal fade" aria-hidden="true"
                            aria-labelledby="exampleModalToggleLabel" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body">

                                        <form class="forms-sample material-form" id="dailyWager">

                                            @csrf

                                            <!-- Wager Name -->
                                            <div class="form-group">
                                                <input id="wager_name" type="text" name="wager_name" />
                                                <label for="wager_name" class="control-label">Wager
                                                    Name</label><i class="bar"></i>

                                                @error('wager_name')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <!-- Price Per day -->
                                            <div class="form-group">
                                                <input id="price_per_day" type="number" name="price_per_day" />
                                                <label for="price_per_day" class="control-label">Price Per
                                                    Day</label><i class="bar"></i>

                                                @error('price_per_day')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <!-- Select Supplier -->
                                                <div class="">
                                                    <select class="form-select form-select-sm" id="supplier_id"
                                                        name="supplier_id">
                                                        <option value="">Select Supplier</option>
                                                        @foreach ($workforce_suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}">
                                                                {{ $supplier->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <!-- Select Phase -->
                                                <div class=" col-md-6 mt-3">
                                                    <input id="phase_id" type="hidden" name="phase_id"
                                                        placeholder="Phase" value="{{ $phase->id }}" />
                                                    @error('phase_id')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>
                                            </div>



                                            <div class="flex items-center justify-end mt-4">



                                                <x-primary-button>
                                                    {{ __('Create Wager') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal 4 -->
                        <div id="modal-daily-expenses{{ $phase->id }}" class="modal fade" aria-hidden="true"
                            aria-labelledby="exampleModalToggleLabel" tabindex="-1">

                            {{-- Daily Expenses  --}}
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        <form id="dailyExpenses" class="forms-sample material-form">

                                            @csrf

                                            <!-- Wager Name -->
                                            <div class="form-group">
                                                <input id="item_name" type="text" name="item_name" />
                                                <label for="item_name" class="control-label">Item
                                                    Name</label><i class="bar"></i>
                                                @error('item_name')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <!-- Price -->
                                            <div class="form-group">
                                                <input id="price" type="number" name="price" />
                                                <label for="price" class="control-label">Price</label><i
                                                    class="bar"></i>
                                                @error('price')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>

                                            <!-- Select Phase -->
                                            <div class=" col-md-6 mt-3">
                                                <input id="phase_id" type="hidden" name="phase_id"
                                                    placeholder="Phase" value="{{ $phase->id }}" />
                                                @error('phase_id')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>


                                            <div class="col-12 mt-3">

                                                <input class="form-control" type="file" id="formFile"
                                                    name="bill_photo">
                                                @error('bill_photo')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror

                                            </div>

                                            <div class="flex items-center justify-end mt-4">

                                                <x-primary-button class="ms-4">
                                                    {{ __('Create Bill') }}
                                                </x-primary-button>
                                            </div>
                                        </form>
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
                        <td class="text-danger fw-bold text-center">No Site Data Awailiable..</td>
                    </tr>
                </tbody>
            </table>

        @endif

        {{-- Phase Form --}}
        <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
            tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">

                        <form class="forms-sample material-form" id="phaseForm">

                            @csrf

                            {{-- Phase Name --}}
                            <div class="form-group">
                                <input type="text" name="phase_name" id="phase_name" />
                                <label for="phase_name" class="control-label">Phase Name</label>
                                <i class="bar"></i>
                                <x-input-error :messages="$errors->get('phase_name')" class="mt-2" />
                            </div>

                            <!-- Site -->
                            <div class="form-group">
                                <input type="hidden" name="site_id" value="{{ $site->id }}" />
                                <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button>
                                    {{ __('Create Phase') }}
                                </x-primary-button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>

        </div>



        {{-- Payment Supplier --}}
        <div id="payment-supplier" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
            tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">

                        <form id="payment_supplierForm" class="forms-sample material-form"
                            enctype="multipart/form-data">

                            @csrf

                            {{-- Phase Name --}}
                            <div class="form-group">
                                <input type="number" min="0" name="amount" step="0.01" />
                                <label for="input" class="control-label">Amount</label><i class="bar"></i>
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>

                            <!-- Site -->
                            <div class="form-group">
                                <input type="hidden" name="site_id" value="{{ $site->id }}" />
                                <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                            </div>

                            {{-- Supplier --}}

                            <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror

                            {{-- Screenshot --}}
                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="screenshot">
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button>
                                    {{ __('Pay') }}
                                </x-primary-button>
                            </div>


                        </form>

                    </div>
                </div>

            </div>
        </div>

    @endif







</x-app-layout>
