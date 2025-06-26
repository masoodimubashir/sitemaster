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
        <h2 class="text-xl font-semibold">Ledger Report</h2>
        <div class="ms-auto action-buttons d-flex gap-2">
            <!-- Dropdown Menu -->
            <form action="{{ url($user . '/ledger/report') }}" method="GET">
                <input type="hidden" name="site_id" value="{{ request('site_id', 'all') }}">
                <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                <input type="hidden" name="phase_id" value="{{ request('phase_id', 'all') }}">
                <button type="submit" class="btn btn-outline">
                    <i class="far fa-file-pdf"></i> Download PDF
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
                        {{ $phase->phase_name }} - {{ $phase->site->site_name }}
                    </option>
                @endforeach
            @endif
        </select>

        <!-- Site Select -->
        <select class="bg-white text-black form-select form-select-sm" name="site_id" id="siteFilter">
            <option value="all" {{ request('site_id') == 'all' ? 'selected' : '' }}>All Sites</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                    {{ $site->site_name }}
                </option>
            @endforeach
        </select>

        <!-- Supplier Select -->
        <select class="bg-white text-black form-select form-select-sm" name="supplier_id" id="supplierFilter">
            <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>All Suppliers</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
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
        </select>

        <!-- Date Range Inputs -->
        <div id="customDateRange"
            style="display: {{ request('date_filter') === 'custom' ? 'flex' : 'none' }}; gap: 10px;">
            <input type="date" name="start_date" class="form-control form-control-sm"
                value="{{ request('start_date') }}" placeholder="Start Date">
            <input type="date" name="end_date" class="form-control form-control-sm"
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




        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="fw-bold">DATE</th>
                        <th class="fw-bold">Customer Name</th>
                        <th class="fw-bold">DETAILS</th>
                        <th class="fw-bold text-end">Debit</th>
                        <th class="fw-bold text-end">Credit</th>
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
                                <td class="text-end text-danger fw-bold">
                                    @if ($ledger['debit'] > 0)
                                        ₹{{ number_format($ledger['debit']) }}
                                    @else
                                        ₹0
                                    @endif
                                </td>
                                <td class="text-end text-success fw-bold">
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
                            <td colspan="5" class="text-center py-4 text-muted">No records available</td>
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




    {{-- ------------------------------------------  All The Scripts For This Page Are Below ---------------------------------------- --}}

    <script>
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
