<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    @if (session('status') === 'update')
        <x-success-message message="Your Record has been updated..." />
    @endif

    @if (session('status') === 'delete')
        <x-success-message message="Your Record has been deleted..." />
    @endif

    @if (session('status') === 'not_found')
        <x-success-message message="No Site Payments Available..." />
    @endif

    @if (session('status') === 'error')
        <x-success-message message="Something went wrong! try again..." />
    @endif

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

    @if ($user === 'admin')
        <x-breadcrumb :names="['Sites', $site->site_name]" :urls="['admin/sites', 'admin/sites/' . base64_encode($site->id)]" />
    @else
        <x-breadcrumb :names="['Sites', $site->site_name]" :urls="['user/dashboard', 'user/sites/' . base64_encode($site->id)]" />
    @endif

    {{-- Action Buttons Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-start gap-2">
                <a class="btn btn-info" data-bs-toggle="modal" href="#phase" role="button">
                    <i class="fas fa-tasks me-2"></i>Phase
                </a>

                <a class="btn btn-info" href="#payment-supplier" data-bs-toggle="modal" role="button">
                    <i class="fas fa-money-bill me-2"></i>Make Payment
                </a>

                <a href="{{ url('user/sites/payments', [$site->id]) }}" class="btn btn-info px-4">
                    <i class="fas fa-list me-2"></i>View Payments
                </a>

                <a href="{{ url('user/site/ledger', $site->id) }}" class="btn btn-info px-4">
                    <i class="fas fa-book me-2"></i>View Ledger
                </a>

                <a href="{{ url('user/download-site/report', ['id' => base64_encode($site->id)]) }}"
                    class="btn btn-info px-4">
                    <i class="fas fa-file-pdf me-2"></i>Download PDF
                </a>

                <a href="{{ url('user/site-payment/report', ['id' => base64_encode($site->id)]) }}"
                    class="btn btn-info px-4">
                    <i class="fas fa-file-invoice me-2"></i>Generate Payments
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Section --}}
    <div class="row g-4">
        <div class="col-12 col-md-6">
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

        <div class="col-12 col-md-6">
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


      
    </div>

    @if ($site)

        @if ($site->phases->count() > 0)

            <div class="card-body mt-3">

                <ul class="nav nav-pills mb-4">

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

                </ul>
            </div>

            <div class="tab-content border-0 p-0">
                @foreach ($site->phases as $phase_key => $phase)
                    <div class="tab-pane fade {{ $phase_key === 0 ? 'show active' : '' }}" id="{{ $phase->id }}">


                        <div class=" mb-3 d-flex justify-content-end gap-3">


                            <a href="{{ url($user . '/download-phase/report', ['id' => base64_encode($phase->id)]) }}"
                                class="btn btn-info btn-sm text-white">
                                Generate Phase PDF
                            </a>

                            <button class="btn btn-sm btn-info text-white dropdown-toggle" type="button"
                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Make Entry
                            </button>



                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                <li>
                                    <a class="btn" data-bs-toggle="modal" role="button"
                                        href="#modal-construction-billings{{ $phase->id }}">
                                        Construction
                                    </a>
                                </li>

                                <li>

                                    <a class="btn" data-bs-toggle="modal" role="button"
                                        href="#modal-square-footage-bills{{ $phase->id }}">
                                        Square Footage </a>

                                </li>

                                


                                <li class="nav-item dropdown-button">

                                    <a class="btn" data-bs-toggle="modal" role="button"
                                        href="#modal-daily-expenses{{ $phase->id }}">
                                        Daily Expenses
                                    </a>

                                </li>

                            </ul>



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
                                                        {{-- <td>
                                                                Service Charge + Total Amount
                                                            </td> --}}

                                                    </tr>
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
                                                        <th>
                                                            Actions
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
                                                                    <img style="cursor: pointer"
                                                                        data-full="{{ asset('storage/' . $construction_material_billing->item_image_path) }}"
                                                                        src="{{ asset('storage/' . $construction_material_billing->item_image_path) }}"
                                                                        alt=""
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

                                                                <td>

                                                                    <a
                                                                        href="{{ url('user/construction-material-billings', [base64_encode($construction_material_billing->id)]) }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square fs-5  bg-white rounded-full px-2 py-1"></i>
                                                                    </a>

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

                                                        <th>
                                                            Actions
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
                                                                        data-full="{{ asset('storage/' . $sqft->image_path) }}"
                                                                        src="{{ asset('storage/' . $sqft->image_path) }}"
                                                                        alt=""
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

                                                                <td>
                                                                    <a
                                                                        href="{{ url('user/square-footage-bills/' . base64_encode($sqft->id) . '/edit') }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square fs-5 bg-white rounded-full px-2 py-1"></i>
                                                                    </a>





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
                                                        <th>
                                                            Actions
                                                        </th>
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
                                                                    <img src="{{ asset('storage/' . $daily_expenses->bill_photo) }}"
                                                                        alt="{{ asset('storage/' . $daily_expenses->bill_photo) }}"
                                                                        class="w-20 h-20 rounded-full cursor-pointer"
                                                                        data-full="{{ asset('storage/' . $daily_expenses->bill_photo) }}">
                                                                </td>

                                                                <td>
                                                                    {{ ucwords($daily_expenses->item_name) }}
                                                                </td>



                                                                <td>
                                                                    {{ $daily_expenses->price }}
                                                                </td>

                                                                <td>

                                                                    <a
                                                                        href="{{ url($user . '/daily-expenses/' . base64_encode($daily_expenses->id) . '/edit') }}">
                                                                        <i
                                                                            class="fa-regular fa-pen-to-square fs-5 bg-white rounded-full px-2 py-1"></i>
                                                                    </a>

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
                                                <input type="number" name="amount" id="amount" />
                                                <label for="amount" class="control-label">Material Price</label>
                                                <i class="bar"></i>
                                                @error('amount')
                                                    <x-input-error :messages="$message" class="mt-2" />
                                                @enderror
                                            </div>


                                            <div class="row">
                                                <!-- Item Name -->
                                                <div class="col-md-6">
                                                    <select class="form-select text-black form-select-sm"
                                                        id="exampleFormControlSelect3" name="item_name"
                                                        style="cursor: pointer">
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
                                                    <select class="form-select text-black form-select-sm"
                                                        id="exampleFormControlSelect3" name="supplier_id"
                                                        style="cursor: pointer">
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
                                                    <select class="form-select text-black form-select-sm"
                                                        id="exampleFormControlSelect3" name="type"
                                                        style="cursor: pointer">
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
                                                    <select class="form-select text-black form-select-sm"
                                                        id="supplier_id" name="supplier_id" style="cursor: pointer">
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
                        {{-- <div id="modal-daily-wager{{ $phase->id }}" class="modal fade" aria-hidden="true"
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
                                                    <select class="form-select text-black form-select-sm"
                                                        id="supplier_id" name="supplier_id" style="cursor: pointer">
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
                        </div> --}}

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
                        <td class="text-danger fw-bold text-center">No Site Data Availiable..</td>
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

                        <form class="forms-sample material-form" id="phase">

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
                            <select class="form-select text-black form-select-sm" id="supplier_id" name="supplier_id"
                                style="cursor: pointer">
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
        let phaseform = document.getElementById('phase');
        console.log(phaseform);


        $(document).on('click', '.delete-link', function(e) {
            e.preventDefault();

            const link = $(this);
            const id = link.data('id');
            const name = link.data('name');
            const messageContainer = $('#messageContainer');
            messageContainer.empty();

            let url = '';

            switch (name) {
                case 'materials':
                    url = 'construction-material-billings';
                    break;
                case 'expenses':
                    url = 'daily-expenses';
                    break;
                case 'sqft':
                    url = 'square-footage-bills';
                    break;
                case 'wager':
                    url = 'dailywager';
                    break;
                case 'attendance':
                    url = 'daily-wager-attendance'
                    break;
                default:
                    console.error('Invalid name parameter');
                    return;
            }

            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }

            if (!url) {
                console.error('URL not set');
                return;
            }

            $.ajax({
                url: `{{ url('admin') }}/${url}/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response, xhr) {


                    link.closest('tr').remove();

                    messageContainer.append(`
                                 <div class="alert align-items-center text-white bg-success border-0" role="alert">
                                                 <div class="d-flex">
                                                     <div class="toast-body">
                                                         <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                                    </div>
                                                </div>
                                    </div>`);

                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 2000);
                },
                error: function(error) {

                    let errorMessage;

                    if (error.status === 404) {
                        errorMessage = error.responseJSON?.error || 'Resource not found.';
                    } else {
                        errorMessage = 'An error occurred. Please try again.';
                    }

                    messageContainer.append(`
                                <div class="alert align-items-center text-white bg-danger border-0" role="alert">
                                     <div class="d-flex">
                                        <div class="toast-body">
                                             <strong><i class="fas fa-exclamation-circle me-2"></i></strong>${errorMessage}
                                         </div>
                                    </div>
                                </div> `);

                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 2000);
                }
            });

        });

        $(document).on('click', '.verify-link', function(e) {

            e.preventDefault();

            const link = $(this);
            const id = link.data('id');
            const verified = link.data('verified');
            const messageContainer = $('#messageContainer');
            const name = link.data('name');
            messageContainer.empty();

            let url = '';

            switch (name) {
                case 'materials':
                    url = 'verify/materials';
                    break;
                case 'expenses':
                    url = 'verify/expenses';
                    break;
                case 'sqft':
                    url = 'verify/square-footage';
                    break;
                case 'wager':
                    url = 'verify/wagers';
                    break;
                case 'attendance':
                    url = 'verify/attendance'
                    break;
                default:
                    console.error('Invalid name parameter');
                    return;
            }

            // Make sure url is not empty before proceeding
            if (!url) {
                console.error('URL not set');
                return;
            }

            $.ajax({
                url: `{{ url('admin') }}/${url}/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    verified: verified
                },
                success: function(response) {
                    console.log(response);

                    if (verified == 1) {

                        link.html('Verified');
                        link.data('verified', 0);
                        link.removeClass('badge-danger').addClass('badge-info');

                    } else {

                        link.html('Verify');
                        link.data('verified', 1);
                        link.removeClass('badge-info').addClass('badge-danger');

                    }

                    // Show success message
                    if (response.message) {

                        messageContainer.append(`
                    <div class="alert align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                            </div>
                        </div>
                    </div>
                `);
                    }

                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 500);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    messageContainer.append(`
                <div class="alert align-items-center text-white bg-danger border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong><i class="fas fa-exclamation-circle me-2"></i></strong>An error occurred. Please try again.
                        </div>
                    </div>
                </div>
            `);

                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                            location.reload();

                        });
                    }, 500);
                }
            });
        });



        $(document).ready(function() {

            $('form[id="phase"]').on('submit', function(e) {

                e.preventDefault();
                console.log(e);


                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url('user/phase') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                                 <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                                     <div class="d-flex">
                                        <div class="toast-body">
                                            <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                        </div>
                                    </div>
                                </div> `);
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

            //  Script For Construction Form
            $('form[id^="constructionBillingForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/construction-material-billings') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                            `);

                        form[0].reset();

                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 2000);
                    }
                });
            });

            // Script For square Footage Bills
            $('form[id^="squareFootageBills"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/square-footage-bills') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        form[0].reset();

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

            $('form[id^="dailyExpenses"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/daily-expenses') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        form[0].reset();

                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

            $('form[id^="dailyWager"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/dailywager') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);

                        form[0].reset();

                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {


                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

            $('form[id^="wagerAttendance"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/daily-wager-attendance') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);

                        form[0].reset();

                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {
                        console.log(response);


                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

            $('form[id="payment_supplierForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url('user/site/payments') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        form[0].reset();

                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 2000);
                    },
                    error: function(response) {

                        console.log(response.responseJSON.errors);


                        if (response.status === 422) { // Validation errors


                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });

        });
    </script>


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