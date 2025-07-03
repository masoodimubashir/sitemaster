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


    <x-breadcrumb :names="['Attendance']" :urls="[$user . '/wager-attendance']" />

    <div class="attendance-dashboard border-0">


        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">

            <div>
                <h4 class="mb-1"><i class="fas fa-calendar-check text-info me-2"></i>Attendance Summary</h4>
                <span class="badge bg-light text-dark">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </span>
            </div>

            <form method="GET" action="{{ url('admin/wager-attendance') }}"
                class="d-flex flex-wrap align-items-center gap-2" id="attendanceFilterForm">
                <!-- Site Filter -->
                <div class="input-group input-group-sm" style="width: 150px;">
                    <select name="site_id" class="form-select form-select-sm bg-white text-black"
                        onchange="this.form.submit()">
                        <option value="">All Sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                {{ $site->site_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Filter -->
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="month" name="monthYear" class="form-control form-control-sm"
                        value="{{ request('monthYear', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}"
                        onchange="this.form.submit()">
                </div>

                <!-- Reset Button -->
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </form>



        </div>

        <!-- Summary Cards -->
        <div class="row g-3">
            @php
                $totalLabours = $wastas->sum(fn($w) => $w->labours->count());
            @endphp

            <div class="row g-3 mb-4">
                <!-- Total Wastas -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <i class="fas fa-users text-primary fs-3"></i>
                            <div>
                                <div class="text-muted small">Total Wastas</div>
                                <h5 class="mb-0">{{ $wastas->count() }}</h5>
                                <small class="text-muted">₹{{ number_format($siteTotal['wasta_amount'], 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Labours -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <i class="fas fa-hard-hat text-info fs-3"></i>
                            <div>
                                <div class="text-muted small">Total Labours</div>
                                <h5 class="mb-0">
                                    {{ $wastas->sum(function ($wasta) {return $wasta->labours->count();}) }}
                                </h5>
                                <small class="text-muted">₹{{ number_format($siteTotal['labour_amount'], 2) }}</small>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Monthly Total -->
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <i class="fas fa-wallet text-dark fs-3"></i>
                            <div>
                                <div class="text-muted small">Monthly Total</div>
                                <h5 class="mb-0">₹{{ number_format($siteTotal['combined_total'], 2) }}</h5>
                                <small class="text-muted">Combined</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-between align-items-center my-4">
            <div class="btn-group">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-create-wasta">
                    <i class="fas fa-plus me-1"></i> Add Wasta
                </button>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal">
                    <i class="fas fa-plus me-1"></i> Add Labour
                </button>
            </div>
        </div>

        <!-- Tabs: Summary View | Calendar View -->
        <ul class="nav nav-tabs mb-3" id="attendanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#summaryTab">Summary View</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#calendarTab">Calendar View</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Summary Tab -->
            <div class="tab-pane fade show active" id="summaryTab">
                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="min-width: 220px;">Name</th>
                                <th>Type</th>
                                <th>Phase</th>
                                <th>Days Present</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wastas as $wasta)
                                <tr>
                                    <td><strong>{{ $wasta->wasta_name }}</strong></td>
                                    <td><span class="badge bg-primary">Wasta</span></td>
                                    <td>{{ $wasta->phase->phase_name ?? '—' }}</td>
                                    <td>{{ $wasta->present_days }}/{{ $daysInMonth }}</td>
                                    <td>₹{{ number_format($wasta->total_amount, 2) }}</td>
                                </tr>
                                @foreach ($wasta->labours as $labour)
                                    <tr>
                                        <td class="ps-4">{{ $labour->labour_name }}</td>
                                        <td><span class="badge bg-info">Labour</span></td>
                                        <td>—</td>
                                        <td>{{ $labour->present_days }}/{{ $daysInMonth }}</td>
                                        <td>₹{{ number_format($labour->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calendar Tab -->
            <div class="tab-pane fade" id="calendarTab">
                <div class="table-responsive border rounded mt-3">
                    <table class="table table-bordered text-center table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="min-width: 200px;">Name</th>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    <th style="width: 28px;">{{ $d }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wastas as $wasta)
                                <tr>
                                    <td class="text-start fw-bold">{{ $wasta->wasta_name }}</td>
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                                            $present = $wasta->attendances->firstWhere('attendance_date', $date)
                                                ?->is_present;
                                        @endphp
                                        <td>{!! $present ? '<i class="fas fa-check text-success"></i>' : '—' !!}</td>
                                    @endfor
                                </tr>
                                @foreach ($wasta->labours as $labour)
                                    <tr>
                                        <td class="ps-4">{{ $labour->labour_name }}</td>
                                        @for ($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                                                $present = $labour->attendances->firstWhere('attendance_date', $date)
                                                    ?->is_present;
                                            @endphp
                                            <td>{!! $present ? '<i class="fas fa-check text-success"></i>' : '—' !!}</td>
                                        @endfor
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                            <select name="phase_id" id="create_phase_id"
                                class="form-select form-select-sm text-black" style="cursor: pointer;">
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
                        <div>

                            <select name="wasta_id" class="form-select form-select-md text-black"
                                style="cursor: pointer">
                                <option value="">Select Wasta</option>
                                @foreach ($wastas as $wasta)
                                    <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                @endforeach
                            </select>
                            <i class="bar"></i>
                            <p class="text-danger" id="wasta_id-error"></p>
                        </div>

                        <!-- Site -->
                        <div>
                            <select name="phase_id" id="labour_phase_id"
                                class="form-select form-select-md text-black" style="cursor: pointer">
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

                    $.post(`/{{ auth()->user()->role_name === 'admin' ? 'admin' : 'user' }}/labour/store`,
                            formData)
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

        <script>
            function resetFilters() {
                // Get the form
                const form = document.getElementById('attendanceFilterForm');

                // Reset select elements
                const selects = form.querySelectorAll('select');
                selects.forEach(select => {
                    select.selectedIndex = 0;
                });

                // Reset month input to current month
                const now = new Date();
                const currentMonth = now.getMonth() + 1;
                const monthStr = currentMonth < 10 ? '0' + currentMonth : currentMonth;
                form.querySelector('input[name="monthYear"]').value = `${now.getFullYear()}-${monthStr}`;

                // Submit the form
                form.submit();
            }
        </script>
    @endpush


</x-app-layout>
