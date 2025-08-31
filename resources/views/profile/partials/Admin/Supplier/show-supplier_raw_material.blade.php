<x-app-layout>

    @php

        use Carbon\Carbon;

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
        <h2 class="text-xl font-semibold">{{ ucwords($supplier->name) }}</h2>


        <div class="ms-auto action-buttons d-flex gap-2">
            <!-- Dropdown Menu for Quick Access -->
            <button class="btn btn-sm btn-outline" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-bolt me-1"></i> Quick Actions
            </button>

            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <i class="fas fa-hand-holding-usd me-2"></i> Make Payment
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>


                <li>
                    <a class="dropdown-item" href="{{ url($user . '/supplier/payments', [$data['supplier']->id]) }}">
                        <i class="fas fa-money-check-alt me-2"></i> View Payments
                    </a>
                </li>

                @if ($user === 'admin')
                    <li>
                        <a class="dropdown-item"
                            href="{{ url($user . '/supplier-payment/report', ['id' => base64_encode($data['supplier']->id)]) }}">
                            <i class="fas fa-file-invoice me-2"></i> Payment Report
                        </a>
                    </li>
                @endif

                <li>
                    <a class="dropdown-item"
                        href="{{ url($user . '/supplier/detail', ['id' => $data['supplier']->id]) }}">
                        <i class="fas fa-user-circle me-2"></i> View Detailed Profile
                    </a>
                </li>

            </ul>

            <form action="{{ url($user . '/supplier/ledger-pdf') }}" method="GET">
                <!-- Existing filters -->
                <input type="hidden" name="site_id" value="{{ request('site_id', 'all') }}">
                <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                <input type="hidden" name="supplier_id"
                    value="{{ request('supplier_id', $data['supplier']->id ?? 'all') }}">
                <input type="hidden" name="phase_id" value="{{ request('phase_id', 'all') }}">

                <!-- Missing custom date range filters -->
                @if (request('date_filter') === 'custom')
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif

                <!-- Add more hidden inputs for any other query parameters you want to preserve -->
                @foreach (request()->query() as $key => $value)
                    @if (!in_array($key, ['site_id', 'date_filter', 'supplier_id', 'phase_id', 'start_date', 'end_date']))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <button type="submit" class="btn btn-outline">
                    <i class="far fa-file-pdf"></i> PDF
                </button>
            </form>

        </div>

    </div>


    <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url()->current() }}" method="GET"
        id="filterForm">

        <!-- Supplier Site -->
        <select class="bg-white text-black form-select form-select-sm" name="site_id" id="supplierFilter">
            <option value="all" {{ request('site_id') == 'all' ? 'selected' : '' }}>All Sites</option>
            @foreach ($data['sites'] as $site)
                <option value="{{ $site['site_id'] }}" {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
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


            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ number_format($data['totalCredit']) }}</div>
                <div class="summary-label got-text">Total Paid</div>
            </div>


            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ number_format($data['returns']) }}</div>
                <div class="summary-label got-text">Total Returns</div>
            </div>

        </div>


        <div class="card">
            <div class="table-responsive mt-4">
                <table class="table  mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Details</th>
                            <th>Return</th>
                            <th>Purchases</th>
                            <th>Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($data['ledgers']))
                            @foreach ($data['ledgers'] as $key => $ledger)
                                <tr>
                                    <td>{{ Carbon::parse($ledger['created_at'])->format('d M Y') }}</td>
                                    <td>
                                        <strong>{{ ucwords($ledger['supplier']) }}</strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ ucwords($ledger['description']) }}</div>
                                        <small class="text-muted">
                                            {{ ucwords($ledger['phase']) }} / {{ $ledger['category'] }}
                                            / {{ $ledger['site'] }}
                                        </small>
                                    </td>
                                    <td class="text-danger fw-bold">
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
                                    <td class="text-success fw-bold">
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
        </div>
        @if ($data['ledgers']->hasPages())
            <div class="p-3 border-top">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end mb-0">
                        {{-- Previous Page Link --}}
                        @if ($data['ledgers']->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $data['ledgers']->previousPageUrl() }}"
                                    rel="prev">&laquo;</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($data['ledgers']->getUrlRange(1, $data['ledgers']->lastPage()) as $page => $url)
                            @if ($page == $data['ledgers']->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($data['ledgers']->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $data['ledgers']->nextPageUrl() }}"
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


    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">


                    <form id="payment_form" class="forms-sample material-form" enctype="multipart/form-data">

                        @csrf


                        <div class="d-flex align-items-center gap-2 p-2 border-start border-3 border-primary">
                            <i class="bi bi-building text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Supplier</small>
                                <strong>{{ $supplier->name }}</strong>
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="form-group">
                            <input type="date" name="created_at" id="created_at" />
                            <label for="created_at" class="control-label">Date</label>
                            <i class="bar"></i>
                            <p class="mt-1 text-danger" id="created_at-error"></p>
                        </div>

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="number" min="1" name="amount" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Site -->
                        <div class="form-group">
                            <input type="hidden" name="supplier_id" value="{{ $data['supplier']->id }}" />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>


                        @if ($user === 'user')
                            <!-- For user role, payments always go to Admin; hide sites selection -->
                            <input type="hidden" name="payment_initiator" value="1" />
                        @endif

                        @if ($user === 'admin')
                            {{-- Select Payee Dropdown --}}
                            <div class="mb-3">

                                <input type="checkbox" name="payment_initiator" id="payment_initiator"
                                    class="form-check-input" value="1" onchange="togglePayOptions()">
                                <label class="form-check-label" for="payment_initiator">
                                    Pay To Admin
                                </label>
                            </div>

                            <div class="mb-3">

                                <label class="form-check-label" for="payment_initiator">
                                    OR
                                </label>
                            </div>

                            <select name="site_id" id="site_id" class="form-select text-black form-select-sm"
                                style="cursor: pointer">
                                <option for="site_id" value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site['site_id'] }}">
                                        {{ $site['site_name'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Admin Options (Shown when Admin is selected) --}}
                            <div id="adminOptions" style="display: none;" class="mt-2">
                                <div class="row g-3">
                                    {{-- Sent Radio Option --}}
                                    <div class="col-auto">
                                        <label for="transaction_sent">
                                            <input type="radio" name="transaction_type" id="transaction_sent"
                                                value="1">
                                            Return To {{ $supplier->name }}
                                        </label>
                                    </div>
                                    {{-- Received Radio Option --}}
                                    <div class="col-auto">
                                        <label for="transaction_received">
                                            <input type="radio" name="transaction_type" id="transaction_received"
                                                value="0">
                                            Received By Admin
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            {{-- Narration  --}}
                            <label class="control-label mt-3">Narration</label>
                            <textarea id="narration" class="form-control" name="narration"></textarea>
                            <div class="invalid-feedback" id="screenshot-error"></div>
                        </div>

                        {{-- File Upload for Screenshot --}}
                        <div class="mt-3">
                            <input class="form-control form-control-md" id="image" type="file"
                                name="screenshot">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Toggle payment options based on checkbox
                function togglePayOptions() {
                    const payToAdmin = document.getElementById('payment_initiator').checked;
                    const adminOptions = document.getElementById('adminOptions');
                    const siteSelect = document.getElementById('site_id');

                    if (payToAdmin) {
                        adminOptions.style.display = 'block';
                        // Hide site selection when paying to admin
                        if (siteSelect) {
                            $(siteSelect).slideUp(200);
                        }
                        // Add smooth transition
                        $(adminOptions).slideDown(300);
                    } else {
                        // Clear radio selections when hiding
                        $('input[name="transaction_type"]').prop('checked', false);
                        $(adminOptions).slideUp(300);
                        // Show site selection when not paying to admin
                        if (siteSelect) {
                            $(siteSelect).slideDown(200);
                        }
                    }
                }

                // Make function globally accessible
                window.togglePayOptions = togglePayOptions;

                // Form submission with enhanced styling
                $('form[id="payment_form"]').on('submit', function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const formData = new FormData(form[0]);
                    const messageContainer = $('#messageContainer');
                    const submitBtn = $('#submitPaymentBtn');
                    const spinner = submitBtn.find('.spinner-border');
                    const submitText = submitBtn.find('.submit-text');

                    // Clear previous messages and errors
                    messageContainer.empty();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Show loading state
                    submitBtn.prop('disabled', true);
                    spinner.removeClass('d-none');
                    submitText.html('<i class="bi bi-hourglass-split me-2"></i>Processing...');

                    $.ajax({
                        url: '{{ url($user . '/supplier/payments') }}',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            messageContainer.html(`
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                <strong>Success!</strong> ${response.message}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);

                            // Reset form
                            form[0].reset();
                            $('#adminOptions').hide();

                            // Reload page after delay
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        },
                        error: function(xhr) {
                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;

                                // Display field-specific errors only in the current form
                                $.each(errors, function(field, messages) {
                                    const input = form.find(
                                        `[name="${field}"]`
                                    ); // Use form scope instead of global $
                                    const formGroup = input.closest('.form-group, .mb-3');

                                    input.addClass('is-invalid');

                                    // Create error message
                                    const errorMsg =
                                        `<div class="invalid-feedback d-block">${messages.join('<br>')}</div>`;

                                    if (formGroup.length) {
                                        formGroup.append(errorMsg);
                                    } else {
                                        input.after(errorMsg);
                                    }
                                });
                            } else {
                                // Only show general error for non-validation errors
                                messageContainer.html(`
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Error!</strong> An unexpected error occurred. Please try again later.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

                                // Auto-dismiss error after 5 seconds
                                setTimeout(function() {
                                    messageContainer.find('.alert').alert('close');
                                }, 5000);
                            }
                        },

                        complete: function() {
                            // Reset button state
                            submitBtn.prop('disabled', false);
                            spinner.addClass('d-none');
                            submitText.html('<i class="bi bi-credit-card me-2"></i>Pay');
                        }
                    });
                });

                // Clear validation errors on input change
                $('input, select').on('input change', function() {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.invalid-feedback').remove();
                });

                // Initialize tooltips if any
                $('[data-bs-toggle="tooltip"]').tooltip();
            });


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
    @endpush


</x-app-layout>
