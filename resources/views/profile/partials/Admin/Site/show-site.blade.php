<x-app-layout>


    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <style>
        #messageContainer {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
        }


        .header-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-icon {
            background-color: #3b82f6;
            color: white;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .tab-container {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .tab {
            display: inline-block;
            padding: 10px 0;
            margin-right: 30px;
            cursor: pointer;
        }

        .tab.active {
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
            font-weight: 500;
        }

        .badge {
            background-color: #e5e7eb;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            margin-left: 5px;
        }

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        .filter-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
        }

        .summary-card.gave {
            background-color: #fee2e2;
        }

        .summary-card.got {
            background-color: #d1fae5;
        }

        .summary-card.balance {
            background-color: #e0e7ff;
        }

        .summary-amount {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 14px;
        }

        .gave-text {
            color: #dc2626;
        }

        .got-text {
            color: #059669;
        }

        .balance-text {
            color: #4f46e5;
        }

        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .report-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #4b5563;
        }

        .report-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .report-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline {
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
        }
    </style>


    <x-breadcrumb :names="['Sites', $site->site_name]" :urls="[$user . '/sites', $user . '/sites/' . base64_encode($site->id)]" />

    <div class="header-container">


        <div class="header-icon">
            <i class="menu-icon fa fa-building"></i>
        </div>

        <h2 class="text-xl font-semibold">{{ ucwords($site->site_name) }} | {{ ucwords($site->client->name) }}</h2>

        <div class="ms-auto action-buttons d-flex gap-2">


            <button class="btn btn-outline btn-sm" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-bolt me-1"></i> Quick Actions
            </button>

            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <!-- Entry Actions -->
                <li>
                    <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#phase">
                        <i class="fas fa-layer-group me-2"></i> Add Phase
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#modal-construction-billings">
                        <i class="fas fa-truck-loading me-2"></i> Add Construction Billing
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#modal-square-footage-bills">
                        <i class="fas fa-ruler-combined me-2"></i> Add Contractor Billing
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#modal-daily-expenses">
                        <i class="fas fa-receipt me-2"></i> Add Daily Expense
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#payment-supplier" data-bs-toggle="modal" role="button">
                        <i class="fas fa-money-bill me-2"></i> Pay balance
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- View / Utility Actions -->
                <li>
                    <a class="dropdown-item" href="{{ url('admin/sites/details/' . base64_encode($id)) }}">
                        <i class="fas fa-info-circle me-2"></i> View Site Details
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ url($user . '/attendance/site/show/' . $id) }}">
                        <i class="fas fa-calendar-check me-2"></i> View Attendance
                    </a>
                </li>
                <li>
                    <a class="dropdown-item"
                        href="{{ url('admin/site-payment/report', ['id' => base64_encode($id)]) }}">
                        <i class="fas fa-file-invoice-dollar me-2"></i> View Payment Report
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ url('admin/sites/payments', [$id]) }}">
                        <i class="fas fa-list me-2"></i>
                        View Payments
                    </a>
                </li>
            </ul>

            <form action="{{ url($user . '/ledger/report') }}" method="GET">
                <input type="hidden" name="site_id" value="{{ request('site_id', $id) }}">
                <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                <input type="hidden" name="phase_id" value="{{ request('phase_id', 'all') }}">
                <button type="submit" class="btn btn-outline">
                    <i class="far fa-file-pdf"></i> PDF
                </button>
            </form>
            
        </div>

    </div>



    <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url()->current() }}" method="GET"
        id="filterForm">

        <!-- Supplier Select -->
        <select class="bg-white text-black form-select form-select-sm" name="phase_id" id="phaseFilter">
            <option value="all" {{ request('phase_id') == 'all' ? 'selected' : '' }}>All Phases</option>
            @if (!empty($phases))
                @foreach ($phases as $phase)
                    <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                        {{ $phase->phase_name }}
                    </option>
                @endforeach
            @endif
        </select>

        <!-- Supplier Select -->
        <select class="bg-white text-black form-select form-select-sm" name="supplier_id" id="supplierFilter">
            <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>All Suppliers</option>
            @if (!empty($suppliers))
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier['supplier_id'] }}"
                        {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                        {{ $supplier['supplier_name'] }}
                    </option>
                @endforeach
            @endif
        </select>

        <!-- Date Period Filter -->
        <select class="bg-white text-black form-select form-select-sm" name="date_filter" id="dateFilter">
            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
            <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday
            </option>
            <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week
            </option>
            <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month
            </option>
            <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year
            </option>
            <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
            <option value="lifetime" {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>All Data</option>
        </select>



        <!-- Date Range Inputs (shown only when custom is selected) -->
        <div id="customDateRange"
            style="display: {{ request('date_filter') === 'custom' ? 'flex' : 'none' }}; gap: 0.5rem;">
            <input type="date" name="start_date" class="form-control form-control-sm bg-white text-black"
                value="{{ request('start_date') }}" placeholder="Start Date">
            <input type="date" name="end_date" class="form-control form-control-sm bg-white text-black"
                value="{{ request('end_date') }}" placeholder="End Date">
        </div>

        <!-- Reset Button -->
        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
            <i class="fas fa-undo"></i> Reset
        </button>
    </form>


    <div class="mt-4">

        <div class="summary-cards">

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ $total_balance }}</div>
                <div class="summary-label gave-text">Total Balance</div>
            </div>

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ $total_due }}</div>
                <div class="summary-label gave-text">Total Due</div>
            </div>

            <div class="summary-card balance">
                <div class="summary-amount balance-text">₹{{ $effective_balance }}</div>
                <div class="summary-label balance-text">Effective Balance</div>
            </div>

            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ $total_paid }}</div>
                <div class="summary-label got-text">Total Paid</div>
            </div>

            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ $returns }}</div>
                <div class="summary-label got-text">Total Returns</div>
            </div>


        </div>




        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="fw-bold">Date</th>
                        <th class="fw-bold">Customer Name</th>
                        <th class="fw-bold">Details</th>
                        <th class="fw-bold">Returns</th>
                        <th class="fw-bold ">Purchases</th>
                        <th class="fw-bold ">Payments</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($paginatedLedgers->count())
                        @foreach ($paginatedLedgers as $ledger)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ledger['created_at'])->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ ucwords($ledger['supplier']) }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ ucwords($ledger['description']) }}</div>
                                    <small class="text-muted">
                                        {{ ucwords($ledger['phase']) }} / {{ $ledger['category'] }}
                                    </small>
                                </td>
                                <td class=" text-danger fw-bold">
                                    @if ($ledger['return'] > 0)
                                        ₹{{ number_format($ledger['return']) }}
                                    @else
                                        ₹0
                                    @endif
                                </td>
                                <td class="fw-bold">
                                    @if ($ledger['debit'] > 0)
                                        ₹{{ number_format($ledger['debit']) }}
                                    @else
                                        <div class="fw-bold">₹0</div>
                                        <small class="text-muted">
                                            {{ ucwords($ledger['amount_status']) }} 
                                        </small>
                                    @endif
                                </td>
                                <td class=" text-success fw-bold">
                                    @if ($ledger['credit'] > 0)
                                        ₹{{ number_format($ledger['credit']) }}
                                    @else
                                        ₹0
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No records available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($paginatedLedgers->hasPages())
            <div class="p-3 border-top">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0">
                        @if ($paginatedLedgers->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginatedLedgers->previousPageUrl() }}"
                                    rel="prev">&laquo;</a>
                            </li>
                        @endif

                        @foreach ($paginatedLedgers->getUrlRange(1, $paginatedLedgers->lastPage()) as $page => $url)
                            @if ($page == $paginatedLedgers->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        @if ($paginatedLedgers->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginatedLedgers->nextPageUrl() }}"
                                    rel="next">&raquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">&raquo;</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        @endif


    </div>

    <div id="messageContainer"></div>


    {{-- ------------------------------------------------------- All The Models Are Here ----------------------------------------------------------- --}}


    {{-- Phase Form --}}

    <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
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
                        </div>

                        {{-- Site --}}
                        <div class="form-group">
                            <input type="hidden" name="site_id" value="{{ $id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                Create Phase
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Construction Material Form -->
    <div id="modal-construction-billings" class="modal fade" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="exampleModalToggleLabel" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    <form enctype="multipart/form-data" class="forms-sample material-form"
                        id="constructionBillingForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Amount -->
                                <div class="form-group mb-3">
                                    <input type="number" name="amount" id="amount" step="0.01"
                                        min="0" />
                                    <label for="amount" class="control-label">Material Price</label>
                                    <i class="bar"></i>
                                    <p class="mt-1 text-danger" id="amount-error"></p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Unit -->
                                <div class="form-group mb-3">
                                    <input type="number" name="unit_count" id="unit_count" min="1" />
                                    <label for="unit_count" class="control-label">Units</label>
                                    <i class="bar"></i>
                                    <p class="mt-1 text-danger" id="unit_count-error"></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Item Name - Toggleable Field -->
                            <div class="col-12 mb-3">
                                <div class="btn-group btn-group-sm mb-2" role="group">
                                    <button type="button" class="btn btn-outline-primary toggle-item-btn active"
                                        data-mode="select">View list</button>
                                    <button type="button" class="btn btn-outline-secondary toggle-item-btn"
                                        data-mode="custom">Enter custom</button>
                                </div>

                                <!-- Item Select (visible by default) -->
                                <div id="item-select-container">
                                    <select class="form-select text-black form-select-sm" name="item_name"
                                        id="item_name">
                                        <option value="">Select Item</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->item_name }}">{{ $item->item_name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-danger" id="item_name-error"></p>
                                </div>

                                <!-- Custom Input (hidden by default) -->
                                <div id="custom-item-container" style="display: none;">
                                    <input type="text" class="form-control" name="custom_item_name"
                                        id="custom_item_name" placeholder="Enter item name">
                                    <p class="mt-1 text-danger" id="custom_item_name-error"></p>
                                </div>
                            </div>

                            <!-- Supplier -->
                            <div class="col-md-6 mb-3">
                                <select class="form-select text-black form-select-sm" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach ($raw_material_providers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-danger" id="supplier_id-error"></p>
                            </div>

                            <!-- Phase -->
                            <div class="col-md-6 mb-3">
                                <select class="form-select text-black form-select-sm" name="phase_id">
                                    <option value="">Select Phase</option>
                                    @foreach ($phases as $phase)
                                        <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-danger" id="phase_id-error"></p>
                            </div>

                            <!-- Image Upload -->
                            <div class="col-12 mb-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="image">
                                <p class="mt-1 text-danger" id="image-error"></p>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Create Billing</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>


    {{-- Square Footage Bill Model --}}
    <div id="modal-square-footage-bills" class="modal fade" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="exampleModalToggleLabel" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    {{-- Create Square Footage Bills --}}
                    <form id="squareFootageBills" enctype="multipart/form-data" class="forms-sample material-form">

                        @csrf

                        <!-- Wager Name -->
                        <div class="form-group">
                            <input id="wager_name" type="text" name="wager_name" />
                            <label for="wager_name" class="control-label" />Work
                            Type</label><i class="bar"></i>
                            <p class="text-danger" id="wager_name-error"></p>
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input id="price" type="number" name="price" />
                            <label for="price" class="control-label" />Price</label><i class="bar"></i>
                            <p class="text-danger" id="price-error"></p>
                        </div>

                        <!-- Number Of Days -->
                        <div class="form-group">
                            <input id="multiplier" type="number" name="multiplier" />
                            <label for="multiplier" class="control-label">Multiplier</label><i class="bar"></i>

                            <p class="text-danger" id="multiplier-error"></p>
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <!-- Type -->
                                <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                    name="type" style="cursor: pointer">
                                    <option value="">Select Type</option>
                                    <option value="per_sqr_ft">Per Square Feet</option>
                                    <option value="per_unit">Per Unit</option>
                                    <option value="full_contract">Full Contract
                                    </option>
                                </select>
                                <p class="text-danger" id="type-error"></p>
                            </div>

                            <div class="col-md-4">
                                <!-- Select Supplier -->
                                <select class="form-select text-black form-select-sm" id="supplier_id"
                                    name="supplier_id" style="cursor: pointer">
                                    <option value="">Select Supplier</option>
                                    @foreach ($workforce_suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="text-danger" id="supplier_id-error"></p>

                            </div>


                            <!-- Phases -->
                            <div class="col-md-4">
                                <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                    name="phase_id" style="cursor: pointer">
                                    <option value="">Select Phase</option>
                                    @foreach ($phases as $phase)
                                        <option value="{{ $phase->id }}">
                                            {{ $phase->phase_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class=" mt-1 text-danger" id="phase_id-error"></p>
                            </div>
                        </div>


                        <!-- Image -->
                        <div class="mt-3">
                            <label for="image">Item Bill</label>
                            <input class="form-control form-control-md" id="image" type="file"
                                name="image_path">
                            <p class="text-danger" id="image_path-error"></p>

                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                {{ __('Create Bill') }}
                            </button>
                        </div>



                    </form>

                </div>
            </div>
        </div>
    </div>



    <!-- Daily Expense -->
    <div id="modal-daily-expenses" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

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
                            <p class="text-danger" id="date-error"></p>
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input id="price" type="number" name="price" />
                            <label for="price" class="control-label">Price</label><i class="bar"></i>
                            <p class="text-danger" id="description-error"></p>
                        </div>

                        <!-- sites -->
                        <div class="form-group">
                            <input id="site_id" type="hidden" name="site_id" value="{{ $site->id }}" />
                        </div>

                        <!-- Phases -->
                        <div class=" col-12">
                            <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                name="phase_id" style="cursor: pointer">
                                <option value="">Select Phase
                                </option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">
                                        {{ $phase->phase_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class=" mt-1 text-danger" id="phase_id-error"></p>
                        </div>


                        <div class="col-12 mt-3">

                            <input class="form-control" type="file" id="formFile" name="bill_photo">

                            <p class="text-danger" id="category_id-error"></p>

                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                {{ __('Create Bill') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


    {{-- Payment Modal --}}
    <div id="payment-supplier" class="modal fade" aria-hidden="true" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="payment_supplierForm" class="forms-sample material-form" enctype="multipart/form-data">
                        @csrf

                        <div class="d-flex align-items-center gap-2 mb-3 p-2 border-start border-3 border-primary">
                            <i class="bi bi-building text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Client</small>
                                <strong>{{ $site->client->name }}</strong>
                            </div>
                        </div>

                        {{-- Amount --}}
                        <div class="form-group">
                            <input type="text" name="amount" class="form-control" />
                            <label class="control-label">Amount</label>
                            <i class="bar"></i>
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>

                        {{-- Site (hidden) --}}
                        <input type="hidden" name="site_id" value="{{ $site->id }}" />

                        {{-- Select Payee --}}
                        <div class="mb-3">
                            <select name="payment_initiator" id="payment_initiator" style="cursor: pointer"
                                class="form-select text-black form-select-sm" onchange="togglePayOptions()">
                                <option value="">Select Payee</option>
                                <option value="1">Supplier</option>
                                <option value="0">Admin</option>
                            </select>
                            <div class="invalid-feedback" id="payment_initiator-error"></div>
                        </div>

                        {{-- Supplier Options --}}
                        <div id="supplierOptions" style="display: none;" class="mb-3">
                            <select name="supplier_id" id="supplier_id" class="form-select text-black"
                                style="cursor: pointer">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier['supplier_id'] }}">{{ $supplier['supplier_name'] }}
                                    </option>
                                @endforeach
                                <div class="invalid-feedback" id="supplier_id-error"></div>
                            </select>
                        </div>

                        {{-- Admin Options (Shown when Admin is selected) --}}
                        <div id="adminOptions" style="display: none;" class="mt-4">

                            <div class="row g-3 mt-2">
                                {{-- Sent Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_sent">
                                        <input type="radio" name="transaction_type" id="transaction_sent"
                                            value="1"> Return To {{ $site->client->name }}
                                    </label>
                                </div>
                                {{-- Received Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_received">
                                        <input type="radio" name="transaction_type" id="transaction_received"
                                            value="0"> Received By Admin
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            {{-- Screenshot Upload --}}
                            <label class="control-label mt-3">Upload Screenshot</label>
                            <input class="form-control" id="image" type="file" name="screenshot">
                            <div class="invalid-feedback" id="screenshot-error"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Pay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div id="messageContainer"></div>




    {{-- ------------------------------------------  All The Scripts For This Page Are Below ---------------------------------------- --}}

    <script>
        $(document).ready(function() {


            // Model Ajax Functions
            $('form[id="phaseForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url('admin/phase') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.html(`
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
                    error: function(xhr) {
                        const messageContainer = $('#messageContainer');
                        messageContainer.empty();

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;

                            // Loop through errors and display them next to inputs
                            $.each(errors, function(field, messages) {
                                const input = $(`[name="${field}"]`);
                                const formGroup = input.closest('.form-group');

                                input.addClass('is-invalid');

                                // Append Bootstrap validation error message
                                if (formGroup.length) {
                                    formGroup.append(
                                        `<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`
                                    );
                                } else {
                                    input.after(
                                        `<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`
                                    );
                                }
                            });


                        } else {
                            messageContainer.append(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    An unexpected error occurred. Please try again later.
                                </div>
                            `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 4000);
                    }



                });
            });


            // Toggle between item selection modes
            $('.toggle-item-btn').click(function() {
                const mode = $(this).data('mode');

                // Update button states
                $('.toggle-item-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');

                // Toggle fields
                if (mode === 'select') {
                    $('#item-select-container').show();
                    $('#custom-item-container').hide();
                    $('#custom_item_name').val(''); // Clear custom input
                } else {
                    $('#item-select-container').hide();
                    $('#custom-item-container').show();
                    $('#item_name').val(''); // Clear select
                }
            });

            // Form submission handler
            $('form[id^="constructionBillingForm"]').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                // Clear previous error messages
                form.find('.text-danger').text('');

                // Determine which item field to use
                if ($('#custom-item-container').is(':visible') && $('#custom_item_name').val()) {
                    formData.set('item_name', $('#custom_item_name').val());
                    formData.delete('custom_item_name');
                }

                $.ajax({
                    url: '{{ route('construction-material-billings.store') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.html(`
                    <div class="alert align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                            </div>
                        </div>
                    </div>
                `);

                        form[0].reset();
                        $('.toggle-item-btn[data-mode="select"]')
                            .click(); // Reset to select mode

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {
                        if (response.status === 422) {
                            const errors = response.responseJSON.errors;
                            for (const field in errors) {
                                const errorElement = $(`#${field}-error`);
                                if (errorElement.length) {
                                    errorElement.text(errors[field][0]);
                                } else {
                                    form.find(`[name="${field}"]`).siblings('.text-danger')
                                        .text(errors[field][0]);
                                }
                            }
                        } else {
                            messageContainer.html(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 5000);
                    }
                });
            });



            // Script For Square Footage Bills
            $('form[id^="squareFootageBills"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                // Clear previous error messages for this form only
                form.find('.text-danger').text('');

                $.ajax({
                    url: '{{ route('square-footage-bills.store') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.html(`
                <div class="alert align-items-center text-white bg-success border-0" role="alert">
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
                            const errors = response.responseJSON.errors;

                            // Loop through each error field
                            for (const field in errors) {
                                // Get the first error message from the array
                                const errorMsg = errors[field][0];

                                // First, try to find the field by name and update sibling error element
                                const inputField = form.find(`[name="${field}"]`);
                                if (inputField.length > 0) {
                                    // Try to find sibling error container
                                    const siblingError = inputField.siblings('.text-danger');
                                    if (siblingError.length > 0) {
                                        siblingError.text(errorMsg);
                                    } else {
                                        // If no sibling found, try to find by ID
                                        form.find(`#${field}-error`).text(errorMsg);
                                    }
                                } else {
                                    // If input not found, try to find error container by ID directly
                                    form.find(`#${field}-error`).text(errorMsg);
                                }
                            }

                            // Log fields that couldn't be found for debugging
                            for (const field in errors) {
                                const inputField = form.find(`[name="${field}"]`);
                                const errorContainer = form.find(`#${field}-error`);
                                if (inputField.length === 0 && errorContainer.length === 0) {
                                    console.log(
                                        `Warning: Could not find field or error container for: ${field}`
                                    );
                                }
                            }
                        } else {
                            messageContainer.html(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                        An unexpected error occurred. Please try again later.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                        }

                        // Auto-hide general error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 5000);
                    }
                });
            });



            // Script For Daily Expense
            $('form[id^="dailyExpenses"]').on('submit', function(e) {

                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer') // Form-specific message container
                messageContainer.empty();

                // Clear previous error messages for this form only
                form.find('.text-danger').text('');

                $.ajax({
                    url: '{{ route('daily-expenses.store') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.html(`
                    <div class="alert align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                            </div>
                        </div>
                    </div>
                `);

                        form[0].reset();

                        // Auto-hide success message after 2 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) {

                            const errors = response.responseJSON.errors;

                            // Loop through each error field
                            for (const field in errors) {
                                // Get the first error message from the array
                                const errorMsg = errors[field][0];

                                // First, try to find the field by name and update sibling error element
                                const inputField = form.find(`[name="${field}"]`);
                                if (inputField.length > 0) {
                                    // Try to find sibling error container
                                    const siblingError = inputField.siblings(
                                        '.text-danger');
                                    if (siblingError.length > 0) {
                                        siblingError.text(errorMsg);
                                    } else {
                                        // If no sibling found, try to find by ID
                                        form.find(`#${field}-error`).text(errorMsg);
                                    }
                                } else {
                                    // If input not found, try to find error container by ID directly
                                    form.find(`#${field}-error`).text(errorMsg);
                                }
                            }
                        } else {
                            messageContainer.html(`
                                 <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                     An unexpected error occurred. Please try again later.
                                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                 </div>
                             `);
                        }

                        // Auto-hide general error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 5000);
                    }
                });
            });




            // Script For Payment

            $('form[id="payment_supplierForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url('admin/sites/payments') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.html(`
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
                    error: function(xhr) {
                        const messageContainer = $('#messageContainer');
                        messageContainer.empty();

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;

                            // Loop through errors and display them next to inputs
                            $.each(errors, function(field, messages) {
                                const input = $(`[name="${field}"]`);
                                const formGroup = input.closest('.form-group');


                                // Append Bootstrap validation error message
                                if (formGroup.length) {
                                    formGroup.append(
                                        `<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`
                                    );
                                } else {
                                    input.after(
                                        `<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`
                                    );
                                }
                            });


                        } else {
                            messageContainer.append(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    An unexpected error occurred. Please try again later.
                                </div>
                            `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 4000);
                    }

                });
            });

        });

        // Script For Payment Form
        function togglePayOptions() {
            const payTo = document.getElementById('payment_initiator').value; // Get the selected value
            const supplierOptions = document.getElementById('supplierOptions'); // Supplier section
            const adminOptions = document.getElementById('adminOptions'); // Admin section

            // Check selected value and toggle visibility accordingly
            if (payTo === "1") {
                supplierOptions.style.display = 'block'; // Show Supplier options
                adminOptions.style.display = 'none'; // Hide Admin options
            } else if (payTo === "0") {
                supplierOptions.style.display = 'none'; // Hide Supplier options
                adminOptions.style.display = 'block'; // Show Admin options
            } else {
                // Hide both sections if "Select Payee" or invalid option is selected
                supplierOptions.style.display = 'none';
                adminOptions.style.display = 'none';
            }
        }


        document.addEventListener('DOMContentLoaded', function() {

            const supplierFilter = document.getElementById('supplierFilter');
            const filterForm = document.getElementById('filterForm');
            const dateFilter = document.getElementById('dateFilter');
            const phaseFilter = document.getElementById('phaseFilter');
            const customDateRange = document.getElementById('customDateRange');
            const resetBtn = document.getElementById('resetFilters');

            // Ensure form submits to the correct URL with site ID
            filterForm.action = "{{ url()->current() }}";

            // Toggle date range visibility
            dateFilter.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'flex';
                } else {
                    customDateRange.style.display = 'none';
                    // Clear date inputs when not using custom range
                    document.querySelector('input[name="start_date"]').value = '';
                    document.querySelector('input[name="end_date"]').value = '';
                }
                submitForm();
            });

            // Auto-submit when any filter changes (except date inputs)
            document.querySelectorAll('#filterForm select:not(#dateFilter)').forEach(select => {
                select.addEventListener('change', function() {
                    submitForm();
                });
            });

            // For date inputs, add a small delay before submitting
            document.querySelectorAll('#customDateRange input').forEach(input => {
                let timeout;
                input.addEventListener('change', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        submitForm();
                    }, 500);
                });
            });

            // Reset all filters
            resetBtn.addEventListener('click', function() {
                // Reset form values
                filterForm.reset();
                // Ensure default selections
                document.getElementById('supplierFilter').value = 'all';
                document.getElementById('dateFilter').value = 'today';
                document.getElementById('phaseFilter').value = 'all';
                // Hide custom date range
                customDateRange.style.display = 'none';
                // Submit the form
                submitForm();
            });

            // Initialize date range visibility based on current selection
            if (dateFilter.value === 'custom') {
                customDateRange.style.display = 'flex';
            }

            // Custom form submission to preserve URL structure
            function submitForm() {
                // Get all current query parameters
                const params = new URLSearchParams(window.location.search);

                // Update with new form values
                new FormData(filterForm).forEach((value, key) => {
                    if (value) params.set(key, value);
                    else params.delete(key);
                });

                // Preserve the site ID in the URL path
                const newUrl = "{{ url()->current() }}?" + params.toString();
                window.location.href = newUrl;
            }
        });
    </script>
</x-app-layout>
