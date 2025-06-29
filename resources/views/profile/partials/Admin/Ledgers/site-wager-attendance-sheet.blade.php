<x-app-layout>

    @php
        $month = now()->month;
        $year = now()->year;
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
    @endphp

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    @php
        $currentDate = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp

    <style>
        :root {
            --primary: #4361ee;
            --light: #f8f9fa;
            --border-radius: 0.5rem;
        }

        .attendance-card,
        .attendance-dashboard {
            border-radius: var(--border-radius);
            border: none;
            overflow: hidden;
            font-size: 0.85rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            max-height: 80vh;
        }

        th,
        td {
            white-space: nowrap;
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
            font-size: 0.9rem;
        }

        th:first-child,
        td:first-child {
            position: sticky;
            left: 0;
            background-color: #fff;
            z-index: 1;
            min-width: 200px;
        }

        th:first-child {
            background-color: #f8f9fa;
            z-index: 2;
        }

        .present-marker {
            color: green;
            font-weight: bold;
        }

        .absent-indicator {
            color: red;
            font-weight: bold;
        }

        .attendance-checkbox,
        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin: 0 auto;
            transform: scale(0.9);
        }

        .employee-name {
            font-weight: 600;
        }

        .collapse-inner td:first-child {
            padding-left: 2rem;
        }

        .employee-row:hover {
            background-color: #f1f7ff;
        }

        .weekend-column {
            background-color: #f0f0f0;
        }

        .today-column {
            background-color: #e7f0ff !important;
            position: relative;
        }

        .today-column::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--bs-success);
        }

        .sticky-start {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: inherit;
        }
    </style>

    <x-breadcrumb :names="['Sites', $site->site_name, ' Back']" :urls="[
        $user . '/sites',
        $user . '/sites/' . base64_encode($site->id),
        $user . '/sites/' . base64_encode($site->id),
    ]" />


    <div id="ajaxAlertContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; width: 300px;"></div>



    <div class="attendance-dashboard border-0">


        <!-- Card Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 p-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-calendar-check text-info"></i>
                <h5 class="mb-0">Attendance Summary</h5>
                <span class="badge bg-light text-dark ms-2">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </span>
            </div>
            <!-- Compact Filters with Auto-Submit -->
            <form method="GET" action="{{ url($user . '/attendance/site/show/' . $site->id) }}"
                class="d-flex flex-wrap gap-2" id="attendanceFilterForm">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="month" name="monthYear" class="form-control form-control-sm"
                        value="{{ request('monthYear', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}"
                        id="monthYearFilter">
                </div>

            </form>


        </div>

        <!-- Summary Stats -->
        <div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card  border-0 h-100">
                        <div class="card-body py-2">
                            <h6 class="text-muted mb-1">Total Wastas</h6>
                            <h4 class="mb-0">{{ $wastas->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card  border-0 h-100">
                        <div class="card-body py-2">
                            <h6 class="text-muted mb-1">Total Labours</h6>
                            <h4 class="mb-0">{{ $wastas->sum(function ($wasta) {return $wasta->labours->count();}) }}
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card  border-0 h-100">
                        <div class="card-body py-2">
                            <h6 class="text-muted mb-1">Present Today</h6>
                            <h4 class="mb-0">
                                @php
                                    $today = now()->format('Y-m-d');
                                    $presentCount = 0;
                                    foreach ($wastas as $wasta) {
                                        $presentCount += $wasta->attendances
                                            ->where('attendance_date', $today)
                                            ->where('is_present', true)
                                            ->count();
                                        foreach ($wasta->labours as $labour) {
                                            $presentCount += $labour->attendances
                                                ->where('attendance_date', $today)
                                                ->where('is_present', true)
                                                ->count();
                                        }
                                    }
                                    echo $presentCount;
                                @endphp
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-0 h-100">
                        <div class="card-body py-2">
                            <h6 class="text-muted mb-3">Actions</h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#modal-create-wasta">
                                    <i class="fas fa-plus"></i> Wasta
                                </button>
                                <button class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#attendanceModal">
                                    <i class="fas fa-plus"></i> Labour
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Calendar View -->
        <div class="card-body pt-3">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="sticky-start bg-white" style="min-width: 180px; z-index: 3;">Days</th>
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $day);
                                    $isWeekend = $date->isWeekend();
                                    $isToday = $date->isToday();
                                    $classes = [];
                                    if ($isToday) {
                                        $classes[] = 'today-column';
                                    }
                                    if ($isWeekend) {
                                        $classes[] = 'weekend-column';
                                    }
                                    if ($day > 20) {
                                        $classes[] = 'text-truncate';
                                    }
                                @endphp
                                <th class="{{ implode(' ', $classes) }}" style="min-width: 30px; max-width: 30px;">
                                    <div class="d-flex flex-column align-items-center">
                                        <small class="fw-normal mb-2">{{ $date->format('D')[0] }}</small>
                                        <span>{{ $day }}</span>
                                    </div>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wastas as $index => $wasta)
                            <!-- Wasta Row -->
                            <tr class="" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}"
                                style="cursor: pointer;">
                                <td class="sticky-start ps-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $wasta->wasta_name }}</strong>
                                            <div class="small text-muted">{{ $wasta->labours->count() }} labours</div>
                                        </div>
                                        @if ($user === 'admin')
                                            <button class="btn btn-xs btn-outline-success wasta-edit-btn"
                                                data-wasta='@json($wasta)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = \Carbon\Carbon::create($year, $month, $day);
                                        $dateFormatted = $date->format('Y-m-d');
                                        $attendance = $wasta->attendances->firstWhere(
                                            'attendance_date',
                                            $dateFormatted,
                                        );
                                        $isToday = $date->isToday();
                                        $isPast = $date->lt(\Carbon\Carbon::today());
                                    @endphp
                                    <td
                                        class="{{ $date->isWeekend() ? 'weekend-column' : '' }} {{ $isToday ? 'today-column' : '' }}">
                                        @if ($attendance && $attendance->is_present)
                                            <i class="fas fa-check text-success"></i>
                                        @elseif ($isToday)
                                            <input type="checkbox" class="form-check-input wasta-attendance-checkbox"
                                                data-wasta-id="{{ $wasta->id }}" data-date="{{ $dateFormatted }}">
                                        @elseif ($isPast)
                                            <i class="fas fa-times text-danger"></i>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>

                            <!-- Labour Rows (Collapsed) -->
                            @foreach ($wasta->labours as $labour)
                                <tr class="collapse" id="collapse-{{ $index }}">
                                    <td class="sticky-start ps-4 bg-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $labour->labour_name }}</strong>
                                                <div class="small text-muted">{{ $labour->position }}</div>
                                                @if ($labour->site)
                                                    <span
                                                        class="badge bg-info bg-opacity-10 text-info">{{ $labour->site->site_name }}</span>
                                                @endif
                                            </div>
                                            @if ($user === 'admin')
                                                <button class="btn btn-xs btn-outline-success labour-edit-btn"
                                                    data-labour='@json($labour)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $date = \Carbon\Carbon::create($year, $month, $day);
                                            $dateFormatted = $date->format('Y-m-d');
                                            $attendance = $labour->attendances->firstWhere(
                                                'attendance_date',
                                                $dateFormatted,
                                            );
                                            $isToday = $date->isToday();
                                            $isPast = $date->lt(\Carbon\Carbon::today());
                                        @endphp
                                        <td
                                            class="{{ $date->isWeekend() ? 'weekend-column' : '' }} {{ $isToday ? 'today-column' : '' }}">
                                            @if ($attendance && $attendance->is_present)
                                                <i class="fas fa-check text-success"></i>
                                            @elseif ($isToday)
                                                <input type="checkbox"
                                                    class="form-check-input labour-attendance-checkbox"
                                                    data-labour-id="{{ $labour->id }}"
                                                    data-date="{{ $dateFormatted }}">
                                            @elseif ($isPast)
                                                <i class="fas fa-times text-danger"></i>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination and Date Navigation -->
        <div class="card-footer bg-white d-flex justify-content-between align-items-center p-3">
            <small class="text-muted">
                Showing {{ $wastas->count() }} wastas with
                {{ $wastas->sum(function ($wasta) {return $wasta->labours->count();}) }} labours
            </small>
            <div class="btn-group">
                <a href="?monthYear={{ $prevMonth->format('Y-m') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>

                <a href="?monthYear={{ $nextMonth->format('Y-m') }}" class="btn btn-sm btn-outline-secondary">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>



    <!-- Create Wasta Modal -->
    <div id="modal-create-wasta" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="createWastaForm">
                        <div class="form-group">
                            <input id="create_site_id" name="site_id" value="{{ $site->id }}" type="hidden" />
                            <p class="text-danger" id="create_site_id-error"></p>
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
                            <label for="create_wager_name" class="control-label">Wager Name</label>
                            <input id="create_wager_name" name="wager_name" type="text" class="form-control" />
                            <p class="text-danger" id="create_wager_name-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="create_price_per_day" class="control-label">Price Per Day</label>
                            <input id="create_price_per_day" name="price_per_day" type="number"
                                class="form-control" />
                            <p class="text-danger" id="create_price_per_day-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="create_contact_no" class="control-label">Contact No</label>
                            <input id="create_contact_no" name="contact_no" type="text" class="form-control" />
                            <p class="text-danger" id="create_contact-error"></p>
                        </div>

                        <button class="btn btn-success">Create Wager</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Labour Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="labourForm" method="POST">
                        @csrf
                        <!-- Wasta -->
                        <div class="form-group mt-2">

                            <select name="wasta_id" class="form-select form-select-md text-black"
                                style="cursor: pointer">
                                <option value="">Select Wasta</option>
                                @foreach ($wastas as $wasta)
                                    <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="wasta_id-error"></p>
                        </div>

                        <div class="form-group">

                            <select name="phase_id" id="labour_phase_id"
                                class="form-select form-select-md text-black" style="cursor: pointer">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="phase_id-error"></p>
                        </div>
                        <!-- Site -->
                        <div class="form-group">
                            <input name="site_id" id="labour_site_id" class="form-control"
                                value="{{ $site->id }}" type="hidden" />
                            <p class="text-danger" id="site_id-error"></p>
                        </div>

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

                        <button class="btn btn-success">Save Attendance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Wasta Modal -->
    <div class="modal fade" id="modal-edit-wasta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Wasta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editWastaForm">
                        <input type="hidden" id="edit_wasta_id" name="id">
                        <div class="mb-3">
                            <label for="edit_wasta_name" class="form-label">Wasta Name</label>
                            <input type="text" class="form-control" id="edit_wasta_name" name="wasta_name">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Labour Modal -->
    <div class="modal fade" id="modal-edit-labour" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Labour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLabourForm">
                        <input type="hidden" id="edit_labour_id" name="id">
                        <div class="mb-3">
                            <label for="edit_labour_name" class="form-label">Labour Name</label>
                            <input type="text" class="form-control" id="edit_labour_name" name="labour_name">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">Save changes</button>
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
                        url: '/admin/attendance/wasta',
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker text-success"><i class="fas fa-check"></i></div>'
                            );
                            showAlert('success', 'Attendance updated successfully');
                        },
                        error: function(xhr) {
                            $this.prop('checked', false);
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                $('.labour-attendance-checkbox').change(function() {
                    const $this = $(this);
                    $.ajax({
                        url: '/admin/attendance/labour',
                        type: 'PUT',
                        data: {
                            labour_id: $this.data('labour-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker text-success"><i class="fas fa-check"></i></div>'
                            );
                            showAlert('success', 'Attendance updated successfully');
                        },
                        error: function(xhr) {
                            $this.prop('checked', false);
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
                        price_per_day: $('#create_price_per_day').val(),
                        contact: $('#create_contact_no').val(),
                        site_id: $('#create_site_id').val(),
                        phase_id: $('#phase_id').val(),
                    };

                    $.post('/admin/wasta', data)
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
                        url: `/admin/attendance/wasta/update/${id}`,
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

                    $.post('{{ url('admin/labour/store') }}', formData)
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
                        url: `/admin/attendance/labour/update/${id}`,
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



                document.getElementById('monthYearFilter').addEventListener('change', function() {
                    document.getElementById('attendanceFilterForm').submit();
                });
            });
        </script>
    @endpush


</x-app-layout>
