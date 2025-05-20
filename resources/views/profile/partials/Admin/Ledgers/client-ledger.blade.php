<x-app-layout>


    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
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




    <div class="header-container">


        <div class="header-icon">
            <i class="menu-icon fa fa-building"></i>
        </div>

        <h1 class="text-xl font-semibold">Site Report</h1>

        <div class="ms-auto action-buttons d-flex gap-2">


            <form action="{{ url($user . '/ledger/report') }}" method="GET">
                <input type="hidden" name="site_id" value="{{ request('site_id', 'all') }}">
                <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                <input type="hidden" name="wager_id" value="{{ request('wager_id', 'all') }}">
                <button type="submit" class="btn btn-outline">
                    <i class="far fa-file-pdf"></i> Download PDF
                </button>
            </form>
        </div>

    </div>










    <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url()->current() }}" method="GET"
        id="filterForm">


        {{-- Select Sites --}}
        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm" name="site_id"
            onchange="document.getElementById('filterForm').submit();">
            <option value="all" {{ request('site_id') === 'all' ? 'selected' : '' }}>
                All Sites
            </option>
            @foreach ($sites as $site)
                <option value="{{ $site['site_id'] }}" {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
                    {{ $site['site'] }}
                </option>
            @endforeach
        </select>

        <!-- Supplier Select -->
        <select class="bg-white text-black form-select form-select-sm" name="supplier_id" id="supplierFilter">
            <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>All Suppliers</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier['supplier_id'] }}"
                    {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                    {{ $supplier['supplier'] }}
                </option>
            @endforeach
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
                <div class="summary-amount gave-text">₹{{ number_format($total_balance) }}</div>
                <div class="summary-label gave-text">Total Balance</div>
            </div>

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ number_format($total_due) }}</div>
                <div class="summary-label gave-text">Total Due</div>
            </div>

            <div class="summary-card balance">
                <div class="summary-amount balance-text">₹{{ number_format($effective_balance) }}</div>
                <div class="summary-label balance-text">Effective Balance</div>
            </div>

            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ number_format($total_paid) }}</div>
                <div class="summary-label got-text">Total Paid</div>
            </div>
        </div>



        <div class="card">
            <div class="table-responsive mt-4">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>Customer Name</th>
                            <th>DETAILS</th>
                            <th style="text-align: right;">Debit</th>
                            <th style="text-align: right;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($paginatedLedgers))
                            @foreach ($paginatedLedgers as $key => $ledger)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($ledger['created_at'])->format('d M Y') }}</td>
                                    <td>{{ ucwords($ledger['supplier']) }}</td>
                                    <td>
                                        {{ ucwords($ledger['description']) }}
                                        <div class="text-sm text-gray-500">
                                            {{ ucwords($ledger['phase']) }} / {{ $ledger['category'] }}
                                        </div>
                                    </td>
                                    <td style="text-align: right;" class="gave-text">
                                        @if ($ledger['debit'] > 0)
                                            ₹{{ number_format($ledger['debit']) }}
                                        @else
                                            ₹0
                                        @endif
                                    </td>
                                    <td style="text-align: right;" class="got-text">
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
                                <td colspan="5" class="text-center py-4 text-gray-500">No records available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $paginatedLedgers->links() }}
        </div>
    </div>

    <div id="messageContainer"></div>


    <!-- Daily Wager Form -->
    {{-- <div id="modal-daily-wager" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">
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

                            <p class="text-danger" id="wager_name-error"></p>
                        </div>

                        <!-- Price Per day -->
                        <div class="form-group">
                            <input id="price_per_day" type="number" name="price_per_day" />
                            <label for="price_per_day" class="control-label">Price Per
                                Day</label><i class="bar"></i>
                            <p class="text-danger" id="price_per_day-error"></p>

                        </div>

                        <div class="row">
                            <!-- Select Supplier -->
                            <div class="col-md-6 mt-3">
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
                            <div class="col-md-6 mt-3">
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
                        </div>

                        <x-primary-button>
                            {{ __('Create Wager') }}
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}





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
                        messageContainer.append(`
                             <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                                 <div class="d-flex">
                                    <div class="toast-body">
                                        <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                    </div>
                                </div>
                            </div> `);
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


            //  Script For Construction Form
            $('form[id^="constructionBillingForm"]').on('submit', function(e) {

                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = form.find('.message-container');
                messageContainer.empty();

                // Clear previous error messages for this form
                form.find('.text-danger').text('');

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

                        // Auto-hide success message after 2 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors

                            const errors = response.responseJSON.errors;

                            // Display general error message
                            messageContainer.html(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    Please fix the errors below
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);

                            // Display specific error for each field
                            for (const field in errors) {

                                const errorMsg = errors[field][0];

                                form.find(`[name="${field}"]`).siblings('.text-danger').text(
                                    errorMsg);

                                if (!form.find(`[name="${field}"]`).siblings('.text-danger')
                                    .length) {
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



            // Script For Square Footage Bills
            $('form[id^="squareFootageBills"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = form.find('.message-container');
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
                            console.log('Validation errors:', errors); // Debug log

                            // Display general error message
                            messageContainer.html(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                        Please fix the errors below
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);

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
                const messageContainer = form.find(
                    '.message-container'); // Form-specific message container
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

                        if (response.status === 422) { // Validation errors
                            const errors = response.responseJSON.errors;

                            // Display general error message
                            messageContainer.html(`
                              <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                  Please fix the errors below
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                             </div>
                            `);

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



            // Script For Daily Wager
            $('form[id^="dailyWager"]').on('submit', function(e) {

                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ route('dailywager.store') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.append(`
                            <div class="alert align-items-center text-white bg-success border-0" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                    </div>
                                </div>
                            </div>
                        `);

                        form[0].reset();

                        // Auto-hide success message and reload
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload(); // This should reload the page
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors

                            const errors = response.responseJSON.errors;

                            // Display general error message
                            messageContainer.html(`
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    Please fix the errors below
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);

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


            $('form[id^="wagerAttendance"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ route('daily-wager-attendance.store') }} ',
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

                        if (response.status === 422) {
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
                            messageContainer.append(
                                `

                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">${response.responseJSON.errors}</div>`
                            )

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
