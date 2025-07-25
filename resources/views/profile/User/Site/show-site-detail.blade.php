<x-app-layout>

    <x-breadcrumb :names="['Sites', $site->site_name, ' Back']" :urls="['/user/sites', '/user/sites/' . base64_encode($site->id), '/user/sites/' . base64_encode($site->id)]" />

    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #374151;
            line-height: 1.6;
        }

        .minimal-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.2s ease;
        }

        .minimal-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .site-info-icon {
            width: 40px;
            height: 40px;
            background-color: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            margin-right: 16px;
        }

        .label-text {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .value-text {
            font-size: 16px;
            color: #111827;
            font-weight: 600;
        }

        .tab-minimal {
            background: none;
            border: 1px solid #d1d5db;
            color: #6b7280;
            padding: 8px 16px;
            border-radius: 6px;
            margin-right: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-minimal.active {
            background-color: #111827;
            color: white;
            border-color: #111827;
        }

        .tab-minimal:hover:not(.active) {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        .summary-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .metric-row:last-child {
            border-bottom: none;
        }

        .metric-label {
            color: #6b7280;
            font-weight: 500;
            font-size: 14px;
        }

        .metric-value {
            color: #111827;
            font-weight: 600;
            font-size: 14px;
        }

        .total-row {
            background-color: #f9fafb;
            margin: 0 -24px;
            padding: 16px 24px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-minimal {
            background-color: #111827;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease;
        }

        .btn-minimal:hover {
            background-color: #1f2937;
            color: white;
        }

        .btn-outline {
            background: none;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .btn-outline:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
        }

        .table-minimal {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-header-minimal {
            background-color: #f9fafb;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
        }

        .table-minimal table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-minimal th {
            background-color: #f9fafb;
            padding: 12px 20px;
            text-align: left;
            font-weight: 500;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-minimal td {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }

        .table-minimal tbody tr:hover {
            background-color: #f9fafb;
        }

        .edit-link {
            color: #6b7280;
            padding: 6px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .edit-link:hover {
            background-color: #f3f4f6;
            color: #374151;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 20px 0;
        }
    </style>

    <!-- Site Information Header -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="minimal-card p-4 h-100">
                <div class="d-flex align-items-start mb-3">
                    <div class="site-info-icon bg-info">
                        <i class="fas fa-building text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="label-text">Site Name</div>
                        <div class="value-text">{{ ucwords($site->site_name) }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div class="site-info-icon bg-info">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="label-text">Owner</div>
                        <div class="value-text">{{ ucwords($site->site_owner_name) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="minimal-card p-4 h-100">
                <div class="d-flex align-items-start mb-3">
                    <div class="site-info-icon bg-info">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="label-text">Contact</div>
                        <div class="value-text">
                            <a href="tel:+91-{{ $site->contact_no }}" class="text-decoration-none"
                                style="color: #111827;">
                                +91-{{ $site->contact_no }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <div class="site-info-icon bg-info">
                        <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="label-text">Location</div>
                        <div class="value-text">{{ ucwords($site->location) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Report Button -->
    <div class="mb-4">
        <a href="{{ url('user/download-site/report/' . base64_encode($site->id)) }}" class="btn-minimal">
            <i class="fas fa-download"></i>
            Download Site Report
        </a>
    </div>

    <!-- Phase Data -->
    @if (count($phaseData) > 0)
        <!-- Phase Tabs -->
        <div class="mb-4">
            @foreach ($phaseData as $key => $phase)
                <button class="tab-minimal {{ $key === 0 ? 'active' : '' }}" onclick="showPhase({{ $key }})"
                    id="tab-{{ $key }}">
                    {{ ucfirst($phase['phase']) }}
                </button>
            @endforeach
        </div>

        <!-- Phase Content -->
        @foreach ($phaseData as $key => $phase)
            <div class="phase-content {{ $key === 0 ? '' : 'd-none' }}" id="phase-{{ $key }}">

                <!-- Financial Summary -->
                <div class="summary-section">
                    <h3 class="section-title">{{ ucfirst($phase['phase']) }}</h3>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="metric-row">
                                <span class="metric-label">Construction Materials</span>
                                <span
                                    class="metric-value">₹{{ number_format($phase['construction_total_amount'], 2) }}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Contractor Work</span>
                                <span
                                    class="metric-value">₹{{ number_format($phase['square_footage_total_amount'], 2) }}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Daily Expenses</span>
                                <span
                                    class="metric-value">₹{{ number_format($phase['daily_expenses_total_amount'], 2) }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="metric-row">
                                <span class="metric-label">Wasta</span>
                                <span
                                    class="metric-value">₹{{ number_format($phase['daily_wastas_total_amount'], 2) }}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Labour</span>
                                <span
                                    class="metric-value">₹{{ number_format($phase['daily_labours_total_amount'], 2) }}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Effective Balance</span>
                                <span class="metric-value">₹{{ number_format($phase['phase_total'], 2) }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="total-row">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="metric-row mb-0">
                                    <span class="metric-label">Total Amount</span>
                                    <span class="metric-value"
                                        style="font-size: 16px;">₹{{ number_format($phase['phase_total_with_service_charge'], 2) }}</span>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Generate PDF Button -->
                <div class="d-flex justify-content-end mt-4 mb-4">
                    <a href="{{ url('user/download-phase/report', ['id' => base64_encode($phase['phase_id'])]) }}"
                        class="btn-minimal">
                        <i class="fas fa-file-pdf"></i>
                        Generate PDF
                    </a>
                </div>
                <!-- Data Tables -->
                @php
                    $tables = [
                        'construction_material_billings' => [
                            'label' => 'Construction Materials',
                            'data' => $phase['construction_material_billings'],
                            'color' => 'info',
                        ],
                        'square_footage_bills' => [
                            'label' => 'Contractor Work',
                            'data' => $phase['square_footage_bills'],
                            'color' => 'info',
                        ],
                        'daily_expenses' => [
                            'label' => 'Daily Expenses',
                            'data' => $phase['daily_expenses'],
                            'color' => 'info',
                        ],
                        'daily_wastas' => [
                            'label' => 'Wasta Records',
                            'data' => $phase['daily_wastas'],
                            'color' => 'info',
                        ],
                        'daily_labours' => [
                            'label' => 'Labour Records',
                            'data' => $phase['daily_labours'],
                            'color' => 'info',
                        ],
                    ];
                @endphp

                @foreach ($tables as $tableKey => $table)
                    <div class="card mb-4">
                        <div class="card-header bg-{{ $table['color'] }} bg-opacity-10 border-bottom-0">
                            <h4 class="h6 mb-0 text-white ">
                                <i
                                    class="fas fa-{{ $tableKey === 'construction_material_billings'
                                        ? 'bricks'
                                        : ($tableKey === 'square_footage_bills'
                                            ? 'hard-hat'
                                            : ($tableKey === 'daily_expenses'
                                                ? 'receipt'
                                                : ($tableKey === 'daily_wastas'
                                                    ? 'handshake'
                                                    : 'users'))) }} me-2">
                                </i>
                                {{ $table['label'] }}
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Bill Proof</th>
                                            <th>Description</th>
                                            <th>Supplier</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Total (with SC)</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($table['data'] as $entry)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($entry['created_at'])->format('d-M-Y') }}
                                                </td>
                                                <td>
                                                    @if ($entry['image'])
                                                        <div class="position-relative d-inline-block">
                                                            <img src="{{ asset('storage/' . $entry['image']) }}"
                                                                alt=""
                                                                style="max-width: 100px; max-height: 100px;">

                                                            <a href="{{ asset('storage/' . $entry['image']) }}"
                                                                download
                                                                class="position-absolute start-0 end-0 bottom-0 text-center text-white bg-dark bg-opacity-70 p-1 text-decoration-none opacity-0 hover-opacity-100 transition-all">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        Bill Not Available
                                                    @endif
                                                </td>
                                                <td>{{ $entry['description'] ?? '-' }}</td>
                                                <td>{{ $entry['supplier'] ?? '-' }}</td>
                                                <td class="text-end">₹{{ number_format($entry['debit'], 2) }}</td>
                                                <td class="text-end fw-medium">
                                                    ₹{{ number_format($entry['total_amount_with_service_charge'], 2) }}
                                                </td>
                                                <td class="text-nowrap">
                                                    @switch($entry['category'])
                                                        @case('Material')
                                                            <a href="{{ url('/user/construction-material-billings/' . base64_encode($entry['id'])) }}"
                                                                class="text-primary me-2" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @break

                                                        @case('SQFT')
                                                            <a href="{{ url('/user/square-footage-bills/' . base64_encode($entry['id'])) }}"
                                                                class="text-primary me-2" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @break

                                                        @case('Expense')
                                                            <a href="{{ url('/user/daily-expenses/' . base64_encode($entry['id'])) }}"
                                                                class="text-primary me-2" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @break
                                                    @endswitch
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">
                                                        <i class="fas fa-database me-2"></i> No records found
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @else
            <div class="minimal-card p-5 text-center">
                <i class="fas fa-info-circle" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                <h4 style="color: #6b7280; font-weight: 500;">No phase data available for this site.</h4>
            </div>
        @endif



        @push('scripts')
            <script>
                function showPhase(phaseIndex) {
                    // Hide all phase contents
                    document.querySelectorAll('.phase-content').forEach(content => {
                        content.classList.add('d-none');
                    });

                    // Remove active class from all tabs
                    document.querySelectorAll('.tab-minimal').forEach(tab => {
                        tab.classList.remove('active');
                    });

                    // Show selected phase content
                    document.getElementById('phase-' + phaseIndex).classList.remove('d-none');

                    // Add active class to selected tab
                    document.getElementById('tab-' + phaseIndex).classList.add('active');
                }
            </script>

            <script>
                $(document).on('click', '.delete-btn', function() {
                    const button = $(this);
                    const id = button.data('id');
                    const type = button.data('type');
                    const url = button.data('url');
                    const row = button.closest('tr');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete this  record. This action cannot be undone!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                },
                                success: function(response) {
                                    if (response.error) {
                                        // Show error message from backend
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Cannot Delete',
                                            text: response.error,
                                            confirmButtonColor: '#3085d6',
                                        });
                                    } else {
                                        // Show success message
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted!',
                                            text: response.message ||
                                                'The record has been deleted successfully.',
                                            confirmButtonColor: '#3085d6',
                                        });
                                        row.fadeOut(400, function() {
                                            $(this).remove();
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    let errorMessage = 'Something went wrong. Please try again.';

                                    // Check for specific error messages from backend
                                    if (xhr.responseJSON && xhr.responseJSON.error) {
                                        errorMessage = xhr.responseJSON.error;
                                    } else if (xhr.status === 404) {
                                        errorMessage =
                                            'Record not found. It may have already been deleted.';
                                    } else if (xhr.status === 500) {
                                        errorMessage = 'Server error. Please try again later.';
                                    }

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: errorMessage,
                                        confirmButtonColor: '#3085d6',
                                    });
                                }
                            });
                        }
                    });
                });
            </script>

            <script>
                // Delete functionality
                $(document).on('click', '.delete-link', function(e) {
                    e.preventDefault();

                    const button = $(this);
                    const id = button.data('id');
                    const type = button.data('type');
                    const row = button.closest('tr');

                    const routes = {
                        'Materials': 'construction-material-billings',
                        'Contractor': 'square-footage-bills',
                        'Expenses': 'daily-expenses',
                        'Wasta': 'dailywager',
                        'Labour': 'daily-wager-attendance'
                    };

                    if (!routes[type]) {
                        showAlert('error', 'Invalid operation type');
                        return;
                    }

                    if (!confirm('Are you sure you want to delete this item?')) {
                        return;
                    }

                    $.ajax({
                        url: `{{ url('admin') }}/${routes[type]}/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response) {
                            showAlert('success', response.message);
                            row.fadeOut(400, function() {
                                $(this).remove();
                            });
                        },
                        error: function(error) {
                            const message = error.status === 404 ?
                                (error.responseJSON?.error || 'Resource not found') :
                                'An error occurred. Please try again.';
                            showAlert('error', message);
                        }
                    });
                });



                // Helper function for showing alerts
                function showAlert(type, message) {
                    const container = $('#messageContainer');
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                    container.empty().append(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);

                    setTimeout(() => {
                        container.find('.alert').fadeOut(400, function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            </script>
        @endpush

    </x-app-layout>
