<x-app-layout>



    @php

        // User role detection
        $user = match (auth()->user()->role_name) {
            'admin' => 'admin',
            'site_engineer' => 'user',
            default => 'client',
        };

    @endphp

    <!-- Alert Container -->
    <div id="ajaxAlertContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; width: 300px;">
    </div>

    <x-breadcrumb :names="['Attendance']" :urls="[$user . '/wager-attendance']" />

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-end align-items-center mb-4">

            <div>
                @if ($user === 'admin' || $user === 'user')
                    <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal"
                        data-bs-target="#modal-create-wasta">
                        <i class="fas fa-plus me-1"></i> Add Wasta
                    </button>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                        <i class="fas fa-plus me-1"></i> Add Labour
                    </button>
                @endif
            </div>
        </div>





        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="attendanceFilterForm" method="GET" class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">Select Site</label>
                        <select name="site_id" class="form-select bg-white text-black" onchange="this.form.submit()">
                            <option value="">All Sites</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->site_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter Type -->
                    <div class="col-md-3">
                        <label class="form-label">Select Date</label>
                        <select name="date_filter" class="form-select text-black" onchange="this.form.submit()">
                            <option value="month" {{ $dateFilter === 'month' ? 'selected' : '' }}>Month</option>
                            <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range
                            </option>
                        </select>
                    </div>

                    <!-- Month Selector -->
                    <div class="col-md-3" id="monthSelector"
                        style="{{ $dateFilter !== 'month' ? 'display: none;' : '' }}">
                        <label class="form-label">Month</label>
                        <input type="month" name="monthYear" class="form-control" value="{{ $monthYear }}"
                            onchange="this.form.submit()">
                    </div>

                    <!-- Custom Date Range -->
                    <div class="col-md-6 text-black" id="customDateRange"
                        style="{{ $dateFilter !== 'custom' ? 'display: none;' : '' }}">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="custom_start" class="form-control"
                                    value="{{ $customStart }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="custom_end" class="form-control" value="{{ $customEnd }}"
                                    onchange="this.form.submit()">
                            </div>
                        </div>
                    </div>

                    <!-- Phase Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Phase</label>
                        <select name="phase_id" class="form-select text-black" onchange="this.form.submit()">
                            <option value="">All Phases</option>
                            @foreach ($phases as $phase)
                                <option value="{{ $phase->id }}"
                                    {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                    {{ $phase->phase_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Export Buttons -->
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="btn-group">
                            <button type="submit" formaction="{{ url($user . '/attendance/pdf') }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="far fa-file-pdf me-1"></i> PDF
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-users text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Total Wastas</h6>
                                <h3 class="mb-0">{{ $wastas->count() }}</h3>
                                <small class="text-muted">₹{{ number_format($siteTotal['wasta_amount'], 2) }}
                                    Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-hard-hat text-info fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Total Labours</h6>
                                <h3 class="mb-0">{{ $totalLabours }}</h3>
                                <small class="text-muted">₹{{ number_format($siteTotal['labour_amount'], 2) }}
                                    Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-wallet text-success fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Total Amount</h6>
                                <h3 class="mb-0">₹{{ number_format($siteTotal['combined_total'], 2) }}</h3>
                                <small class="text-muted">{{ $dateRange }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="attendanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="summary-tab" data-bs-toggle="tab"
                    data-bs-target="#summary-tab-pane">
                    Summary View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-tab-pane">
                    Calendar View
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Summary Tab -->
            <div class="tab-pane fade show active" id="summary-tab-pane">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Name</th>
                                        <th>Type</th>
                                        <th>Phase</th>
                                        <th>Present Days</th>
                                        <th>Rate/Day</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wastas as $wasta)
                                        <tr>
                                            <td>
                                                <strong>{{ $wasta->wasta_name }}</strong>
                                                <div class="small text-muted">{{ $wasta->contact_no }}</div>
                                            </td>
                                            <td><span class="badge bg-primary">Wasta</span></td>
                                            <td>{{ $wasta->phase->phase_name ?? '—' }}</td>
                                            <td>{{ $wasta->present_days }}/{{ $totalDays }}</td>
                                            <td>₹{{ number_format($wasta->price, 2) }}</td>
                                            <td>₹{{ number_format($wasta->total_amount, 2) }}</td>
                                        </tr>
                                        @foreach ($wasta->labours as $labour)
                                            <tr>
                                                <td class="ps-4">
                                                    {{ $labour->labour_name }}
                                                    <div class="small text-muted">{{ $labour->contact }}</div>
                                                </td>
                                                <td><span class="badge bg-info">Labour</span></td>
                                                <td>—</td>
                                                <td>{{ $labour->present_days }}/{{ $totalDays }}</td>
                                                <td>₹{{ number_format($labour->price, 2) }}</td>
                                                <td>₹{{ number_format($labour->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td colspan="5" class="text-end fw-bold">Subtotal</td>
                                            <td class="fw-bold">
                                                ₹{{ number_format($wasta->total_amount + $wasta->labours->sum('total_amount'), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Tab -->
            <div class="tab-pane fade" id="calendar-tab-pane">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="sticky-start bg-white" style="min-width: 250px; z-index: 3;">
                                            Wasta/Labour
                                        </th>
                                        @php
                                            $currentDay = $startDate->copy();
                                        @endphp
                                        @while ($currentDay <= $endDate)
                                            @php
                                                $isWeekend = $currentDay->isWeekend();
                                                $isToday = $currentDay->isToday();
                                                $classes = [];
                                                if ($isToday) {
                                                    $classes[] = 'today-column';
                                                }
                                                if ($isWeekend) {
                                                    $classes[] = 'weekend-column';
                                                }
                                            @endphp
                                            <th class="{{ implode(' ', $classes) }} text-center"
                                                style="width: 36px;">
                                                <div class="d-flex flex-column align-items-center">
                                                    <small class="fw-normal" style="font-size: 0.7rem;">
                                                        {{ $currentDay->format('D')[0] }}
                                                    </small>
                                                    <span style="font-size: 0.8rem;">{{ $currentDay->day }}</span>
                                                </div>
                                            </th>
                                            @php
                                                $currentDay->addDay();
                                            @endphp
                                        @endwhile
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wastas as $index => $wasta)
                                        <!-- Wasta Row -->
                                        <tr class="employee-row" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $index }}" style="cursor: pointer;">
                                            <td class="sticky-start ps-3 bg-white" style="min-width: 250px;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $wasta->wasta_name }}</strong>
                                                        <div class="small text-muted">
                                                            {{ $wasta->labours->count() }} labours
                                                        </div>
                                                        <div class="small text-muted">
                                                            @if ($wasta->phase)
                                                                {{ $wasta->phase->phase_name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if ($user === 'admin' || $user === 'user')
                                                        <button class="btn btn-xs btn-outline-success wasta-edit-btn"
                                                            data-wasta='@json($wasta)'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            @php
                                                $currentDay = $startDate->copy();
                                            @endphp
                                            @while ($currentDay <= $endDate)
                                                @php
                                                    $dateFormatted = $currentDay->format('Y-m-d');
                                                    $attendance = $wasta->attendances->firstWhere(
                                                        'attendance_date',
                                                        $dateFormatted,
                                                    );
                                                    $isToday = $currentDay->isToday();
                                                    $isPast = $currentDay->lt(now()->startOfDay());
                                                @endphp
                                                <td
                                                    class="{{ $currentDay->isWeekend() ? 'weekend-column' : '' }} {{ $isToday ? 'today-column' : '' }} text-center">
                                                    @if ($isToday)
                                                        @if ($user === 'admin' || $user === 'user')
                                                            <input type="checkbox"
                                                                class="form-check-input wasta-attendance-checkbox"
                                                                data-attendance-id="{{ $attendance->id ?? null }}"
                                                                data-wasta-id="{{ $wasta->id }}"
                                                                data-date="{{ $dateFormatted }}"
                                                                {{ $attendance && $attendance->is_present ? 'checked' : '' }}>
                                                        @endif
                                                    @elseif ($attendance && $attendance->is_present)
                                                        <i class="fas fa-check text-success"></i>
                                                    @elseif ($isPast)
                                                        <i class="fas fa-times text-danger"></i>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @php
                                                    $currentDay->addDay();
                                                @endphp
                                            @endwhile
                                        </tr>

                                        <!-- Labour Rows -->
                                        @foreach ($wasta->labours as $labour)
                                            <tr class="collapse" id="collapse-{{ $index }}">
                                                <td class="sticky-start ps-4 bg-white" style="min-width: 250px;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $labour->labour_name }}</strong>
                                                            <div class="small text-muted">{{ $labour->position }}
                                                            </div>
                                                            <div class="small text-muted">₹{{ $labour->price }}/day
                                                            </div>
                                                        </div>
                                                        @if ($user === 'admin' || $user === 'user')
                                                            <button
                                                                class="btn btn-xs btn-outline-success labour-edit-btn"
                                                                data-labour='@json($labour)'>
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                                @php
                                                    $currentDay = $startDate->copy();
                                                @endphp
                                                @while ($currentDay <= $endDate)
                                                    @php
                                                        $dateFormatted = $currentDay->format('Y-m-d');
                                                        $attendance = $labour->attendances->firstWhere(
                                                            'attendance_date',
                                                            $dateFormatted,
                                                        );
                                                        $isToday = $currentDay->isToday();
                                                        $isPast = $currentDay->lt(now()->startOfDay());
                                                    @endphp
                                                    <td
                                                        class="{{ $currentDay->isWeekend() ? 'weekend-column' : '' }} {{ $isToday ? 'today-column' : '' }} text-center">
                                                        @if ($isToday)
                                                            @if ($user === 'admin' || $user === 'user')
                                                                <input type="checkbox"
                                                                    class="form-check-input labour-attendance-checkbox"
                                                                    data-labour-id="{{ $labour->id }}"
                                                                    data-date="{{ $dateFormatted }}"
                                                                    data-attendance-id="{{ $attendance->id ?? null }}"
                                                                    {{ $attendance && $attendance->is_present ? 'checked' : '' }}>
                                                            @endif
                                                        @elseif ($attendance && $attendance->is_present)
                                                            <i class="fas fa-check text-success"></i>
                                                        @elseif ($isPast)
                                                            <i class="fas fa-times text-danger"></i>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    @php
                                                        $currentDay->addDay();
                                                    @endphp
                                                @endwhile
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Pagination -->
        @if ($wastas->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Showing {{ $wastas->firstItem() }} to {{ $wastas->lastItem() }} of {{ $wastas->total() }}
                    entries
                </div>
                <nav aria-label="Page navigation">
                    {{ $wastas->withQueryString()->links() }}
                </nav>
            </div>
        @endif
    </div>













    <!-- Create Wasta Modal -->
    <div id="modal-create-wasta" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="createWastaForm">

                        <div class="form-group">
                            <label class="control-label">Date</label>
                            <div class="form-control-plaintext">{{ date('F j, Y') }}</div>
                        </div>

                        <div class="form-group">
                            <input id="create_site_id" name="site_id" value="{{ $site->id }}" type="hidden" />
                            <p class="text-danger" id="create_site_id-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="create_wager_name" class="control-label">Wager Name</label>
                            <input id="create_wager_name" name="wager_name" type="text" class="form-control" />
                            <p class="text-danger" id="create_wager_name-error"></p>
                        </div>

                        <div class="form-group">
                            <select name="phase_id" id="phase_id" class="form-select form-select-md text-black"
                                style="cursor: pointer">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="create_phase_id-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="price_per_day" class="control-label">Price Per Day</label>
                            <input id="price_per_day" name="price_per_day" type="number" class="form-control" />
                            <p class="text-danger" id="price_per_day-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="create_contact_no" class="control-label">Contact No</label>
                            <input id="create_contact_no" name="contact_no" type="text" class="form-control" />
                            <p class="text-danger" id="create_contact-error"></p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-sm btn-success">Create Wager</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Labour Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="labourForm" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="control-label">Date</label>
                            <div class="form-control-plaintext">{{ date('F j, Y') }}</div>
                        </div>

                        <div class="row">
                            <!-- Wasta -->
                            <div class=" col-md-6">

                                <select name="wasta_id" class="form-select form-select-md text-black"
                                    style="cursor: pointer">
                                    <option value="">Select Wasta</option>
                                    @foreach ($wastas as $wasta)
                                        <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-danger" id="wasta_id-error"></p>
                            </div>

                            <div class=" col-md-6">
                                <select name="phase_id" id="labour_phase_id"
                                    class="form-select form-select-md text-black" style="cursor: pointer">
                                    <option value="">Select Phase</option>
                                    @foreach ($phases as $phase)
                                        <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-danger" id="phase_id-error"></p>
                            </div>
                        </div>

                        <input name="site_id" id="labour_site_id" class="form-control" value="{{ $site->id }}"
                            type="hidden" />

                        <!-- Labour Name -->
                        <div class="form-group">
                            <label for="labour_name" class="control-label">Labour Name</label>
                            <input type="text" name="labour_name" class="form-control" />
                            <p class="text-danger" id="labour_name-error"></p>
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <label for="price" class="control-label">Price</label>
                            <input type="text" name="price" id="price" class="form-control" />
                            <p class="text-danger" id="price-error"></p>
                        </div>

                        <!-- Contact -->
                        <div class="form-group">
                            <label for="contact" class="control-label">Contact No</label>
                            <input type="text" name="contact" id="contact" class="form-control" />
                            <p class="text-danger" id="contact-error"></p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-sm btn-success">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Wasta Modal -->
    <div class="modal fade" id="modal-edit-wasta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Wasta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWastaForm">

                        <div class="form-group">
                            <label class="control-label">Date</label>
                            <div class="form-control-plaintext">{{ date('F j, Y') }}</div>
                        </div>

                        <input type="hidden" id="edit_wasta_id" name="id">
                        <div class="mb-3">
                            <label for="edit_wasta_name" class="form-label">Wasta Name</label>
                            <input type="text" class="form-control" id="edit_wasta_name" name="wasta_name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Labour Modal -->
    <div class="modal fade" id="modal-edit-labour" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Labour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLabourForm">

                        <div class="form-group">
                            <label class="control-label">Date</label>
                            <div class="form-control-plaintext">{{ date('F j, Y') }}</div>
                        </div>

                        <input type="hidden" id="edit_labour_id" name="id">
                        <div class="mb-3">
                            <label for="edit_labour_name" class="form-label">Labour Name</label>
                            <input type="text" class="form-control" id="edit_labour_name" name="labour_name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">Save changes</button>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Fields Script -->
    <div id="messageContainer"></div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                // Pass PHP variable to JavaScript
                const userRole = '{{ $user }}';

                // Set CSRF token for all AJAX requests
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Auto-dismiss alerts after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);

                // Alert Function
                function showAlert(type, message) {
                    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

                    const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas ${icon} me-2"></i>
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;

                    $('#ajaxAlertContainer').append(alertHtml);

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 5000);
                }

                // Attendance Checkbox Handlers
                $('.wasta-attendance-checkbox').change(function() {
                    const $this = $(this);
                    $.ajax({
                        url: `/${userRole}/attendance/wasta`,
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
                            attendance_date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                            attendance_id: $this.data('attendance-id'),
                        },
                        success: function() {
                            showAlert('success', 'Attendance updated successfully');
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            $this.prop('checked', !$this.is(':checked')); // Revert checkbox state
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                $('.labour-attendance-checkbox').change(function() {
                    const $this = $(this);
                    $.ajax({
                        url: `/${userRole}/attendance/labour`,
                        type: 'PUT',
                        data: {
                            labour_id: $this.data('labour-id'),
                            attendance_date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                            attendance_id: $this.data('attendance-id'),
                        },
                        success: function() {
                            showAlert('success', 'Attendance updated successfully');
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            $this.prop('checked', !$this.is(':checked')); // Revert checkbox state
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                // Utility: Show form errors
                function showErrors(errors, prefix = '') {
                    $('.text-danger').text('');
                    for (let key in errors) {
                        $(`#${prefix}${key}-error`).text(errors[key][0]);
                    }
                }

                // CREATE WASTA
                $('#createWastaForm').submit(function(e) {
                    e.preventDefault();
                    const data = {
                        wager_name: $('#create_wager_name').val(),
                        price_per_day: $('#price_per_day').val(),
                        contact: $('#create_contact_no').val(),
                        site_id: $('#create_site_id').val(),
                        phase_id: $('#phase_id').val(),
                    };

                    $.post(`/${userRole}/wasta`, data)
                        .done(response => {
                            showAlert('success', response.message || 'Wasta created successfully');
                            $('#modal-create-wasta').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        })
                        .fail(xhr => {
                            if (xhr.status === 422) {
                                showErrors(xhr.responseJSON.errors, 'create_');
                            } else {
                                showAlert('error', xhr.responseJSON?.message || 'Failed to create wasta');
                            }
                        });
                });

                // EDIT WASTA
                $(document).on('click', 'button.wasta-edit-btn', function() {
                    const wasta = JSON.parse($(this).attr('data-wasta'));
                    $('#edit_wasta_id').val(wasta.id);
                    $('#edit_wasta_name').val(wasta.wasta_name);
                    $('#modal-edit-wasta').modal('show');
                });

                $('#editWastaForm').submit(function(e) {
                    e.preventDefault();
                    const id = $('#edit_wasta_id').val();
                    const data = {
                        wasta_name: $('#edit_wasta_name').val()
                    };

                    $.ajax({
                        url: `/${userRole}/attendance/wasta/update/${id}`,
                        type: 'PUT',
                        data,
                        success: () => {
                            showAlert('success', 'Wasta updated successfully');
                            $('#modal-edit-wasta').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: (xhr) => {
                            showAlert('error', xhr.responseJSON?.message ||
                                'Failed to update wasta');
                        }
                    });
                });

                // CREATE LABOUR
                $('#labourForm').submit(function(e) {
                    e.preventDefault();
                    const formData = {
                        wasta_id: $('select[name="wasta_id"]').val(),
                        site_id: $('#labour_site_id').val(),
                        labour_name: $('input[name="labour_name"]').val(),
                        price: $('#price').val(),
                        contact: $('#contact').val(),
                        is_present: $('#is_present').is(':checked') ? 1 : 0,
                        phase_id: $('#labour_phase_id').val()
                    };

                    $.post(`/${userRole}/labour/store`, formData)
                        .done(response => {
                            showAlert('success', response.message || 'Labour created successfully');
                            $('#attendanceModal').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        })
                        .fail(xhr => {
                            if (xhr.status === 422) {
                                showErrors(xhr.responseJSON.errors);
                            } else {
                                showAlert('error', xhr.responseJSON?.message || 'Failed to create labour');
                            }
                        });
                });

                // EDIT LABOUR
                $(document).on('click', 'button.labour-edit-btn', function() {
                    const labour = JSON.parse($(this).attr('data-labour'));
                    $('#edit_labour_id').val(labour.id);
                    $('#edit_labour_name').val(labour.labour_name);
                    $('#modal-edit-labour').modal('show');
                });

                $('#editLabourForm').submit(function(e) {
                    e.preventDefault();
                    const id = $('#edit_labour_id').val();
                    const data = {
                        labour_name: $('#edit_labour_name').val()
                    };

                    $.ajax({
                        url: `/${userRole}/attendance/labour/update/${id}`,
                        type: 'PUT',
                        data,
                        success: () => {
                            showAlert('success', 'Labour updated successfully');
                            $('#modal-edit-labour').modal('hide');
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: (xhr) => {
                            showAlert('error', xhr.responseJSON?.message ||
                                'Failed to update labour');
                        }
                    });
                });

                // Month/Year filter change handler
                document.getElementById('monthYearFilter').addEventListener('change', function() {
                    document.getElementById('attendanceFilterForm').submit();
                });
            });
        </script>

        <script>
            function resetSiteFilters() {
                // Reset the form and submit
                document.getElementById('siteAttendanceFilterForm').reset();
                document.getElementById('siteAttendanceFilterForm').submit();
            }
        </script>
    @endpush



</x-app-layout>
