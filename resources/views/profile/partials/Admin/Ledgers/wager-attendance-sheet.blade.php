<x-app-layout>


    <!-- Alert Container (Fixed Position) -->
    <div id="ajaxAlertContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; width: 300px;"></div>

    @php
        $month = now()->month;
        $year = now()->year;
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

        $currentDate = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp


    <x-breadcrumb 
        :names="['Attendance']" 
        :urls="[$user . '/wager-attendance',]" 
    />

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

            <!-- Compact Filters -->
            <form method="GET" action="{{ url('admin/wager-attendance') }}" class="d-flex flex-wrap gap-2">
                <div class="input-group input-group-sm" style="width: 150px;">
                    <select name="site_id" class="form-select form-select-sm bg-white text-black">
                        <option value="">All Sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->site_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="month" name="monthYear" class="form-control form-control-sm"
                        value="{{ request('monthYear', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}">
                </div>

                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-filter"></i>
                </button>
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

    <style>
        .attendance-dashboard {
            font-size: 0.85rem;
        }

        .today-column {
            background-color: rgba(25, 135, 84, 0.1);
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

        .weekend-column {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .sticky-start {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: inherit;
        }

        .table th,
        .table td {
            padding: 0.4rem;
            vertical-align: middle;
        }

        .form-check-input {
            margin: 0 auto;
            transform: scale(0.9);
        }

        .present-marker.text-success {
            color: #28a745 !important;
        }

        #ajaxAlertContainer .alert {
            margin-bottom: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>

    <!-- Create Wasta Modal -->
    <div id="modal-create-wasta" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="createWastaForm" class="forms-sample material-form">
                        <div>
                            <select name="phase_id" id="create_phase_id" class="form-select form-select-sm text-black" style="cursor: pointer;">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}
                                        ({{ $phase->site->site_name }})
                                    </option>
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

                        <button class="btn btn-success btn-sm">Create Wager</button>

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
                    <form id="labourForm" method="POST" class="forms-sample material-form">
                        @csrf

                        <!-- Wasta -->
                        <div >

                            <select name="wasta_id" class="form-select form-select-md text-black" style="cursor: pointer" >
                                <option value="">Select Wasta</option>
                                @foreach ($wastas as $wasta)
                                    <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                @endforeach
                            </select>
                            <i class="bar"></i>
                            <p class="text-danger" id="wasta_id-error"></p>
                        </div>

                        <!-- Site -->
                        <div >
                            <select name="phase_id" id="labour_phase_id" class="form-select form-select-md text-black" style="cursor: pointer" >
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}
                                        ({{ $phase->site->site_name }})
                                    </option>
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

                        <button class="btn btn-success btn-sm">Create Labour</button>
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
                        <button class="btn btn-success btn-sm">Save Changes</button>

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
            </div>
        `;

                    $('#ajaxAlertContainer').append(alertHtml);

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 5000);
                }

                // Attendance Checkbox Handlers
                $(document).on('change', '.wasta-attendance-checkbox', function() {
                    const $this = $(this);
                    $.ajax({
                        url: '/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/attendance/wasta',
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
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
                            $this.prop('checked', !$this.is(':checked'));
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                $(document).on('change', '.labour-attendance-checkbox', function() {
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
                                '<div class="present-marker text-success"><i class="fas fa-check"></i></div>'
                            );
                            showAlert('success', 'Attendance updated successfully');
                        },
                        error: function(xhr) {
                            $this.prop('checked', !$this.is(':checked'));
                            const errorMsg = xhr.responseJSON?.message ||
                                'Failed to update attendance';
                            showAlert('error', errorMsg);
                        }
                    });
                });

                // Form Submissions
                $('#createWastaForm').submit(function(e) {
                    e.preventDefault();
                    const data = {
                        wager_name: $('#create_wager_name').val(),
                        price_per_day: $('#create_price_per_day').val(),
                        contact: $('#create_contact_no').val(),
                        phase_id: $('#create_phase_id').val()
                    };

                    $.post('/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/wasta', data)
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

                $('#labourForm').submit(function(e) {
                    e.preventDefault();
                    const formData = {
                        wasta_id: $('select[name="wasta_id"]').val(),
                        phase_id: $('#labour_phase_id').val(),
                        labour_name: $('input[name="labour_name"]').val(),
                        price: $('#price').val(),
                        contact: $('#contact').val(),
                        is_present: $('#is_present').is(':checked') ? 1 : 0
                    };

                    $.post(`/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/labour/store`, formData)
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

                // Utility Functions
                function showErrors(errors, prefix = '') {
                    $('.text-danger').text('');
                    for (let key in errors) {
                        $(`#${prefix}${key}-error`).text(errors[key][0]);
                    }
                }

                // Event delegation for dynamically loaded elements
                $(document).on('click', '.wasta-edit-btn', function() {
                    const wasta = JSON.parse($(this).attr('data-wasta'));
                    $('#edit_wasta_id').val(wasta.id);
                    $('#edit_wasta_name').val(wasta.wasta_name);
                    $('#modal-edit-wasta').modal('show');
                });

                $(document).on('click', '.labour-edit-btn', function() {
                    const labour = JSON.parse($(this).attr('data-labour'));
                    $('#edit_labour_id').val(labour.id);
                    $('#edit_labour_name').val(labour.labour_name);
                    $('#modal-edit-labour').modal('show');
                });
            });
        </script>
    @endpush


</x-app-layout>
