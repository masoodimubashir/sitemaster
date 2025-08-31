<x-app-layout>


    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

    <style>
        .stat-card {
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .verification-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .amount-positive {
            color: #dc3545;
            font-weight: 600;
        }

        .category-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .bg-attendance {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .bg-raw-material {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }

        .bg-square-footage-bill {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .bg-daily-expense {
            background-color: #e8f5e8;
            color: #388e3c;
        }

        .table th {
            font-weight: 600;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .btn-verify {
            background-color: #28a745;
            border: 2px solid #28a745;
            color: #fff;
            padding: 0.375rem 1rem;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .btn-verify:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: #fff;
        }

        .btn-unverify {
            background-color: #fff;
            border: 2px solid #dc3545;
            color: #dc3545;
            padding: 0.375rem 1rem;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .btn-unverify:hover {
            background-color: #dc3545;
            color: #fff;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        #customDateRange {
            display: none;
        }

        #customDateRange.show {
            display: flex;
            gap: 0.5rem;
        }

        .btn-bulk-verify {
            display: none;
        }

        .btn-bulk-verify.show {
            display: inline-block;
        }

        /* Ensure black text in dropdown options */
        .form-select option {
            color: #000 !important;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-2 text-info"></i>
                            Item Verification
                        </h5>
                        <button class="btn btn-success btn-sm btn-bulk-verify" id="bulkVerifyBtn" onclick="bulkVerify()">
                            <i class="fas fa-check-double me-1"></i>Bulk Verify (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalTransactions = $paginatedData->total();
        $verifiedCount = collect($paginatedData->items())->where('verified_by_admin', 1)->count();
        $pendingCount = $totalTransactions - $verifiedCount;
        $totalAmount = collect($paginatedData->items())->sum('debit');
        $verifiedAmount = collect($paginatedData->items())->where('verified_by_admin', 1)->sum('debit');
        $pendingAmount = collect($paginatedData->items())->where('verified_by_admin', 0)->sum('debit');
        $verificationPercentage = $totalTransactions > 0 ? round(($verifiedCount / $totalTransactions) * 100) : 0;
    @endphp

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <!-- Total Transactions -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 fw-normal">Total Items</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalTransactions }}</h3>
                    </div>
                    <div class="ms-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-receipt text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 fw-normal">Verified</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $verifiedCount }}</h3>
                        <small class="text-muted">{{ $verificationPercentage }}% verified</small>
                    </div>
                    <div class="ms-3">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check-circle text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 fw-normal">Total Amount</h6>
                        <h3 class="mb-0 fw-bold">₹{{ number_format($totalAmount) }}</h3>
                    </div>
                    <div class="ms-3">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-chart-line text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 fw-normal">Pending Verification</h6>
                        <h3 class="mb-0 fw-bold text-warning">₹{{ number_format($pendingAmount) }}</h3>
                        <small class="text-muted">{{ $pendingCount }} transactions</small>
                    </div>
                    <div class="ms-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock text-white fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                <button class="btn btn-outline-secondary btn-sm ms-auto" onclick="clearFilters()">
                    Clear Filters
                </button>
            </div>

            <form method="GET" action="" id="filterForm">
                <div class="row g-3">
                    <!-- Site Filter -->
                    <div class="col-md-3">
                        <select class="form-select" name="site_id" onchange="submitForm()">
                            <option value="all">All Sites</option>
                            @if (isset($sites) && count($sites) > 0)
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ ucwords($site->site_name) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Verification Status -->
                    <div class="col-md-3">
                        <select class="form-select" name="verification_status" onchange="submitForm()">
                            <option value="all">All Status</option>
                            <option value="verified"
                                {{ request('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending"
                                {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <!-- Date Period Filter -->
                    <div class="col-md-3">
                        <select class="form-select" name="date_filter" id="dateFilter"
                            onchange="toggleCustomDateRange(); submitForm();">
                            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="lifetime" {{ request('date_filter') === 'lifetime' || !request('date_filter') ? 'selected' : '' }}>All Data</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range Inputs -->
                <div class="row g-3 mt-2" id="customDateRange"
                    {{ request('date_filter') === 'custom' ? 'style=display:flex' : '' }}>
                    <div class="col-md-3">

                        <label class="form-label small text-muted">From Date</label>
                        <input type="date" class="form-control" name="start_date"
                            value="{{ request('start_date') }}" onchange="submitForm()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">To Date</label>
                        <input type="date" class="form-control" name="end_date"
                            value="{{ request('end_date') }}" onchange="submitForm()">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Info -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">
            Showing {{ $paginatedData->firstItem() ?? 0 }} to {{ $paginatedData->lastItem() ?? 0 }} of
            {{ $paginatedData->total() }} transactions
        </p>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                @if (count($paginatedData) > 0)
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Site</th>
                                <th>Amount</th>
                                <th>Created</th>
                                @if($user === 'admin')
                                    <th class="pe-4">Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($paginatedData as $data)
                                <tr class="{{ $data['verified_by_admin'] == 0 ? 'table-warning bg-opacity-10' : '' }}">
                                    <td>
                                        <span class="fw-semibold">{{ $data['id'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $categoryClass = 'bg-attendance';
                                            switch ($data['category']) {
                                                case 'Raw Material':
                                                    $categoryClass = 'bg-raw-material';
                                                    break;
                                                case 'Square Footage Bill':
                                                    $categoryClass = 'bg-square-footage-bill';
                                                    break;
                                                case 'Daily Expense':
                                                    $categoryClass = 'bg-daily-expense';
                                                    break;
                                            }
                                        @endphp
                                        <span class="badge {{ $categoryClass }} category-badge">
                                            {{ $data['category'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $data['description'] }}</div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $data['site'] ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="amount-positive">₹{{ number_format($data['debit']) }}</span>
                                            @if (isset($data['total_amount_with_service_charge']))
                                                <div><small class="text-muted">Total:
                                                        ₹{{ number_format($data['total_amount_with_service_charge']) }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            {{ \Carbon\Carbon::parse($data['created_at'])->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="pe-4">
                                        @if($user === 'admin')
                                            @if ($data['verified_by_admin'] == 1)
                                                <button class="btn btn-unverify btn-sm unverify-btn"
                                                        data-id="{{ $data['id'] }}"
                                                        data-category="{{ $data['category'] }}"
                                                        onclick="toggleVerification({{ $data['id'] }}, '{{ $data['category'] }}', 0)">
                                                    <i class="fas fa-times me-1"></i>Unverify
                                                </button>
                                            @else
                                                <button class="btn btn-verify btn-sm verify-btn"
                                                        data-id="{{ $data['id'] }}"
                                                        data-category="{{ $data['category'] }}"
                                                        onclick="toggleVerification({{ $data['id'] }}, '{{ $data['category'] }}', 1)">
                                                    <i class="fas fa-check me-1"></i>Verify
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-light text-center py-5">
                        <div class="py-4">
                            <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No Items Found</h4>
                            <p class="text-muted">Try adjusting your filters to see more results.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if ($paginatedData->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Page {{ $paginatedData->currentPage() }} of {{ $paginatedData->lastPage() }}
                ({{ $paginatedData->total() }} total results)
            </div>

            <nav aria-label="Table pagination">
                {{ $paginatedData->appends(request()->query())->links() }}
            </nav>
        </div>
    @endif

    @push('scripts')
        <!-- SweetAlert2 -->
        <script>
            // CSRF Token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Reusable SweetAlert2 toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            const showToast = (message, type = 'info') => {
                const icon = type === 'danger' ? 'error' : type; // map bootstrap-ish to swal
                Toast.fire({ icon, title: message });
            };

            // Submit form function
            function submitForm() {
                setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 100);
            }

            // Toggle custom date range
            function toggleCustomDateRange() {
                const dateFilter = document.getElementById('dateFilter').value;
                const customDateRange = document.getElementById('customDateRange');

                if (dateFilter === 'custom') {
                    customDateRange.style.display = 'flex';
                    customDateRange.classList.add('show');
                } else {
                    customDateRange.style.display = 'none';
                    customDateRange.classList.remove('show');
                }
            }

            // Clear filters
            function clearFilters() {
                const form = document.getElementById('filterForm');
                form.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
                form.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
                form.submit();
            }

            // Toggle verification status with SweetAlert2 confirmation
            function toggleVerification(id, category, verified) {
                const action = verified ? 'verify' : 'unverify';
                const button = document.querySelector(`[data-id="${id}"][data-category="${category}"]`);
                const originalContent = button ? button.innerHTML : '';

                Swal.fire({
                    title: `Confirm ${action}`,
                    text: `Are you sure you want to ${action} this ${category} item?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: action === 'verify' ? 'Yes, verify' : 'Yes, unverify',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    if (button) {
                        button.disabled = true;
                        button.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${action === 'verify' ? 'Verifying' : 'Unverifying'}...`;
                        button.classList.add('loading');
                    }

                    const categoryUrlMap = {
                        'Daily Expense': 'expenses',
                        'Raw Material': 'materials',
                        'Square Footage Bill': 'square-footage',
                        'Attendance': 'attendance'
                    };

                    const endpoint = categoryUrlMap[category];
                    if (!endpoint) {
                        showToast('Invalid category', 'error');
                        if (button) resetButton(button, originalContent);
                        return;
                    }

                    $.ajax({
                        url: `{{ url('admin/verify') }}/${endpoint}/${id}`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', verified },
                        success: function(response) {
                            showToast(response.message || `Item ${action}ed successfully!`, 'success');
                            setTimeout(() => window.location.reload(), 800);
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            let message = 'An error occurred. Please try again.';
                            if (xhr.responseJSON) message = xhr.responseJSON.message || message;
                            else if (xhr.status === 404) message = 'Item not found.';
                            else if (xhr.status === 500) message = 'Server error occurred.';
                            showToast(message, 'error');
                            if (button) resetButton(button, originalContent);
                        }
                    });
                });
            }

            // Reset button to original state
            function resetButton(button, originalContent) {
                button.disabled = false;
                button.innerHTML = originalContent;
                button.classList.remove('loading');
            }

            // Bulk verify function (placeholder)
            function bulkVerify() {
                showToast('Bulk verify functionality not implemented yet.', 'info');
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleCustomDateRange();
            });
        </script>
    @endpush
</x-app-layout>
