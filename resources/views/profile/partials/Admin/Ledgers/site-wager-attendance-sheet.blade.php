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

    <x-breadcrumb :names="['Sites', $site->site_name]" :urls="['admin/sites', 'admin/sites/' . base64_encode($site->id)]" />



    <div class="card attendance-card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0 d-flex align-items-center">
                <i class="fas fa-calendar-check me-2 text-primary"></i>
                Attendance Report
            </h2>

            <div class="controls">

                <div class="ms-auto action-buttons d-flex gap-2">
                    <!-- Dropdown Menu -->
                    <div class="dropdown">

                        <button class="btn btn-outline-primary btn-sm dropdown-toggle " type="button"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Make Entry
                        </button>

                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                            <li>
                                <a class="dropdown-item" data-bs-toggle="modal" role="button"
                                    href="#modal-create-wasta">
                                    Create Wasta
                                </a>
                            </li>

                            <li>
                                <!-- Button to Open Modal -->
                                <a class="dropdown-item" role="button" data-bs-toggle="modal" href="#attendanceModal">
                                    Create Labour
                                </a>
                            </li>

                        </ul>

                    </div>

                </div>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th></th>
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
                            <th class="{{ implode(' ', $classes) }}">{{ $day }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wastas as $index => $wasta)
                        <!-- Wasta Row - Compact Header -->
                        <tr class="wasta-row bg-light" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $index }}" style="cursor: pointer;">
                            <td class="align-middle" style="width: 200px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $wasta->wasta_name }}</strong>
                                        <span class="badge bg-primary ms-2">{{ $wasta->labours->count() }}</span>
                                    </div>
                                    <x-primary-button class="wasta-edit-btn"
                                        data-wasta='@json($wasta)'>
                                        Edit
                                    </x-primary-button>
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
                                    $dayInitial = $date->format('D')[0];
                                    $tdClass = ($isWeekend ? 'bg-light' : '') . ($isToday ? ' table-success' : '');
                                @endphp
                                <td class=" text-center align-middle p-1" style="width: 30px;">
                                    <div class="d-flex flex-column small">
                                        <div class="text-muted">{{ $dayInitial }}</div>
                                        <div class="mt-2">
                                            @if ($attendance && $attendance->is_present)
                                                <span class="text-success fs-6">✓</span>
                                            @elseif ($isToday)
                                                <input type="checkbox"
                                                    class="form-check-input wasta-attendance-checkbox"
                                                    data-wasta-id="{{ $wasta->id }}"
                                                    data-date="{{ $dateFormatted }}">
                                            @elseif ($isPast)
                                                <span class="text-danger fs-6">✗</span>
                                            @else
                                                <span class="text-muted fs-6">-</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            @endfor
                        </tr>

                        <!-- Labour Sub-table Row -->
                        <tr class="collapse bg-white" id="collapse-{{ $index }}">
                            <td colspan="{{ $daysInMonth + 1 }}" class="p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0 labour-table">
                                        @foreach ($wasta->labours as $labour)
                                            <tr>
                                                <td class="align-middle" style="width: 200px;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $labour->labour_name }}</strong>
                                                            @if ($labour->position)
                                                                <small
                                                                    class="d-block text-muted">{{ $labour->position }}</small>
                                                            @endif
                                                        </div>
                                                        <x-primary-button class="labour-edit-btn"
                                                            data-labour='@json($labour)'>
                                                            Edit
                                                        </x-primary-button>
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
                                                        $isToday =
                                                            \Carbon\Carbon::today()->format('Y-m-d') === $dateFormatted;
                                                        $isPast = $date->lt(\Carbon\Carbon::today());
                                                        $isWeekend = $date->isWeekend();
                                                        $tdClass =
                                                            ($isWeekend ? 'bg-light' : '') .
                                                            ($isToday ? ' table-info' : '');
                                                        $dayInitial = $date->format('D')[0];
                                                    @endphp
                                                    <td class=" text-center align-middle p-1" style="width: 30px;">
                                                        <div class="text-muted">{{ $dayInitial }}</div>
                                                        <div class="mt-2">
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
                            <label for="phase_id" class="control-label">Select Phase</label>
                            <select name="phase_id" id="phase_id" class="form-select form-select-md">
                                <option value="">Select Phase</option>
                                @foreach ($phases as $phase)
                                    <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="phase_id-error"></p>
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

                        <x-primary-button>Create Wager</x-primary-button>
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
                        <h5 class="modal-title" id="attendanceModalLabel">Create labour</h5>

                        <!-- Wasta -->
                        <div class="form-group mt-2">
                            <label for="wasta_id" class="control-label">Select Wasta</label>

                            <select name="wasta_id" class="form-select form-select-md">
                                <option value="">Select Wasta</option>
                                @foreach ($wastas as $wasta)
                                    <option value="{{ $wasta->id }}">{{ $wasta->wasta_name }}</option>
                                @endforeach
                            </select>
                            <p class="text-danger" id="wasta_id-error"></p>
                        </div>

                        <div class="form-group">
                            <label for="phase_id" class="control-label">Select Phase</label>

                            <select name="phase_id" id="labour_phase_id" class="form-select form-select-md">
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
                        url: '/admin/attendance/wasta',
                        type: 'PUT',
                        data: {
                            wasta_id: $this.data('wasta-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0,
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker"><i class="fas fa-check"></i></div>'
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
                        url: '/admin/attendance/labour',
                        type: 'PUT',
                        data: {
                            labour_id: $this.data('labour-id'),
                            date: $this.data('date'),
                            is_present: $this.is(':checked') ? 1 : 0
                        },
                        success: function() {
                            $this.replaceWith(
                                '<div class="present-marker"><i class="fas fa-check"></i></div>'
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
                        site_id: $('#create_site_id').val(),
                        phase_id: $('#phase_id').val(),

                    };

                    $.post('/admin/wasta', data)
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
                        url: `/admin/attendance/wasta/update/${id}`,
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
                        site_id: $('#labour_site_id').val(),
                        labour_name: $('input[name="labour_name"]').val(),
                        price: $('#price').val(),
                        contact: $('#contact').val(),
                        is_present: $('#is_present').is(':checked') ? 1 : 0,
                        phase_id: $('#labour_phase_id').val(),
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
                // EDIT LABOUR (Use event delegation)
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
