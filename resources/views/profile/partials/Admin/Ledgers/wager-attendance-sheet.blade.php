<x-app-layout>


    @php

        // User role detection
        $user = match (auth()->user()->role_name) {
            'admin' => 'admin',
            'site_engineer' => 'user',
            default => 'client',
        };

        // Get current query parameters for form actions
        $queryParams = request()->except(['page']);

    @endphp

    <style>
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .summary-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.2s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .display-4 {
            font-size: 2rem;
            font-weight: 700;
        }

        .btn-group-actions {
            gap: 10px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.6rem 0.75rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }

        .section-header {
            margin-bottom: 1.5rem;
        }

        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .filter-row {
            margin-bottom: 1rem;
        }

        .export-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .action-section {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>


    <x-breadcrumb :names="['Sites', $site->site_name]" :urls="[$user . '/sites', $user . '/sites/' . base64_encode($site->id)]" />


    <div class="py-4">
        <!-- Toast Container -->
        <div id="toastContainer" class="toast-container"></div>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-dark fw-bold mb-1">
                    <i class="fas fa-users me-2 text-success"></i>
                    Attendance Management
                </h2>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="section-header">
                <h5 class="section-title">
                    <i class="fas fa-filter me-2"></i>
                    Filter Options
                </h5>
            </div>

            <form id="attendanceFilterForm" method="GET"
                action="{{ url($user . '/attendance/site/show/' . $site->id) }}">
                <input type="hidden" name="site_id" value="{{ $site->id }}">

                <!-- Date Range Row -->
                <div class="row filter-row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="start_date" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Start Date
                        </label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="end_date" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>
                            End Date
                        </label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="month_filter" class="form-label">
                            <i class="fas fa-calendar-month me-1"></i>
                            Filter by Month
                        </label>
                        <input type="month" class="form-control" id="month_filter" name="month_filter"
                            value="{{ request('month_filter') }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="worker_type" class="form-label">
                            <i class="fas fa-user-tag me-1"></i>
                            Worker Type
                        </label>
                        <select class="form-select text-black" id="worker_type" name="worker_type">
                            <option value="all" {{ request('worker_type') == 'all' ? 'selected' : '' }}>All Workers
                            </option>
                            <option value="contractors" {{ request('worker_type') == 'contractors' ? 'selected' : '' }}>
                                Contractors Only</option>
                            <option value="workers" {{ request('worker_type') == 'workers' ? 'selected' : '' }}>Workers
                                Only</option>
                            <option value="independents"
                                {{ request('worker_type') == 'independents' ? 'selected' : '' }}>Independents Only
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Second Filter Row -->
                <div class="row filter-row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="attendance_status" class="form-label">
                            <i class="fas fa-check-circle me-1"></i>
                            Attendance Status
                        </label>
                        <select class="form-select text-black" id="attendance_status" name="attendance_status">
                            <option value="all" {{ request('attendance_status') == 'all' ? 'selected' : '' }}>All
                                Records</option>
                            <option value="present" {{ request('attendance_status') == 'present' ? 'selected' : '' }}>
                                Present </option>
                            <option value="absent" {{ request('attendance_status') == 'absent' ? 'selected' : '' }}>
                                Absent </option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="wasta_id" class="form-label">
                            <i class="fas fa-building me-1"></i>
                            Filter by Wasta
                        </label>
                        <select class="form-select text-black" id="wasta_id" name="wasta_id">
                            <option value="">All Wastas</option>
                            @foreach ($wastas as $wasta)
                                <option value="{{ $wasta->id }}"
                                    {{ request('wasta_id') == $wasta->id ? 'selected' : '' }}>
                                    {{ $wasta->wasta_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="wager_id" class="form-label">
                            <i class="fas fa-hard-hat me-1"></i>
                            Filter by Labour
                        </label>
                        <select class="form-select text-black" id="wager_id" name="wager_id">
                            <option value="">All Workers</option>
                            @foreach ($wagers as $wager)
                                <option value="{{ $wager->id }}"
                                    {{ request('wager_id') == $wager->id ? 'selected' : '' }}>
                                    {{ $wager->wager_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12 mb-3">
                        <label for="search" class="form-label">
                            <i class="fas fa-search me-1"></i>
                            Search Name
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search"
                                placeholder="Search name..." value="{{ request('search') }}">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <div class="btn-group-actions d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-filter me-1"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ url($user . '/attendance/site/show/' . $site->id) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-sync me-1"></i>
                                    Reset Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Export and Actions Section -->
        <div class="d-flex justify-content-end align-items-center gap-3">

            <form action="{{ url($user . '/attendance/pdf') }}" method="GET" target="_blank">
                <input type="hidden" name="site_id" value="{{ $site->id }}">
                <input type="hidden" name="start_date"
                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                <input type="hidden" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                <input type="hidden" name="worker_type" value="{{ request('worker_type') }}">
                <input type="hidden" name="wasta_id" value="{{ request('wasta_id') }}">
                <input type="hidden" name="wager_id" value="{{ request('wager_id') }}">
                <input type="hidden" name="attendance_status" value="{{ request('attendance_status') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="month_filter" value="{{ request('month_filter') }}">
                <div class="d-flex align-items-center justify-content-between">
                    <button type="submit" class="btn btn-light">
                        <i class="fas fa-file-pdf me-1"></i>
                        Generate PDF
                    </button>
                </div>
            </form>

            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                <i class="fas fa-plus me-1"></i>
                Add Attendance
            </button>

        </div>

    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card summary-card   h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1 text-start">Total Workers</h6>
                            <div class="display-4 text-start">{{ $totalWorkers + $totalContractors }}</div>
                            <small class="text-white-75 text-start">{{ $totalContractors }} Wastas +
                                {{ $totalWorkers }} workers</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card summary-card h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1 text-start">Total Days</h6>
                            <div class="display-4 text-start">{{ $grandTotalDays }}</div>
                            <small class="text-white-75 text-start">Present days in period</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card summary-card  h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1 text-start">Total Amount</h6>
                            <div class="display-4 text-start">{{ $grandTotalAmount }}</div>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-indian-rupee"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card summary-card  h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title mb-1 text-start">Date Range</h6>
                            <div class="h5 text-start mb-1">{{ $startDate->format('M d, Y') }}
                                to<br>{{ $endDate->format('M d, Y') }}</div>
                            <small class="text-dark-75 text-start">{{ $totalDays }} days</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Attendance Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover attendance-table">
            <thead class="sticky-header">
                <tr>
                    <th rowspan="2" style="width: 200px;">Name</th>
                    <th rowspan="2" style="width: 80px;">Rate/Day</th>
                    @foreach ($dateArray as $date)
                        <th class="date-column" title="{{ $date->format('D, M d, Y') }}">
                            {{ $date->format('d') }}
                        </th>
                    @endforeach
                    <th rowspan="2" style="width: 60px;">Total Days</th>
                    <th rowspan="2" style="width: 100px;">Total Amount</th>
                </tr>
                <tr>
                    @foreach ($dateArray as $date)
                        <th class="date-column" title="{{ $date->format('D, M d, Y') }}">
                            {{ $date->format('M') }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedData as $row)
                    <tr class="{{ $row['is_contractor'] ? 'contractor-row' : 'worker-row' }}">
                        <td>
                            {{ $row['name'] }}
                        </td>
                        <td class="text-right">{{ number_format($row['rate'], 2) }}</td>

                        @foreach ($row['daily'] as $isPresent)
                            <td class="text-center date-column">
                                <span class="fw-bold {{ $isPresent ? 'text-success' : 'text-danger' }}">
                                    {{ $isPresent ? 'P' : 'A' }}
                                </span>
                            </td>
                        @endforeach

                        <td class="text-center font-weight-bold">{{ $row['days'] }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($row['amount'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + $totalDays }}" class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x mb-2 text-muted"></i>
                            <p class="text-muted">No attendance records found for the selected filters.</p>
                        </td>
                    </tr>
                @endforelse

                <!-- Grand Total Row -->
                @if (count($paginatedData) > 0)
                    <tr class="bg-dark text-white">
                        <td colspan="2" class="text-center font-weight-bold">GRAND TOTAL</td>
                        @foreach ($dateArray as $date)
                            <td class="text-center">-</td>
                        @endforeach
                        <td class="text-center font-weight-bold">{{ $grandTotalDays }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($grandTotalAmount, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($paginatedData->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing {{ $paginatedData->firstItem() }} to {{ $paginatedData->lastItem() }} of
                {{ $paginatedData->total() }} results
            </div>

            <nav aria-label="Attendance pagination">
                <ul class="pagination mb-0">
                    <!-- First Page Link -->
                    <li class="page-item {{ $paginatedData->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $paginatedData->url(1) }}" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>

                    <!-- Previous Page Link -->
                    <li class="page-item {{ $paginatedData->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $paginatedData->previousPageUrl() }}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Pagination Elements -->
                    @foreach ($paginatedData->getUrlRange(1, $paginatedData->lastPage()) as $page => $url)
                        @if ($page == $paginatedData->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link"
                                    href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    <!-- Next Page Link -->
                    <li class="page-item {{ $paginatedData->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $paginatedData->nextPageUrl() }}" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>

                    <!-- Last Page Link -->
                    <li class="page-item {{ $paginatedData->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $paginatedData->url($paginatedData->lastPage()) }}"
                            aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
    </div>

    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form id="attendanceForm" method="POST" action="{{ url($user . '/attendance-setup') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="attendanceModalLabel">Add Attendance</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <!-- Worker Type Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="worker_type" class="form-label">Worker Type</label>
                                <select name="worker_type" id="worker_type_select" class="form-select">
                                    <option value="wasta" selected>Contractor</option>
                                    <option value="wager">Single Labor</option>
                                    <option value="multiple">Multiple Labors</option>
                                </select>
                                <div id="worker_type_error" class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <!-- Common Fields -->
                                <label for="attendance_date" class="form-label">Date</label>
                                <input type="date" name="attendance_date" id="attendance_date"
                                    class="form-control" value="{{ date('Y-m-d') }}">
                                <div id="attendance_date_error" class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Contractor (Wasta) Fields -->
                        <div id="wastaFields" class="mb-3">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="wasta_name" class="form-label">Contractor Name</label>
                                    <input list="wastaList" name="wasta_name" id="wasta_name" class="form-control"
                                        autocomplete="off">
                                    <datalist id="wastaList">
                                        @foreach ($wastas as $wasta)
                                            <option value="{{ $wasta->wasta_name }}">
                                        @endforeach
                                    </datalist>
                                    <div id="wasta_name_error" class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="wasta_price" class="form-label">Rate (₹)</label>
                                    <input type="number" name="wasta_price" id="wasta_price" class="form-control"
                                        min="0" value="0">
                                    <div id="wasta_price_error" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Single Labor (Wager) Fields -->
                        <div id="wagerFields" class="mb-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="wager_name" class="form-label">Labor Name</label>
                                    <input list="wagerList" name="wager_name" id="wager_name" class="form-control"
                                        autocomplete="off">
                                    <datalist id="wagerList">
                                        @foreach ($wagers as $wager)
                                            <option value="{{ $wager->wager_name }}">
                                        @endforeach
                                    </datalist>
                                    <div id="wager_name_error" class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="wager_price" class="form-label">Rate (₹)</label>
                                    <input type="number" name="wager_price" id="wager_price" class="form-control"
                                        min="0" value="0">
                                    <div id="wager_price_error" class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="wager_count" class="form-label">Count</label>
                                    <input type="number" name="wager_count" id="wager_count" class="form-control"
                                        min="1" value="1">
                                    <div id="wager_count_error" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="wager_wasta_id" class="form-label">Assign to Contractor (Optional)</label>
                                <select name="wager_wasta_id" id="wager_wasta_id" class="form-select">
                                    <option value="">-- Select Contractor --</option>
                                    @foreach ($wastas as $wasta)
                                        <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Multiple Labors Fields -->
                        <div id="multipleFields" class="mb-3" style="display: none;">
                            <div class="mb-3">
                                <label for="multiple_wasta_id" class="form-label">Assign to Contractor
                                    (Optional)</label>
                                <select name="multiple_wasta_id" id="multiple_wasta_id" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach ($wastas as $wasta)
                                        <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="laborContainer" class="mb-3">
                                <div class="section-header mb-2">
                                    <i class="fas fa-users me-2"></i>Labors List
                                </div>

                                <div class="labor-row mb-2">
                                    <div class="flex-grow-1 me-2">
                                        <input type="text" name="multiple_names[]" class="form-control"
                                            placeholder="Labor Name" autocomplete="off" list="wagerList" />
                                        <div class="invalid-feedback multiple_names_error"></div>
                                    </div>
                                    <div class="me-2" style="width: 100px;">
                                        <input type="number" name="multiple_prices[]" class="form-control"
                                            placeholder="Rate" min="0" value="0" />
                                        <div class="invalid-feedback multiple_prices_error"></div>
                                    </div>
                                    <div class="me-2" style="width: 80px;">
                                        <input type="number" name="multiple_counts[]" class="form-control"
                                            placeholder="Count" min="1" value="1" />
                                        <div class="invalid-feedback multiple_counts_error"></div>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm remove-labor"
                                        disabled>&times;</button>
                                </div>
                            </div>

                            <button type="button" id="addLaborRow" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Add Labor
                            </button>
                        </div>

                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitAttendanceBtn" class="btn btn-primary">
                            <span class="submit-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <form id="editAttendanceForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="attendance_id" id="edit_attendance_id">
                        <input type="hidden" name="site_id" value="{{ $site->id }}">

                        <div class="mb-3">
                            <label for="edit_attendance_date" class="form-label">Date</label>
                            <input type="date" name="attendance_date" id="edit_attendance_date"
                                class="form-control">
                            <div id="edit_attendance_date_error" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_worker_name" class="form-label">Worker Name</label>
                            <input type="text" name="worker_name" id="edit_worker_name" class="form-control">
                            <div id="edit_worker_name_error" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                            </select>
                            <div id="edit_status_error" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Rate (₹)</label>
                            <input type="number" name="price" id="edit_price" class="form-control"
                                min="0">
                            <div id="edit_price_error" class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_count" class="form-label">Count</label>
                            <input type="number" name="count" id="edit_count" class="form-control"
                                min="1" value="1">
                            <div id="edit_count_error" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap and jQuery -->

    @push('scripts')
        <script>
            // Initialize tooltips
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Bootstrap tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Initialize all components
                initFilterForm();
                initAttendanceModal();
                initFormSubmissions();
            });

            // Toast notification function
            function showToast(type, message) {
                const toastContainer = document.getElementById('toastContainer');
                if (!toastContainer) return;

                const toast = document.createElement('div');
                toast.className = `toast show align-items-center text-white bg-${type} border-0`;
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');

                toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

                toastContainer.appendChild(toast);

                // Auto remove after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            }

            // Initialize filter form toggle
            function initFilterForm() {
                const dateFilter = $('#dateFilter');
                const monthField = $('.month-filter-fields');
                const dayField = $('.day-filter-fields');
                const customFields = $('.custom-date-fields');

                function toggleFields() {
                    const filterValue = dateFilter.val();

                    monthField.toggle(filterValue === 'month');
                    dayField.toggle(filterValue === 'day');
                    customFields.toggle(filterValue === 'custom');
                }

                dateFilter.change(toggleFields);
                toggleFields(); // Initialize on page load
            }

            // Initialize attendance modal
            function initAttendanceModal() {
                const workerType = $('#worker_type_select');
                const wastaFields = $('#wastaFields');
                const wagerFields = $('#wagerFields');
                const multipleFields = $('#multipleFields');
                const laborContainer = $('#laborContainer');

                // Show/hide fields based on worker type
                workerType.change(function() {
                    wastaFields.hide();
                    wagerFields.hide();
                    multipleFields.hide();

                    switch ($(this).val()) {
                        case 'wasta':
                            wastaFields.show();
                            break;
                        case 'wager':
                            wagerFields.show();
                            break;
                        case 'multiple':
                            multipleFields.show();
                            break;
                    }
                }).trigger('change');

                // Add labor row
                $('#addLaborRow').click(function() {
                    const newRow = laborContainer.find('.labor-row').first().clone();
                    newRow.find('input').val(function() {
                        return this.name.includes('count') ? '1' : '';
                    });
                    newRow.find('.remove-labor').prop('disabled', false);
                    newRow.find('input').removeClass('is-invalid');
                    newRow.find('.invalid-feedback').text('');
                    laborContainer.append(newRow);
                });

                // Remove labor row
                $(document).on('click', '.remove-labor', function() {
                    if (laborContainer.find('.labor-row').length > 1) {
                        $(this).closest('.labor-row').remove();
                    }
                });

                // Auto-fill contractor rate when selecting from datalist
                $('#wasta_name').on('input', function() {
                    const selectedName = $(this).val();
                    const wastas = {!! json_encode($wastas->pluck('wasta_price', 'wasta_name')) !!};

                    if (wastas[selectedName]) {
                        $('#wasta_price').val(wastas[selectedName]);
                    }
                });

                // Auto-fill labor rate when selecting from datalist
                $('#wager_name').on('input', function() {
                    const selectedName = $(this).val();
                    const wagers = {!! json_encode($wagers->pluck('wager_price', 'wager_name')) !!};

                    if (wagers[selectedName]) {
                        $('#wager_price').val(wagers[selectedName]);
                    }
                });
            }

            // Edit contractor function
            function editWasta(wastaId) {
                // In a real application, you would fetch the contractor data via AJAX
                // For now, we'll just show the modal with some placeholder data
                const modal = $('#editAttendanceModal');
                modal.find('#edit_attendance_id').val(wastaId);
                modal.find('#edit_worker_name').val('Contractor Name');
                modal.find('#edit_attendance_date').val('{{ date('Y-m-d') }}');
                modal.find('#edit_status').val('present');
                modal.find('#edit_price').val(500);
                modal.find('#edit_count').val(1);

                // Set form action URL
                const form = $('#editAttendanceForm');
                form.attr('action', '{{ url($user . '/attendance/') }}/' + wastaId);

                modal.modal('show');
            }

            // Edit labor function
            function editLabor(laborId) {
                // In a real application, you would fetch the labor data via AJAX
                // For now, we'll just show the modal with some placeholder data
                const modal = $('#editAttendanceModal');
                modal.find('#edit_attendance_id').val(laborId);
                modal.find('#edit_worker_name').val('Labor Name');
                modal.find('#edit_attendance_date').val('{{ date('Y-m-d') }}');
                modal.find('#edit_status').val('present');
                modal.find('#edit_price').val(300);
                modal.find('#edit_count').val(1);

                // Set form action URL
                const form = $('#editAttendanceForm');
                form.attr('action', '{{ url($user . '/attendance/') }}/' + laborId);

                modal.modal('show');
            }

            // Delete confirmation
            function confirmDelete() {
                if (confirm('Are you sure you want to delete this attendance record?')) {
                    const id = $('#edit_attendance_id').val();
                    const form = $('#editAttendanceForm');
                    const url = form.attr('action');

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', response.message || 'Attendance deleted successfully');
                            $('#editAttendanceModal').modal('hide');
                            location.reload();
                        },
                        error: function(xhr) {
                            showToast('error', xhr.responseJSON?.message || 'Failed to delete attendance');
                        }
                    });
                }
            }

            // Initialize form submissions
            function initFormSubmissions() {
                // Attendance form submission
                $('#attendanceForm').submit(function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const submitBtn = $('#submitAttendanceBtn');
                    const submitText = submitBtn.find('.submit-text');
                    const spinner = submitBtn.find('.spinner-border');

                    // Show loading state
                    submitBtn.prop('disabled', true);
                    submitText.text('Saving...');
                    spinner.removeClass('d-none');

                    // Clear previous errors
                    $('.invalid-feedback').text('');
                    $('.is-invalid').removeClass('is-invalid');

                    // Prepare form data
                    const formData = new FormData(this);
                    const workerType = $('#worker_type_select').val();

                    // Clean up data based on worker type
                    if (workerType === 'wasta') {
                        formData.delete('wager_name');
                        formData.delete('wager_price');
                        formData.delete('wager_wasta_id');
                        formData.delete('multiple_names[]');
                        formData.delete('multiple_prices[]');
                        formData.delete('multiple_counts[]');
                        formData.delete('multiple_wasta_id');
                    } else if (workerType === 'wager') {
                        formData.delete('wasta_name');
                        formData.delete('wasta_price');
                        formData.delete('multiple_names[]');
                        formData.delete('multiple_prices[]');
                        formData.delete('multiple_counts[]');
                        formData.delete('multiple_wasta_id');
                    } else if (workerType === 'multiple') {
                        formData.delete('wasta_name');
                        formData.delete('wasta_price');
                        formData.delete('wager_name');
                        formData.delete('wager_price');
                        formData.delete('wager_wasta_id');
                    }

                    // AJAX request
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', response.message || 'Attendance saved successfully');
                            $('#attendanceModal').modal('hide');
                            form[0].reset();
                            location.reload();
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to save attendance';

                            // Handle validation errors
                            if (xhr.responseJSON?.errors) {
                                const errors = xhr.responseJSON.errors;
                                Object.keys(errors).forEach(key => {
                                    const errorElement = $(`#${key}_error`);
                                    if (errorElement.length) {
                                        errorElement.text(errors[key][0]);
                                        $(`[name="${key}"]`).addClass('is-invalid');
                                    } else if (key.startsWith('multiple_')) {
                                        // Handle multiple labor errors
                                        const parts = key.split('.');
                                        const field = parts[0];
                                        const index = parts[1];
                                        $(`.labor-row:eq(${index}) .${field}_error`).text(errors[
                                            key][0]);
                                        $(`.labor-row:eq(${index}) [name="${field}[]"]`).addClass(
                                            'is-invalid');
                                    }
                                });
                                errorMessage = 'Please fix the errors in the form';
                            }

                            showToast('error', errorMessage);
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                            submitText.text('Save Attendance');
                            spinner.addClass('d-none');
                        }
                    });
                });

                // Edit attendance form submission
                $('#editAttendanceForm').submit(function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const id = $('#edit_attendance_id').val();
                    const url = form.attr('action');

                    // Clear previous errors
                    $('.invalid-feedback').text('');
                    $('.is-invalid').removeClass('is-invalid');

                    $.ajax({
                        url: url,
                        type: 'POST', // Using POST with _method=PUT
                        data: form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', response.message || 'Attendance updated successfully');
                            $('#editAttendanceModal').modal('hide');
                            location.reload();
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON?.message || 'Failed to update attendance';

                            if (xhr.responseJSON?.errors) {
                                const errors = xhr.responseJSON.errors;
                                Object.keys(errors).forEach(key => {
                                    $(`#edit_${key}_error`).text(errors[key][0]);
                                    $(`#edit_${key}`).addClass('is-invalid');
                                });
                                errorMessage = 'Please fix the errors in the form';
                            }

                            showToast('error', errorMessage);
                        }
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Clear search functionality
                document.getElementById('clearSearch').addEventListener('click', function() {
                    document.getElementById('search').value = '';
                    document.getElementById('attendanceFilterForm').submit();
                });

                // Auto-submit form when certain filters change
                const autoSubmitElements = ['worker_type', 'attendance_status', 'wasta_id', 'wager_id', 'per_page'];
                autoSubmitElements.forEach(function(elementId) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        element.addEventListener('change', function() {
                            document.getElementById('attendanceFilterForm').submit();
                        });
                    }
                });

                // Date validation
                const startDate = document.getElementById('start_date');
                const endDate = document.getElementById('end_date');

                if (startDate && endDate) {
                    startDate.addEventListener('change', function() {
                        if (new Date(endDate.value) < new Date(this.value)) {
                            endDate.value = this.value;
                        }
                    });

                    endDate.addEventListener('change', function() {
                        if (new Date(this.value) < new Date(startDate.value)) {
                            this.value = startDate.value;
                        }
                    });
                }
            });
        </script>
    @endpush

</x-app-layout>
