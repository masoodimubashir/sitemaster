<x-app-layout>

    @php
        $month = now()->month;
        $year = now()->year;
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

        $currentDate = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp

    <!-- Custom Styling -->
    <style>
        :root {
            --primary: #4361ee;
            --light: #f8f9fa;
            --border-radius: 0.5rem;
        }

        .attendance-card {
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
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

        .attendance-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
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
        }
    </style>

    <style>
        :root {
            --primary: #4361ee;
            --light: #f8f9fa;
            --border-radius: 0.5rem;
        }

        .attendance-card {
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
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

        .attendance-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
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
        }
    </style>
<div class="card attendance-card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h2 class="h5 mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-calendar-check text-primary"></i>
            <span>Attendance Report</span>
        </h2>

        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-md-center gap-2 w-100 w-md-auto">
           

            <form method="GET" action="{{ url('admin/wager-attendance') }}" 
      class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1">

    <!-- Site Filter -->
    <select name="site_id" class="form-select text-black">
        <option value="">All Sites</option>
        @foreach ($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                {{ $site->site_name }}
            </option>
        @endforeach
    </select>

    <!-- Month Filter -->
    <input type="month" name="monthYear" class="form-control"
        value="{{ request('monthYear', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}">

    <!-- Submit Button -->
    <div class="d-block d-md-flex">
        <button type="submit" class="btn btn-success w-100 w-md-auto">
            <i class="fas fa-filter d-md-none"></i>
            <span class="d-none d-md-inline">Filter</span>
        </button>
    </div>
</form>


            <!-- Quick Actions Dropdown -->
            <div class="dropdown  w-sm-auto">
                <button class="btn btn-success w-100 w-sm-auto" type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bolt me-1"></i>
                    <span class="d-none d-sm-inline">Quick Actions</span>
                    <span class="d-sm-none">Actions</span>
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#modal-create-wasta">
                            <i class="fas fa-user-plus me-2"></i> Create Wasta
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" role="button" data-bs-toggle="modal" href="#attendanceModal">
                            <i class="fas fa-hard-hat me-2"></i> Create Labour
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mobile View (only shown on small screens) -->
    <div class="d-block d-lg-none">
        <div class="accordion" id="mobileAttendanceAccordion">
            @foreach ($wastas as $index => $wasta)
            <div class="accordion-item border-0 mb-2">
                <div class="accordion-header p-2">
                    <button class="accordion-button collapsed p-2" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#mobileCollapse-{{ $index }}">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="text-truncate me-2">
                                <strong>{{ $wasta->wasta_name }}</strong>
                            </div>
                            @if ($user === 'admin')
                            <button class="btn btn-sm btn-outline-success wasta-edit-btn mt-2"
                                    data-wasta='@json($wasta)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                        </div>
                    </button>
                </div>
                
                <div id="mobileCollapse-{{ $index }}" class="accordion-collapse collapse" 
                     data-bs-parent="#mobileAttendanceAccordion">
                    <div class="accordion-body p-0">
                        @foreach ($wasta->labours as $labour)
                        <div class="border-bottom p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>{{ $labour->labour_name }}</strong>
                                    @if ($labour->position)
                                    <div class="text-muted small">{{ $labour->position }}</div>
                                    @endif
                                    @if ($labour->site)
                                    <div class="text-info small">{{ $labour->site->site_name }}</div>
                                    @endif
                                </div>
                                @if ($user === 'admin')
                                <button class="btn btn-sm btn-outline-success labour-edit-btn"
                                        data-labour='@json($labour)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endif
                            </div>
                            
                            <div class="d-flex overflow-auto py-1" style="max-width: 100vw;">
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $day);
                                    $dateFormatted = $date->format('Y-m-d');
                                    $attendance = $labour->attendances->firstWhere('attendance_date', $dateFormatted);
                                    $isToday = \Carbon\Carbon::today()->format('Y-m-d') === $dateFormatted;
                                    $isPast = $date->lt(\Carbon\Carbon::today());
                                    $isWeekend = $date->isWeekend();
                                @endphp
                                <div class="text-center mx-1" style="min-width: 36px;">
                                    <div class="small">{{ $day }}</div>
                                    <div class="small">
                                        @if ($attendance && $attendance->is_present)
                                            <span class="text-success">✓</span>
                                        @elseif ($isToday)
                                            <input type="checkbox"
                                                class="form-check-input labour-attendance-checkbox"
                                                data-labour-id="{{ $labour->id }}"
                                                data-date="{{ $dateFormatted }}">
                                        @elseif ($isPast)
                                            <span class="text-danger">✗</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Desktop View (only shown on larger screens) -->
    <div class="d-none d-lg-block">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 200px;">Labours/Wastas</th>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::create($year, $month, $day);
                                $classes = [];
                                if ($date->isWeekend()) {
                                    $classes[] = 'weekend-column';
                                }
                                if ($date->isToday()) {
                                    $classes[] = 'today-column';
                                }
                            @endphp
                            <th class="{{ implode(' ', $classes) }} text-center" style="min-width: 40px; width: 40px;">
                                <div class="small">{{ $day }}</div>
                                <div class="small text-muted">{{ $date->format('D')[0] }}</div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wastas as $index => $wasta)
                        <!-- Wasta Row -->
                        <tr class="employee-row bg-light" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $index }}" style="cursor:pointer;">
                            <td class="align-middle">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="text-truncate">
                                        <strong>{{ $wasta->wasta_name }}</strong>
                                    </div>
                                    @if ($user === 'admin')
                                    <button class="btn btn-sm btn-outline-success wasta-edit-btn flex-shrink-0"
                                        data-wasta='@json($wasta)'>
                                        <i class="fas fa-edit d-md-none"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </button>
                                    @endif
                                </div>
                            </td>

                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $day);
                                    $dateFormatted = $date->format('Y-m-d');
                                    $attendance = $wasta->attendances->firstWhere('attendance_date', $dateFormatted);
                                    $isToday = \Carbon\Carbon::today()->format('Y-m-d') === $dateFormatted;
                                    $isPast = $date->lt(\Carbon\Carbon::today());
                                    $isWeekend = $date->isWeekend();
                                    $tdClass = ($isWeekend ? 'weekend-column ' : '') . ($isToday ? 'today-column' : '');
                                @endphp
                                <td class="{{ $tdClass }} text-center align-middle p-1">
                                    @if ($attendance && $attendance->is_present)
                                        <span class="text-success fs-6">✓</span>
                                    @elseif ($isToday)
                                        <input type="checkbox" class="form-check-input wasta-attendance-checkbox"
                                            data-wasta-id="{{ $wasta->id }}" data-date="{{ $dateFormatted }}">
                                    @elseif ($isPast)
                                        <span class="text-danger fs-6">✗</span>
                                    @else
                                        <span class="text-muted fs-6">-</span>
                                    @endif
                                </td>
                            @endfor
                        </tr>

                        <!-- Labour Sub-table Row -->
                        <tr class="collapse bg-white" id="collapse-{{ $index }}">
                            <td colspan="{{ $daysInMonth + 1 }}" class="p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        @foreach ($wasta->labours as $labour)
                                            <tr>
                                                <td class="align-middle" style="min-width: 200px;">
                                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                                        <div class="text-truncate">
                                                            <strong>{{ $labour->labour_name }}</strong>
                                                            @if ($labour->position)
                                                                <small class="d-block text-muted text-truncate">{{ $labour->position }}</small>
                                                            @endif
                                                            @if ($labour->site)
                                                                <small class="d-block text-info text-truncate">{{ $labour->site->site_name }}</small>
                                                            @endif
                                                        </div>
                                                        @if ($user === 'admin')
                                                        <button class="btn btn-sm btn-outline-success labour-edit-btn flex-shrink-0"
                                                            data-labour='@json($labour)'>
                                                            <i class="fas fa-edit d-md-none"></i>
                                                            <span class="d-none d-md-inline">Edit</span>
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
                                                        $isToday = \Carbon\Carbon::today()->format('Y-m-d') === $dateFormatted;
                                                        $isPast = $date->lt(\Carbon\Carbon::today());
                                                        $isWeekend = $date->isWeekend();
                                                        $tdClass = ($isWeekend ? 'weekend-column ' : '') . ($isToday ? 'today-column' : '');
                                                    @endphp
                                                    <td class="{{ $tdClass }} text-center align-middle p-1">
                                                        @if ($attendance && $attendance->is_present)
                                                            <span class="text-success">✓</span>
                                                        @elseif ($isToday)
                                                            <input type="checkbox"
                                                                class="form-check-input labour-attendance-checkbox"
                                                                data-labour-id="{{ $labour->id }}"
                                                                data-date="{{ $dateFormatted }}">
                                                        @elseif ($isPast)
                                                            <span class="text-danger">✗</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
    <!-- Create Wasta Modal -->
    <div id="modal-create-wasta" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="createWastaForm" class="forms-sample material-form">
                        <div class="form-group">
                            <select name="phase_id" id="create_phase_id">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }} ({{$phase->site->site_name}})</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="create_phase_id-error"></p>
                        </div>

                        <div class="form-group">
                            <input id="create_wager_name" name="wager_name" type="text" />
                            <label for="create_wager_name" class="control-label">Wager Name</label><i
                                class="bar"></i>
                            <p class="text-danger" id="create_wager_name-error"></p>
                        </div>

                        <div class="form-group">
                            <input id="create_price_per_day" name="price_per_day" type="number" />
                            <label for="create_price_per_day" class="control-label">Price Per Day</label><i
                                class="bar"></i>
                            <p class="text-danger" id="create_price_per_day-error"></p>
                        </div>

                        <div class="form-group">
                            <input id="create_contact_no" name="contact_no" type="text" />
                            <label for="create_contact_no" class="control-label">Contact No</label><i
                                class="bar"></i>
                            <p class="text-danger" id="create_contact-error"></p>
                        </div>

                        <x-primary-button>Create Wager</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Labour Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="labourForm" method="POST" class="forms-sample material-form">
                        @csrf
                        <h5 class="modal-title mb-4" id="attendanceModalLabel">Create labour</h5>

                        <!-- Wasta -->
                        <div class="form-group">
                            <select name="wasta_id" class="form-select form-select-md">
                                <option value="">Select Wasta</option>
                                @foreach ($wastas as $wasta)
                                    <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                @endforeach
                            </select>
                            <label for="wasta_id" class="control-label">Select Wasta</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="wasta_id-error"></p>
                        </div>

                        <!-- Site -->
                        <div class="form-group">
                            <select name="phase_id" id="labour_phase_id">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }} ({{$phase->site->site_name}})</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="phase_id-error"></p>
                        </div>

                        <!-- Labour Name -->
                        <div class="form-group">
                            <input type="text" name="labour_name" class="form-control" />
                            <label for="labour_name" class="control-label">Labour Name</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="labour_name-error"></p>
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input type="text" name="price" id="price" class="form-control" />
                            <label for="price" class="control-label">Price</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="price-error"></p>
                        </div>

                        <!-- Contact -->
                        <div class="form-group">
                            <input type="text" name="contact" id="contact" class="form-control" />
                            <label for="contact" class="control-label">Contact No</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="contact-error"></p>
                        </div>

                        <x-primary-button>Save Attendance</x-primary-button>
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
                        <button type="submit" class="btn btn-primary">Save changes</button>
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
                        <button type="submit" class="btn btn-primary">Save changes</button>
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


                $('.wasta-attendance-checkbox').change(function() {
                    const $this = $(this);
                    $.ajax({
                        url: '/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/attendance/wasta',
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker text-danger"><i class="fas fa-check"></i></div>'
                            );
                        },
                        error: function() {
                            alert('Attendance error.');
                            $this.prop('checked', false);
                        }
                    });
                });

                $('.labour-attendance-checkbox').change(function() {
                    const $this = $(this);
                    $.ajax({
                        url: '/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/attendance/labour',
                        type: 'PUT',
                        data: {
                            labour_id: $this.data('labour-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker text-danger"><i class="fas fa-check"></i></div>'
                            );
                        },
                        error: function() {
                            alert('Attendance error.');
                            $this.prop('checked', false);
                        }
                    });
                });


                // CSRF setup for all AJAX
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // -----------------------------
                // Utility: Show form errors
                // -----------------------------
                function showErrors(errors, prefix = '') {
                    $('.text-danger').text('');
                    for (let key in errors) {
                        $(`#${prefix}${key}-error`).text(errors[key][0]);
                    }
                }

                // -----------------------------
                // CREATE WASTA
                // -----------------------------
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                function showErrors(errors, prefix = '') {
                    $('.text-danger').text('');
                    for (let key in errors) {
                        $(`#${prefix}${key}-error`).text(errors[key][0]);
                    }
                }

                $('#createWastaForm').submit(function(e) {

                    e.preventDefault();

                    const data = {
                        wager_name: $('#create_wager_name').val(),
                        price_per_day: $('#create_price_per_day').val(),
                        contact: $('#create_contact_no').val(),
                        phase_id: $('#create_phase_id').val(),
                    };

                    $.post('/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/wasta', data)
                        .done(response => {
                            alert(response.message || 'Wasta created!');
                            $('#modal-create-wasta').modal('hide');
                            location.reload();
                        })
                        .fail(xhr => {
                            if (xhr.status === 422) {
                                showErrors(xhr.responseJSON.errors, 'create_');
                            } else {
                                alert('Something went wrong.');
                            }
                        });
                });


                // -----------------------------
                // EDIT WASTA
                // -----------------------------
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
                        url: `/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/attendance/wasta/update/${id}`,
                        type: 'PUT',
                        data,
                        success: () => {
                            alert('Wasta updated!');
                            $('#modal-edit-wasta').modal('hide');
                            location.reload();
                        },
                        error: () => alert('Update failed.')
                    });
                });

                // -----------------------------
                // CREATE LABOUR (Attendance Form)
                // -----------------------------
                $('#labourForm').submit(function(e) {
                    e.preventDefault();
                    const formData = {
                        wasta_id: $('select[name="wasta_id"]').val(),
                        phase_id: $('#labour_phase_id').val(),
                        labour_name: $('input[name="labour_name"]').val(),
                        price: $('#price').val(),
                        contact: $('#contact').val(),
                        is_present: $('#is_present').is(':checked') ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    };

                    $.post('{{ url('admin/labour/store') }}', formData)
                        .done(response => {
                            alert(response.message || 'Attendance saved!');
                            $('#attendanceModal').modal('hide');
                            $('#labourForm')[0].reset();
                            location.reload();
                        })
                        .fail(xhr => {
                            if (xhr.status === 422) {
                                showErrors(xhr.responseJSON.errors);
                            } else {
                                alert('Something went wrong.');
                            }
                        });
                });

                // -----------------------------
                // EDIT LABOUR
                // -----------------------------
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
                        url: `/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/attendance/labour/update/${id}`,
                        type: 'PUT',
                        data,
                        success: () => {
                            alert('Labour updated!');
                            $('#modal-edit-labour').modal('hide');
                            location.reload();
                        },
                        error: () => alert('Update failed.')
                    });
                });

            });
        </script>
    @endpush


</x-app-layout>
