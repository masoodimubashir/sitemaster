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
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --border-radius: 0.5rem;
        }

        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .attendance-card {
            border-radius: var(--border-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
        }

        .month-selector {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.4rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-button {
            background-color: white;
            border: 1px solid #e9ecef;
            color: #495057;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.4rem;
            transition: all 0.2s;
        }

        .nav-button:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .attendance-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .attendance-table th,
        .attendance-table td {
            border: none;
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .attendance-table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .attendance-table th:first-child,
        .attendance-table td:first-child {
            padding-left: 1.5rem;
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.025);
        }

        .attendance-table th:first-child {
            background-color: #f8f9fa;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        /* Style for name cell */
        .name-cell {
            min-width: 180px;
            max-width: 200px;
        }

        /* Attendance markers */
        .present-marker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0f7e6;
            color: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 14px;
        }

        .attendance-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
            border-radius: 4px;
            border: 2px solid #dee2e6;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            position: relative;
            margin: 0 auto;
            display: block;
        }

        .attendance-checkbox:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .attendance-checkbox:checked::after {
            content: '✓';
            color: white;
            position: absolute;
            font-size: 12px;
            top: 0;
            left: 4px;
        }

        /* Striped rows */
        .attendance-table tbody tr:nth-child(even) {
            background-color: #f9fbfd;
        }

        /* Hover effect */
        .attendance-table tbody tr:hover {
            background-color: #f0f7ff !important;
        }

        /* Today column highlight */
        .today-column {
            background-color: #f0f7ff !important;
        }

        /* Profile images */
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .weekend-column {
            background-color: #f9f9f9;
        }

        .weekend-column.today-column {
            background-color: #e6f0ff !important;
        }

        /* Date indicators */
        .date-cell {
            text-align: center;
            min-width: 40px;
        }

        /* Small status indicator for past days */
        .absent-indicator {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #ffe5e5;
            margin: 0 auto;
        }

        /* Name styling */
        .employee-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .employee-info {
            display: flex;
            align-items: center;
        }

        .employee-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Improving the scrollbar */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #cfd8dc;
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #b0bec5;
        }
    </style>





    <div class="controls">




        <div class="ms-auto action-buttons d-flex gap-2">
            <!-- Dropdown Menu -->
            <div class="dropdown">

                <button class="btn btn-outline-primary btn-sm dropdown-toggle " type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Make Entry
                </button>

                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#modal-wasta">
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



    <div class="card attendance-card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h2 class="h5 mb-0 d-flex align-items-center">
                <i class="fas fa-calendar-check me-2 text-primary"></i>
                Monthly Attendance Report
            </h2>

            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ url('admin/wager-attendance') }}"
                    class="mb-3 d-flex align-items-center gap-2">
                    <input type="month" name="monthYear" class="form-control w-auto"
                        value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                    <button type="submit" class="btn btn-primary ">Filter</button>
                </form>
            </div>

        </div>

        <div class="table-container">
            <table class="table attendance-table mb-0">
                <thead>
                    <tr>
                        <th class="text-start name-cell">Employee</th>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::create($year, $month, $day);
                                $isWeekend = $date->isWeekend();
                                $isToday = $date->isToday();
                                $classes = [];
                                if ($isWeekend) {
                                    $classes[] = 'weekend-column';
                                }
                                if ($isToday) {
                                    $classes[] = 'today-column';
                                }
                            @endphp
                            <th class="date-cell {{ implode(' ', $classes) }}">{{ $day }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($wastas as $wasta)
                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="profile-img"
                                        style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <span style="font-weight: bold;">
                                            {{ strtoupper(substr($wasta->wasta_name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $wasta->wasta_name)[1] ?? '', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="employee-name">{{ $wasta->wasta_name }}</div>
                                    </div>
                                </div>
                            </td>

                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                                    $attendance = $wasta->attendances->firstWhere('attendance_date', $date);
                                    $isToday = \Carbon\Carbon::today()->format('Y-m-d') === $date;
                                    $isPast = \Carbon\Carbon::parse($date)->lt(\Carbon\Carbon::today());
                                    $isWeekend = \Carbon\Carbon::parse($date)->isWeekend();
                                    $tdClasses = $isWeekend ? 'weekend-column' : '';
                                    $tdClasses .= $isToday ? ' today-column' : '';
                                @endphp

                                <td class="{{ $tdClasses }}">
                                    @if ($attendance && $attendance->is_present)
                                        <div class="present-marker"><i class="fas fa-check"></i></div>
                                    @elseif ($isToday)
                                        <input type="checkbox" class="attendance-checkbox"
                                            name="attendance[{{ $wasta->id }}][{{ $date }}]"
                                            data-wasta-id="{{ $wasta->id }}" data-date="{{ $date }}">
                                    @elseif ($isPast)
                                        <div class="absent-indicator"></div>
                                    @else
                                        <input type="checkbox" disabled>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <!-- Daily Wager Form -->
    <div id="modal-wasta" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    <form id="wastaForm" class="forms-sample material-form">

                        <div class="form-group">
                            <input id="wager_name" name="wager_name" type="text" />
                            <label for="wager_name" class="control-label">Wager Name</label><i class="bar"></i>
                            <p class="text-danger" id="wager_name-error"></p>
                        </div>

                        <div class="form-group">
                            <input id="price_per_day" name="price_per_day" type="number" />
                            <label for="price_per_day" class="control-label">Price Per Day</label><i class="bar"></i>
                            <p class="text-danger" id="price-error"></p>
                        </div>

                        <div class="form-group">
                            <input id="contact_no" name="contact_no" type="text" />
                            <label for="contact_no" class="control-label">Contact No</label><i class="bar"></i>
                            <p class="text-danger" id="contact_no-error"></p>
                        </div>

                        <x-primary-button> Create Wager </x-primary-button>
                    </form>


                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">

                <div class="modal-body">

                    <form id="attendanceForm" method="POST" class="forms-sample material-form">

                        @csrf

                        <h5 class="modal-title mb-4" id="attendanceModalLabel">Add Attendance</h5>

                        <!-- Wasta Section -->
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

                        <!-- Labour Section -->
                        <div class="form-group ">
                            <input type="text" name="labour_name" class="form-control" />
                            <label for="labour_name" class="control-label">Labour Name</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="labour_name-error"></p>
                        </div>

                        {{-- Price Section  --}}
                        <div class="form-group ">
                            <input type="text" name="price" id="price" class="form-control" />
                            <label for="price" class="control-label">Price</label>
                            <i class="bar"></i>
                            <p class="text-danger" id="price-error"></p>
                        </div>


                        {{-- Contact Section --}}
                        <div class="form-group ">
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

    <!-- Toggle Fields Script -->
    <div id="messageContainer"></div>

    @push('scripts')
        <script>
            function resetForm() {
                document.querySelector('select[name="site_id"]').value = 'all';
                document.querySelector('select[name="wager_id"]').value = 'all';
                document.querySelector('select[name="date_filter"]').value = 'today';
                document.getElementById('filterForm').submit();
            }

            $(document).ready(function() {

                const form = $('#wastaForm');

                // Form To Create Wasta
                $('#wastaForm').on('submit', function(e) {

                    e.preventDefault();

                    const messageContainer = form.find('.message-container');
                    messageContainer.empty();
                    $('.text-danger').text('');

                    let formData = {
                        wager_name: $('#wager_name').val(),
                        price: $('#price_per_day').val(),
                        contact: $('#contact_no').val(),
                        _token: $('input[name="_token"]').val()
                    };

                    $.ajax({
                        url: '/admin/wasta',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            messageContainer.html(`
                        <div class="alert align-items-center text-white bg-success border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                    `);
                            $('#wastaForm')[0].reset();
                            $('#modal-wasta').modal('hide');

                            setTimeout(function() {
                                messageContainer.find('.alert').alert('close');
                                location.reload();
                            }, 2000);
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;

                                for (let field in errors) {
                                    $(`#${field}-error`).text(errors[field][0]);
                                }
                            } else {
                                alert('Something went wrong. Please try again.');
                            }
                        }
                    });
                });


                // Attendance Form
                $('#attendanceForm').on('submit', function(e) {

                    e.preventDefault();

                    $('.text-danger').text('');
                    const type = $('#type').val();

                    let url = '';

                    url = '{{ url('admin/attendance/labour') }}';

                    const formData = {
                        wasta_id: $('select[name="wasta_id"]').val(),
                        labour_name: $('input[name="labour_name"]').val(),
                        price: $('input[name="price"]').val(),
                        contact: $('input[name="contact"]').val(),
                        is_present: $('#is_present').is(':checked') ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    };

                    $.ajax({

                        url: url,
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            $('#attendanceModal').modal('hide');
                            $('#attendanceForm')[0].reset();
                            alert(response.message || 'Attendance saved successfully!');
                            location.reload();
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {

                                const errors = xhr.responseJSON.errors;
                                // Clear previous errors
                                $('.text-danger').text('');

                                // Loop through each error field
                                for (const key in errors) {
                                    const errorMessage = errors[key][0];
                                    const errorElement = $(`#${key}-error`);

                                    if (errorElement.length) {
                                        errorElement.text(errorMessage);
                                    } else {
                                        console.warn(`Missing error container for: ${key}`);
                                    }
                                }
                            } else {
                                alert('Something went wrong. Please try again.');
                            }
                        }
                    });

                })


                // Method To Make Attendace For wasta
                $('.attendance-checkbox').change(function() {

                    let checkbox = $(this);
                    let wastaId = checkbox.data('wasta-id');
                    let date = checkbox.data('date');
                    let isPresent = checkbox.is(':checked') ? 1 : 0;

                    $.ajax({
                        url: '/admin/attendance/wasta',
                        type: 'PUT',
                        data: {
                            wasta_id: wastaId,
                            date: date,
                            is_present: isPresent,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            checkbox.replaceWith(
                                '<div class="present-marker"><i class="fas fa-check"></i></div>'
                            );

                        },
                        error: function() {
                            alert('Something went wrong. Please try again.');
                            checkbox.prop('checked', false);
                        }
                    });
                });


            });
        </script>
    @endpush

</x-app-layout>
