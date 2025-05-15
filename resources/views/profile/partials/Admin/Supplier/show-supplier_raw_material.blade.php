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


    <x-breadcrumb :names="['Suppliers', $data['supplier']->name]" :urls="[$user . '/suppliers', $user . '/suppliers/' . $data['supplier']->id]" />



    <div class="header-container">


        <div class="header-icon">
            <i class="menu-icon fa fa-building"></i>
        </div>
        <h1 class="text-xl font-semibold">Supplier Report</h1>
        <div class="ms-auto action-buttons d-flex gap-2">
            <!-- Dropdown Menu -->
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle " type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Make Entry
                </button>

                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">



                    <li>

                        <a href="{{ url($user . '/supplier/ledger', [$data['supplier']->id]) }}" class="btn  btns"
                            data-modal="payment-supplier">
                            View Ledger
                        </a>
                    </li>

                    <li>
                        @if ($user === 'admin')
                            <a href="{{ url($user . '/unverified-supplier-payments/' . $data['supplier']->id) }}"
                                class="btn">
                                Unverified Payments
                            </a>
                        @endif
                    </li>

                    <li>
                        <a href="{{ url($user . '/supplier/payments', [$data['supplier']->id]) }}"
                            class="btn btn-info btns" data-modal="payment-supplier">
                            View Payments
                        </a>
                    </li>
                </ul>
            </div>


            {{-- <a href="{{ url('admin/sites/details/' . base64_encode($id)) }}" class="btn btn-outline">
                <i class="fas fa-eye"></i> View Site Detail
            </a> --}}



            <a href="{{ url($user . '/supplier-payment/report', ['id' => base64_encode($data['supplier']->id)]) }}"
                class="btn btn-outline">
                <i class="far fa-file-pdf"></i>
                Payment Report
            </a>

            <a href="{{ url($user . '/supplier/detail', ['id' => $data['supplier']->id]) }}"
                class="btn btn-outline">
                <i class="far fa-file-pdf"></i>
                View Deatiled View
            </a>



            <button class="btn btn-info btns" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Make Payment
            </button>

        </div>
    </div>


     <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url()->current() }}" method="GET"
        id="filterForm">

        <!-- Supplier Site -->
        <select class="bg-white text-black form-select form-select-sm" name="site_id" id="supplierFilter">
            <option value="all" {{ request('site_id') == 'all' ? 'selected' : '' }}>All Sites</option>
            @foreach ($data['sites'] as $site)
                <option value="{{ $site['site_id'] }}"
                    {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
                    {{ $site['site_name'] }}
                </option>
            @endforeach
        </select>

        <!-- Date Period Filter -->
        <select class="bg-white text-black form-select form-select-sm" name="date_filter" id="dateFilter">
            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
            <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
            <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
            <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month
            </option>
            <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
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
                <div class="summary-amount gave-text">₹{{ number_format($data['balance']) }}</div>
                <div class="summary-label gave-text">Total Balance</div>
            </div>

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ number_format($data['totalDebit']) }}</div>
                <div class="summary-label gave-text">Total Due</div>
            </div>

            <div class="summary-card balance">
                <div class="summary-amount balance-text">₹{{ number_format($data['totalDebit']) }}</div>
                <div class="summary-label balance-text">Effective Balance</div>
            </div>

            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ number_format($data['totalCredit']) }}</div>
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
                        @if (count($data['ledgers']))
                            @foreach ($data['ledgers'] as $key => $ledger)
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
            {{-- {{ $paginatedLedgers->links() }} --}}
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <form id="payment_form" class="forms-sample material-form" enctype="multipart/form-data">

                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="number" min="0" name="amount" step="0.01" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Site -->
                        <div class="form-group">
                            <input type="hidden" name="supplier_id" value="{{ $data['supplier']->id }}" />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Select Payee Dropdown --}}
                        <select name="payment_initiator" id="payment_initiator"
                            class="form-select text-black form-select-sm" style="cursor: pointer"
                            onchange="togglePayOptions()">
                            <option value="" selected>Select Payee</option>
                            <option value="1">Supplier</option>
                            <option value="0">Admin</option>
                        </select>

                        {{-- Supplier Options (Shown when Supplier is selected) --}}
                        <div id="supplierOptions" style="display: none;" class="mt-3">
                            <select name="site_id" id="site_id" class="form-select text-black form-select-sm"
                                style="cursor: pointer">
                                <option for="site_id" value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site['site_id'] }}">
                                        {{ $site['site_name'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- File Upload for Screenshot --}}
                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="screenshot">
                            </div>
                        </div>

                        {{-- Admin Options (Shown when Admin is selected) --}}
                        <div id="adminOptions" style="display: none;" class="mt-4">
                            <div class="row g-3">
                                {{-- Sent Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_sent">
                                        <input type="radio" name="transaction_type" id="transaction_sent"
                                            value="1">
                                        Sent
                                    </label>
                                </div>
                                {{-- Received Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_received">
                                        <input type="radio" name="transaction_type" id="transaction_received"
                                            value="0">
                                        Received
                                    </label>
                                </div>
                            </div>
                        </div>


                        {{-- Screenshot --}}


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


    <div id="messageContainer">

    </div>

    <script>
        $(document).ready(function() {
            $('form[id="payment_form"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url($user . '/supplier/payments') }}',
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

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) {

                            messageContainer.append(`
                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                ${response.responseJSON.errors}
                            </div>`)

                            location.reload();

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


        });

        function togglePayOptions() {
            const payTo = document.getElementById('payment_initiator').value;
            const supplierOptions = document.getElementById('supplierOptions');
            const adminOptions = document.getElementById('adminOptions');

            if (payTo === "1") {
                supplierOptions.style.display = 'block';
                adminOptions.style.display = 'none';
            } else if (payTo === "0") {
                supplierOptions.style.display = 'none';
                adminOptions.style.display = 'block';
            } else {

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



