<x-app-layout>


    <style>
     

        .attendance-header {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .table-container {
            overflow-x: auto;
            max-height: 70vh;
            overflow-y: auto;
        }

        .today-header .day-name,
        .today-header .day-number {
            color: white !important;
        }

        .weekend-header {
            background: #fef3c7 !important;
        }

        .weekend-header .day-name,
        .weekend-header .day-number {
            color: #92400e !important;
        }


        .wasta-row:hover {
            background: #f1f5f9;
        }

        .labour-row {
            background: #ffffff;
            border-bottom: 1px solid #f3f4f6;
        }

        .labour-row:hover {
            background: #f9fafb;
        }

        /* Worker cell styling */
        .worker-cell {
            padding: 14px 16px;
            position: sticky;
            left: 0;
            background: inherit;
            border-right: 2px solid #e5e7eb;
            z-index: 5;
        }

        .worker-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .worker-details {
            flex: 1;
            min-width: 0;
        }

        .worker-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .worker-meta {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .worker-count {
            font-size: 12px;
            color: #6b7280;
        }

        .phase-badge {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .position-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Attendance cell styling */
        .attendance-cell {
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            min-height: 50px;
        }

        .today-cell {
            background: #eff6ff;
            border-left: 2px solid #3b82f6;
            border-right: 2px solid #3b82f6;
        }

        .weekend-cell {
            background: #fffbeb;
        }

        .attendance-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: inherit;
            padding: 4px;
            border-radius: 4px;
            min-height: 40px;
            transition: transform 0.1s;
        }

        .attendance-link:hover {
            transform: scale(1.02);
            text-decoration: none;
            color: inherit;
        }

        .status-present {
            color: #10b981;
            font-size: 18px;
        }

        .status-absent {
            color: #ef4444;
            font-size: 18px;
        }

        .mark-present-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .mark-present-btn:hover {
            background: #059669;
        }

       

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .attendance-wrapper {
                margin: 10px;
                border-radius: 6px;
            }

            .attendance-header {
                padding: 12px 16px;
            }

            .attendance-title {
                font-size: 16px;
            }

            .worker-cell {
                padding: 12px;
            }

            .attendance-cell {
                padding: 6px 4px;
            }

            .worker-column {
                min-width: 200px !important;
            }
        }
    </style>

    @php

        // User role detection
        $user = match (auth()->user()->role_name) {
            'admin' => 'admin',
            'site_engineer' => 'user',
            default => 'client',
        };

    @endphp

    <!-- Alert Container -->
    <div id="ajaxAlertContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; width: 300px;"></div>

    <!-- Breadcrumb -->
    @if ($user === 'admin' || $user === 'user')
        <x-breadcrumb :names="['Sites', $site->site_name, 'Attendance']" :urls="[
            $user . '/sites',
            $user . '/sites/' . base64_encode($site->id),
            $user . '/sites/' . base64_encode($site->id) . '/attendance',
        ]" />
    @else
        <x-breadcrumb :names="['Dashboard', $site->site_name, 'Attendance']" :urls="[
            $user . '/dashboard',
            $user . '/dashboard/' . base64_encode($site->id),
            $user . '/dashboard/' . base64_encode($site->id) . '/attendance',
        ]" />
    @endif




    @php
        // User role detection
        $user = match (auth()->user()->role_name) {
            'admin' => 'admin',
            'site_engineer' => 'user',
            default => 'client',
        };
    @endphp



    <div class="container-fluid">

        <!-- Header Quick Actions -->
        <div class="d-flex justify-content-end align-items-center mb-4">
            <div>
                @if ($user === 'admin' || $user === 'user')
                    <button class="btn btn-success me-2 btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modal-create-wasta">
                        <i class="fas fa-plus me-1"></i> Add Wasta
                    </button>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                        <i class="fas fa-plus me-1"></i> Add Labour
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Panel Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="attendanceFilterForm" method="GET" class="row g-3">
                    <input type="hidden" name="site_id" value="{{ $site->id }}">

                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Select Date</label>
                        <select name="date_filter" class="form-select text-black" onchange="this.form.submit()">
                            <option value="month" {{ $dateFilter === 'month' ? 'selected' : '' }}>Month</option>
                            <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Custom Range
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3" id="monthSelector"
                        style="{{ $dateFilter !== 'month' ? 'display:none' : '' }}">
                        <label class="form-label">Month</label>
                        <input type="month" name="monthYear" class="form-control" value="{{ $monthYear }}"
                            onchange="this.form.submit()">
                    </div>
                    <div class="col-md-6" id="customDateRange"
                        style="{{ $dateFilter !== 'custom' ? 'display:none' : '' }}">
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

                    <!-- Export PDF Button -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" formaction="{{ url($user . '/attendance/pdf') }}"
                            class="btn btn-outline-secondary btn-sm w-100">
                            <i class="far fa-file-pdf me-1"></i> Export PDF
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-users fa-2x text-primary me-3"></i>
                        <div>
                            <div class="fs-6 text-muted">Total Wastas</div>
                            <div class="fs-3 fw-bold">{{ $wastas->count() }}</div>
                            <div class="small text-muted">₹{{ number_format($siteTotal['wasta_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-hard-hat fa-2x text-info me-3"></i>
                        <div>
                            <div class="fs-6 text-muted">Total Labours</div>
                            <div class="fs-3 fw-bold">{{ $totalLabours }}</div>
                            <div class="small text-muted">₹{{ number_format($siteTotal['labour_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-wallet fa-2x text-success me-3"></i>
                        <div>
                            <div class="fs-6 text-muted">Total Amount</div>
                            <div class="fs-3 fw-bold">₹{{ number_format($siteTotal['combined_total'], 2) }}</div>
                            <div class="small text-muted">{{ $dateRange }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nav Tabs Bootstrap -->
        <ul class="nav nav-tabs mb-3" id="attendanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="summary-tab" data-bs-toggle="tab"
                    data-bs-target="#summary-tab-pane">
                    <i class="fas fa-list me-1"></i> Summary View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-tab-pane">
                    <i class="fas fa-calendar-alt me-1"></i> Calendar View
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- SUMMARY TABLE -->
            <div class="tab-pane fade show active" id="summary-tab-pane">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name & Contact</th>
                                        <th>Type</th>
                                        <th>Phase</th>
                                        <th>Attendance</th>
                                        <th>Avg Rate</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wastas as $wasta)
                                        <tr class="employee-row">
                                            <td>
                                                <div>
                                                    <strong class="d-block">{{ $wasta->wasta_name }}</strong>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>{{ $wasta->contact_no }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-primary">Wasta</span></td>
                                            <td>
                                                @if ($wasta->phase)
                                                    <span
                                                        class="badge bg-secondary">{{ $wasta->phase->phase_name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success"
                                                            style="width: {{ ($wasta->present_days / $totalDays) * 100 }}%">
                                                        </div>
                                                    </div>
                                                    <small
                                                        class="text-muted">{{ $wasta->present_days }}/{{ $totalDays }}</small>
                                                </div>
                                            </td>
                                            <td class="fw-medium">
                                                @if ($wasta->present_days > 0)
                                                    ₹{{ number_format($wasta->total_amount / $wasta->present_days, 2) }}
                                                @else
                                                    ₹0.00
                                                @endif
                                            </td>
                                            <td class="fw-bold text-success">
                                                ₹{{ number_format($wasta->total_amount, 2) }}</td>
                                            <td>
                                                @if ($user === 'admin' || $user === 'user')
                                                    <button class="btn btn-sm btn-outline-primary wasta-edit-btn"
                                                        data-wasta='@json($wasta)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @foreach ($wasta->labours as $labour)
                                            <tr class="labour-row">
                                                <td class="ps-4">
                                                    <div>
                                                        <strong class="d-block">{{ $labour->labour_name }}</strong>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i>{{ $labour->contact }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-info">Labour</span></td>
                                                <td><span class="text-muted">—</span></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                            <div class="progress-bar bg-info"
                                                                style="width: {{ ($labour->present_days / $totalDays) * 100 }}%">
                                                            </div>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ $labour->present_days }}/{{ $totalDays }}</small>
                                                    </div>
                                                </td>
                                                <td class="fw-medium">
                                                    @if ($labour->present_days > 0)
                                                        ₹{{ number_format($labour->total_amount / $labour->present_days, 2) }}
                                                    @else
                                                        ₹0.00
                                                    @endif
                                                </td>
                                                <td class="fw-bold text-info">
                                                    ₹{{ number_format($labour->total_amount, 2) }}</td>
                                                <td>
                                                    @if ($user === 'admin' || $user === 'user')
                                                        <button class="btn btn-sm btn-outline-info labour-edit-btn"
                                                            data-labour='@json($labour)'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Subtotal</td>
                                            <td class="fw-bold text-success">
                                                ₹{{ number_format($wasta->combined_total, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>

            <!-- CALENDAR TABLE -->
            <div class="tab-pane fade" id="calendar-tab-pane">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">


                        <div class="table-container">
                            <table class="attendance-table table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="worker-column bg-white sticky-start">
                                        </th>
                                        @php $curDay = $startDate->copy(); @endphp
                                        @while ($curDay <= $endDate)
                                            <th
                                                class="date-header text-center {{ $curDay->isToday() ? '' : ($curDay->isWeekend() ? 'weekend-header bg-light' : '') }}">
                                                <div class="date-content">
                                                    <div class="day-name">{{ $curDay->format('D') }}</div>
                                                    <div class="day-number">{{ $curDay->day }}</div>
                                                </div>
                                            </th>
                                            @php $curDay->addDay(); @endphp
                                        @endwhile
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wastas as $idx => $wasta)
                                        <tr class="wasta-row fw-bold" data-bs-toggle="collapse"
                                            data-bs-target="#cal-labours{{ $idx }}">
                                            <td class="worker-cell sticky-start bg-white">
                                                <div class="worker-info">
                                                    <div class="worker-icon wasta-icon">
                                                        <i class="fas fa-users"></i>
                                                    </div>
                                                    <div class="worker-details">
                                                        <div class="worker-name">{{ $wasta->wasta_name }}
                                                        </div>
                                                        <div class="worker-meta">
                                                            <span class="worker-count">({{ $wasta->labours->count() }}
                                                                workers)</span>
                                                            <span
                                                                class="phase-badge">{{ $wasta->phase->phase_name ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            @php $curDay = $startDate->copy(); @endphp
                                            @while ($curDay <= $endDate)
                                                @php
                                                    $dateFmt = $curDay->format('Y-m-d');
                                                    $attn = $wasta->attendances->firstWhere(
                                                        'attendance_date',
                                                        $dateFmt,
                                                    );
                                                    $isToday = $curDay->isToday();
                                                    $isPast = $curDay->lt(now()->startOfDay());
                                                @endphp
                                                <td
                                                    class="attendance-cell text-center align-middle {{ $isToday ? '' : ($curDay->isWeekend() ? 'weekend-header' : '') }}">
                                                    <a href="javascript:void(0)"
                                                        class="attendance-link open-attendance-modal"
                                                        data-entity="wasta"
                                                        data-attendance-id="{{ $attn->id ?? '' }}"
                                                        data-worker-id="{{ $wasta->id }}"
                                                        data-date="{{ $dateFmt }}"
                                                        data-is-today="{{ $isToday }}"
                                                        data-price="{{ $attn->price ?? '' }}"
                                                        data-is-present="{{ $attn->is_present ?? 0 }}">
                                                        @if ($attn && $attn->is_present)
                                                            <i class="fas fa-check-circle status-present"></i>
                                                            @if ($attn->price)
                                                                <div class="price-text">₹{{ $attn->price }}</div>
                                                            @endif
                                                        @elseif ($isPast)
                                                            <i class="fas fa-times-circle status-absent"></i>
                                                        @else
                                                            <i class="fas fa-pen-to-square"></i>
                                                        @endif
                                                    </a>
                                                </td>
                                                @php $curDay->addDay(); @endphp
                                            @endwhile
                                        </tr>
                                        @foreach ($wasta->labours as $labour)
                                            <tr class="labour-row collapse" id="cal-labours{{ $idx }}">
                                                <td class="worker-cell sticky-start bg-white ps-4">
                                                    <div class="worker-info">
                                                        <div class="worker-icon labour-icon">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="worker-details">
                                                            <div class="worker-name">
                                                                {{ $labour->labour_name }}</div>
                                                            @if ($labour->position)
                                                                <div class="worker-meta">
                                                                    <span
                                                                        class="position-badge">{{ $labour->position }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                @php $curDay = $startDate->copy(); @endphp
                                                @while ($curDay <= $endDate)
                                                    @php
                                                        $dateFmt = $curDay->format('Y-m-d');
                                                        $attn = $labour->attendances->firstWhere(
                                                            'attendance_date',
                                                            $dateFmt,
                                                        );
                                                        $isToday = $curDay->isToday();
                                                        $isPast = $curDay->lt(now()->startOfDay());
                                                    @endphp
                                                    <td
                                                        class="attendance-cell text-center align-middle {{ $isToday ? '' : ($curDay->isWeekend() ? 'weekend-header' : '') }}">
                                                        <a href="javascript:void(0)"
                                                            class="attendance-link open-attendance-modal"
                                                            data-entity="labour"
                                                            data-attendance-id="{{ $attn->id ?? '' }}"
                                                            data-worker-id="{{ $labour->id }}"
                                                            data-date="{{ $dateFmt }}"
                                                            data-is-today="{{ $isToday }}"
                                                            data-price="{{ $attn->price ?? '' }}"
                                                            data-is-present="{{ $attn->is_present ?? 0 }}">
                                                            @if ($attn && $attn->is_present)
                                                                <i class="fas fa-check-circle status-present"></i>
                                                                @if ($attn->price)
                                                                    <div class="price-text"> ₹{{ $attn->price }}</div>
                                                                @endif
                                                            @elseif ($isPast)
                                                                <i class="fas fa-times-circle status-absent"></i>
                                                            @else
                                                            <i class="fas fa-pen-to-square"></i>
                                                            @endif
                                                        </a>
                                                    </td>
                                                    @php $curDay->addDay(); @endphp
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

        @if ($wastas->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Showing {{ $wastas->firstItem() }} to {{ $wastas->lastItem() }} of {{ $wastas->total() }} entries
                </div>
                <nav aria-label="Page navigation">
                    {{ $wastas->withQueryString()->links() }}
                </nav>
            </div>
        @endif
    </div>



    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceEditModal" tabindex="-1" aria-labelledby="attendanceEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="attendanceEditModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendanceEditForm">
                        @csrf
                        <input type="hidden" id="modalAttendanceId" name="attendance_id">
                        <input type="hidden" id="modalWorkerType" name="worker_type"> <!-- wasta/labour -->
                        <input type="hidden" id="modalWorkerId" name="worker_id">
                        <input type="hidden" id="modalDate" name="date">
                        <div class="mb-3">
                            <input class="form-check-input" type="checkbox" id="modalIsPresent" name="is_present">
                            <label class="form-check-label" for="modalIsPresent">
                                Present
                            </label>
                        </div>
                        <div class="mb-3">
                            <label for="modalPrice" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="modalPrice" name="price"
                                placeholder="Enter price">
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="saveAttendanceBtn">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>









    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceEditModal" tabindex="-1" aria-labelledby="attendanceEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceEditModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendanceEditForm">
                        <input type="hidden" id="modalAttendanceId" name="attendance_id">
                        <input type="hidden" id="modalWorkerType" name="worker_type"> <!-- 'wasta' or 'labour' -->
                        <input type="hidden" id="modalWorkerId" name="worker_id">
                        <input type="hidden" id="modalDate" name="date">

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modalIsPresent"
                                    name="is_present">
                                <label class="form-check-label" for="modalIsPresent">
                                    Present
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalPrice" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="modalPrice" name="price"
                                placeholder="Enter price">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveAttendanceBtn">Save changes</button>
                </div>
            </div>
        </div>
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
                const userRole = '{{ $user }}';

                // Handle Bootstrap 5 tab switch (fix vertical bounce bug in native BS)
                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    $($(e.relatedTarget).attr('href')).removeClass('show active');
                    $($(e.target).attr('href')).addClass('show active');
                });

                // Show modal on calendar cell click
                $(document).on("click", ".open-attendance-modal", function() {
                    let entity = $(this).data("entity"),
                        attnId = $(this).data("attendance-id") || "",
                        workerId = $(this).data("worker-id"),
                        date = $(this).data("date"),
                        present = +$(this).data("is-present"),
                        price = $(this).data("price") || "",
                        isToday = $(this).data("is-today");

                    // Set modal fields
                    $("#modalAttendanceId").val(attnId);
                    $("#modalWorkerType").val(entity);
                    $("#modalWorkerId").val(workerId);
                    $("#modalDate").val(date);
                    $("#modalIsPresent").prop("checked", !!present);
                    $("#modalPrice").val(price);
                    $("#modalPrice").prop("disabled", !present);

                    // Allow edit only for today
                    $("#modalIsPresent,#modalPrice,#saveAttendanceBtn").prop("disabled", !isToday);

                    $("#attendanceEditModal").modal("show");
                });

                // enable/disable price input with checkbox
                $("#modalIsPresent").on("change", function() {
                    $("#modalPrice").prop("disabled", !$(this).is(":checked"));
                });

                // Save attendance from modal (AJAX)
                $("#saveAttendanceBtn").on("click", function() {
                    let entity = $("#modalWorkerType").val(),
                        attnId = $("#modalAttendanceId").val(),
                        workerId = $("#modalWorkerId").val(),
                        date = $("#modalDate").val(),
                        isPresent = $("#modalIsPresent").is(":checked") ? 1 : 0,
                        price = $("#modalPrice").val(),
                        url = "/" + userRole + "/attendance/" + entity;

                    let payload = {
                        attendance_id: attnId ? attnId : undefined,
                        attendance_date: date,
                        is_present: isPresent,
                        daily_price: price || null,
                    };
                    if (entity === "wasta") payload.wasta_id = workerId;
                    else payload.labour_id = workerId;

                    $.ajax({
                        url: url,
                        type: 'PUT',
                        data: payload,
                        success: function(response) {
                            $("#attendanceEditModal").modal("hide");
                            showAlert("success", "Attendance updated successfully!");
                            setTimeout(() => location.reload(),
                                700); // Or update table cell dynamically
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message ||
                                "Failed to update attendance";
                            showAlert("danger", errorMsg);
                        }
                    });
                });

                // Show/hide filter range selectors
                $('select[name="date_filter"]').on('change', function() {
                    if ($(this).val() === 'custom') {
                        $('#customDateRange').show();
                        $('#monthSelector').hide();
                    } else {
                        $('#customDateRange').hide();
                        $('#monthSelector').show();
                    }
                });

                // Alert utility
                window.showAlert = function(type, message) {
                    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas ${icon} me-2"></i><div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                    $('#ajaxAlertContainer').append(alertHtml);
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 5000);
                };
            });
        </script>
    @endpush


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

                // UPDATED: Wasta Attendance Checkbox with Price Support
                $('.wasta-attendance-checkbox').change(function() {
                    const $this = $(this);
                    const $priceInput = $this.closest('.attendance-cell').find('.daily-price-input');
                    const dailyPrice = $priceInput.val() || null;

                    $.ajax({
                        url: `/${userRole}/attendance/wasta`,
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
                            attendance_date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                            attendance_id: $this.data('attendance-id'),
                            daily_price: dailyPrice // Add daily price
                        },
                        success: function(response) {
                            showAlert('success', 'Attendance updated successfully');

                            // Update attendance ID if newly created
                            if (response.attendance_id && !$this.data('attendance-id')) {
                                $this.attr('data-attendance-id', response.attendance_id);
                            }

                            // Optional: Update price input with confirmed value
                            if (response.daily_price && $priceInput.length) {
                                $priceInput.val(response.daily_price);
                            }

                            // Remove the auto-reload, let user continue working
                            // setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            $this.prop('checked', !$this.is(':checked')); // Revert checkbox state
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                // UPDATED: Labour Attendance Checkbox with Price Support
                $('.labour-attendance-checkbox').change(function() {
                    const $this = $(this);
                    const $priceInput = $this.closest('.attendance-cell').find('.daily-price-input');
                    const dailyPrice = $priceInput.val() || null;

                    $.ajax({
                        url: `/${userRole}/attendance/labour`,
                        type: 'PUT',
                        data: {
                            labour_id: $this.data('labour-id'),
                            attendance_date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                            attendance_id: $this.data('attendance-id'),
                            daily_price: dailyPrice // Add daily price
                        },
                        success: function(response) {
                            showAlert('success', 'Attendance updated successfully');

                            // Update attendance ID if newly created
                            if (response.attendance_id && !$this.data('attendance-id')) {
                                $this.attr('data-attendance-id', response.attendance_id);
                            }

                            // Optional: Update price input with confirmed value
                            if (response.daily_price && $priceInput.length) {
                                $priceInput.val(response.daily_price);
                            }

                            // Remove the auto-reload, let user continue working
                            // setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            $this.prop('checked', !$this.is(':checked')); // Revert checkbox state
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                // NEW: Handle price input changes (when user changes price without toggling attendance)
                $(document).on('change blur', '.daily-price-input', function() {
                    const $this = $(this);
                    const $checkbox = $this.closest('.attendance-cell').find('input[type="checkbox"]');
                    const wastaId = $this.data('wasta-id');
                    const labourId = $this.data('labour-id');
                    const date = $this.data('date');
                    const dailyPrice = $this.val();
                    const isPresent = $checkbox.is(':checked');
                    const attendanceId = $checkbox.data('attendance-id');

                    // Only update if attendance is marked present or if there's an existing attendance record
                    if (isPresent || attendanceId) {
                        const url = wastaId ? `/${userRole}/attendance/wasta` :
                            `/${userRole}/attendance/labour`;
                        const data = {
                            attendance_date: date,
                            is_present: isPresent ? 1 : 0,
                            daily_price: dailyPrice,
                            attendance_id: attendanceId
                        };

                        if (wastaId) {
                            data.wasta_id = wastaId;
                        } else {
                            data.labour_id = labourId;
                        }

                        $.ajax({
                            url: url,
                            type: 'PUT',
                            data: data,
                            success: function(response) {
                                showAlert('success', 'Price updated successfully');

                                // Update attendance ID if newly created
                                if (response.attendance_id && !attendanceId) {
                                    $checkbox.attr('data-attendance-id', response.attendance_id);
                                }
                            },
                            error: function(xhr) {
                                const errorMsg = xhr.responseJSON?.message ||
                                    'Failed to update price';
                                showAlert('error', errorMsg);
                            }
                        });
                    }
                });

                // NEW: Quick Price Set for Multiple Days (Optional Enhancement)
                $(document).on('click', '.quick-price-btn', function() {
                    const entityType = $(this).data('entity-type'); // 'wasta' or 'labour'
                    const entityId = $(this).data('entity-id');
                    const entityName = $(this).data('entity-name');

                    // Show modal or prompt for bulk price setting
                    const newPrice = prompt(`Set price for all days for ${entityName}:`);
                    if (newPrice && !isNaN(newPrice)) {
                        $(`.daily-price-input[data-${entityType}-id="${entityId}"]`).val(newPrice);
                        showAlert('success', `Price set to ₹${newPrice} for all days`);
                    }
                });

                // Utility: Show form errors
                function showErrors(errors, prefix = '') {
                    $('.text-danger').text('');
                    for (let key in errors) {
                        $(`#${prefix}${key}-error`).text(errors[key][0]);
                    }
                }

                // CREATE WASTA (No changes needed, but can add default price)
                $('#createWastaForm').submit(function(e) {
                    e.preventDefault();
                    const data = {
                        wager_name: $('#create_wager_name').val(),
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

                // CREATE LABOUR (No changes needed, but can add default price)
                $('#labourForm').submit(function(e) {
                    e.preventDefault();
                    const formData = {
                        wasta_id: $('select[name="wasta_id"]').val(),
                        site_id: $('#labour_site_id').val(),
                        labour_name: $('input[name="labour_name"]').val(),
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
