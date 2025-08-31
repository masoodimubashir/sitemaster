<x-app-layout>



    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }

        .badge-soft {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        .dashboard-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }


        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .search-container {
            background: #f8f9fc;
            border-radius: 15px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .search-container:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-container .form-control {
            border: none;
            background: transparent;
            box-shadow: none;
        }

        .search-container .input-group-text {
            border: none;
            background: transparent;
        }

        .btn-modern {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-modern.btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-modern.btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-modern.btn-outline-secondary {
            border: 2px solid #e3e6f0;
            color: #6c757d;
        }

        .btn-modern.btn-outline-secondary:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .table-modern {
            overflow: hidden;
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecf3 100%);
        }

        .table-modern thead th {
            border: none;
            font-weight: 700;
            color: #5a5c69;
            padding: 20px 15px;
            font-size: 0.875rem;
        }

        .table-modern tbody tr {
            border: none;
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
        }

        .table-modern tbody td {
            border: none;
            padding: 20px 15px;
            vertical-align: middle;
        }

        .site-link {
            font-weight: 700;
            color: #5a5c69;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .site-link:hover {
            color: #667eea;
        }

        .amount-positive {
            color: #1cc88a;
            font-weight: 700;
        }

        .amount-negative {
            color: #e74a3b;
            font-weight: 700;
        }

        .amount-neutral {
            color: #36b9cc;
            font-weight: 700;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.75rem;
            border: none;
        }

        .status-progress {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #8b4513;
        }

        .status-completed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .expand-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecf3 100%);
            color: #5a5c69;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .expand-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .breakdown-container {
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            border-radius: 15px;
            margin: 10px 0;
        }

        .breakdown-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .breakdown-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }

        .page-title {
            color: #5a5c69;
            font-weight: 800;
            margin-bottom: 2rem;
        }
    </style>



    <!-- Stats Overview -->
    <div class="row mb-5 g-4">
        <div class="col-md-4">
            <div class="dashboard-card primary h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 ">Sites Open</h6>
                        <h1 class="mb-0 fw-bold">{{ $ongoingSites ?? '1' }}</h1>
                    </div>
                    <div class="icon-circle">
                        <i class="fas fa-building fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card secondary h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 ">Closed Sites</h6>
                        <h1 class="mb-0 fw-bold">{{ $completedSites ?? '0' }}</h1>
                    </div>
                    <div class="icon-circle">
                        <i class="fas fa-lock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="success h-100 p-4">
                <div class="d-flex flex-column gap-3">
                    <a class="btn btn-success btn-modern text-decoration-none d-flex align-items-center justify-content-center"
                        href="{{ route('suppliers.dashboard') ?? '#' }}">
                        <i class="fas fa-exchange-alt me-2"></i> Switch Suppliers
                    </a>
                    <button class="btn btn-success btn-modern d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#create-site-modal">
                        <i class="fas fa-plus me-2"></i> Create Site
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sites Table -->
    <div class="row">
        <div class="col-12">
            <div class="table-modern">
                <!-- Search Filters -->
                <div class="p-4 bg-white">
                    <form method="GET" action="{{ url()->current() ?? '#' }}">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="search-container input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search for customers or sites..."
                                        value="{{ request('search') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-modern btn-success w-100">Search</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ url()->current() ?? '#' }}"
                                    class="btn btn-modern btn-outline-primary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Site</th>
                                <th>Client</th>
                                <th>Created</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>



                            <!-- Dynamic rows would go here in your actual implementation -->
                            @forelse ($sites as  $site)
                                @php
                                    $status = ($site->total_balance ?? 0) <= 0 ? 'Completed' : 'In Progress';
                                    $badgeClass = $status === 'Completed' ? 'status-completed' : 'status-progress';
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ url('/admin/sites/' . base64_encode($site->id)) }}"
                                            class="site-link">
                                            {{ $site->site_name }}
                                        </a>
                                        <div class="text-muted small mt-1">{{ $site->location ?? '' }}</div>
                                    </td>
                                    <td class="fw-semibold">{{ $site->client->name ?? '' }}</td>
                                    <td class="text-muted">{{ $site->created_at->format('M d, Y') ?? '' }}</td>
                                    <td class="text-end amount-positive">
                                        {{ number_format($site->total_paid ?? 0, 2) }}</td>
                                    <td class="text-end amount-negative">
                                        {{ number_format($site->total_due ?? 0, 2) }}</td>
                                    <td class="text-end amount-neutral">
                                        {{ number_format($site->total_balance ?? 0, 2) }}</td>
                                    <td><span class="status-badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="expand-btn" data-bs-toggle="collapse"
                                            data-bs-target="#breakdown-{{ $site->id }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Balance Breakdown -->
                                <tr class="collapse" id="breakdown-{{ $site->id }}">
                                    <td colspan="8" class="p-0">
                                        <div class="breakdown-container p-4">
                                            <div class="row g-3">
                                                @if (isset($site->balance_breakdown))
                                                    @foreach ($site->balance_breakdown as $key => $value)
                                                        <div class="col-md-2">
                                                            <div class="breakdown-card p-3 text-center">
                                                                <small
                                                                    class="text-muted text-uppercase d-block mb-1">{{ ucfirst($key) }}</small>
                                                                <div class="fw-bold">
                                                                    {{ number_format($value, 2) }}</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fs-1 mb-3 d-block text-muted"></i>
                                        <h5>No sites found</h5>
                                        <p class="mb-0">Try adjusting your search criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if (isset($sites) && $sites->hasPages())
                    <div class="p-4 bg-white border-top">
                        {{ $sites->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <!-- Create Site Modal -->
    <div id="create-site-modal" class="modal fade" aria-hidden="true" aria-labelledby="createSiteModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSiteModalLabel">Create New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Message Container for Success/Error Messages -->
                    <div id="messageContainer"></div>

                    <form id="createSiteForm" class="forms-sample material-form" enctype="multipart/form-data">
                        @csrf

                        <!-- Site Name -->
                        <div class="form-group">
                            <input type="text" name="site_name" id="site_name" />
                            <label for="site_name" class="control-label">Site Name</label><i class="bar"></i>
                            <div id="site_name-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" name="service_charge" id="service_charge" />
                            <label for="service_charge" class="control-label">Service Charge</label><i
                                class="bar"></i>
                            <div id="service_charge-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Contact No -->
                        <div class="form-group">
                            <input type="text" name="contact_no" id="contact_no" />
                            <label for="contact_no" class="control-label">Contact No</label><i class="bar"></i>
                            <div id="contact_no-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <input type="text" name="location" id="location" />
                            <label for="location" class="control-label">Location</label><i class="bar"></i>
                            <div id="location-error" class="error-message invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <!-- Multi-select for Engineers -->
                            <div class="col-md-6">
                                <div class="dropdown">

                                    <button class="form-control d-flex justify-content-between align-items-center"
                                        type="button" id="engineerDropdown" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <span id="selectedEngineersText" class="text-muted">
                                            @if (isset($site) && $site->users->count() > 0)
                                                {{ $site->users->pluck('name')->join(', ') }}
                                            @else
                                                Select Engineers
                                            @endif
                                        </span>
                                        <i class="fas fa-chevron-down text-secondary"></i>
                                    </button>


                                    <div id="engineer_ids-error" class="error-message invalid-feedback"></div>

                                    <div class="dropdown-menu w-100 p-0" aria-labelledby="engineerDropdown">
                                        <!-- Search -->
                                        <div class="p-2 border-bottom">
                                            <input type="text" class="form-control form-control-sm"
                                                id="engineerSearch" placeholder="Search engineers..."
                                                onkeyup="filterEngineers()">
                                        </div>

                                        <!-- Engineers List -->
                                        <div style="max-height: 200px; overflow-y: auto;">
                                            @foreach ($users as $user)
                                                <div class="engineer-option">
                                                    <div class="form-check px-3">
                                                        <input class="form-check-input engineer-checkbox"
                                                            type="checkbox" style="margin-left: 0px"
                                                            name="engineer_ids[]"
                                                            id="engineer_checkbox_{{ $user->id }}"
                                                            value="{{ $user->id }}"
                                                            @if (isset($site) && $site->users->contains($user->id)) checked @endif
                                                            onchange="updateSelectedEngineersText()">
                                                        <label class="form-check-label"
                                                            for="engineer_checkbox_{{ $user->id }}">
                                                            {{ $user->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Buttons -->
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm" onclick="clearAllEngineers()">
                                                <i class="fas fa-times me-1"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm"
                                                onclick="closeEngineerDropdown()">
                                                <i class="fas fa-check me-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Selection -->
                            <div class="col-md-6">
                                <select name="client_id" id="client_id" class="form-select text-black form-select-sm"
                                    style="cursor: pointer">
                                    <option value="" selected>Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <div id="client_id-error" class="error-message invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Form Buttons -->
                        <div class="d-flex justify-content-end align-items-center  mt-4">
                            <button type="button" class="btn btn-sm btn-secondary me-2"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-success" id="submitSiteBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                                <span class="submit-text">Create Site</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div id="messageContainer"></div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Auto-dismiss alerts after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);

                // Update selected engineers text
                function updateSelectedEngineersText() {
                    const checkboxes = document.querySelectorAll('.engineer-checkbox:checked');
                    const selectedText = document.getElementById('selectedEngineersText');

                    if (checkboxes.length === 0) {
                        selectedText.textContent = 'Select Engineers';
                    } else if (checkboxes.length <= 3) {
                        // Show names when 3 or fewer engineers are selected
                        const names = Array.from(checkboxes).map(checkbox => {
                            return checkbox.nextElementSibling.textContent.trim();
                        }).join(', ');
                        selectedText.textContent = names;
                    } else {
                        // Show count when more than 3 engineers are selected
                        selectedText.textContent = `${checkboxes.length} Engineers Selected`;
                    }
                }

                // Clear all selections
                function clearAllEngineers() {
                    document.querySelectorAll('.engineer-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    updateSelectedEngineersText();
                }

                // Close dropdown
                function closeEngineerDropdown() {
                    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('engineerDropdown'));
                    if (dropdown) {
                        dropdown.hide();
                    }
                }

                // Filter engineers list
                function filterEngineers() {
                    const term = document.getElementById('engineerSearch').value.toLowerCase();
                    document.querySelectorAll('.engineer-option').forEach(option => {
                        const label = option.querySelector('label').textContent.toLowerCase();
                        option.style.display = label.includes(term) ? 'block' : 'none';
                    });
                }

                // Make functions globally accessible
                window.updateSelectedEngineersText = updateSelectedEngineersText;
                window.clearAllEngineers = clearAllEngineers;
                window.closeEngineerDropdown = closeEngineerDropdown;
                window.filterEngineers = filterEngineers;

                // Initialize on page load
                updateSelectedEngineersText();

                // Form validation and submission
                $('#createSiteForm').submit(function(e) {
                    e.preventDefault();

                    const form = $(this);
                    const submitBtn = $('#submitSiteBtn');
                    const spinner = submitBtn.find('.spinner-border');
                    const submitText = submitBtn.find('.submit-text');

                    // Clear previous error messages
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').text('');
                    $('#messageContainer').html('');

                    // Validate form
                    if (form[0].checkValidity() === false) {
                        e.stopPropagation();
                        form.addClass('was-validated');
                        return;
                    }

                    // Show loading state
                    submitBtn.prop('disabled', true);
                    spinner.removeClass('d-none');
                    submitText.text('Creating...');

                    // Form data
                    const formData = new FormData(this);

                    // AJAX request
                    $.ajax({
                        url: '/admin/sites',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status) {
                                // Show success message
                                $('#messageContainer').html(`
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div>${response.message}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);

                                // Reset form and hide modal
                                form[0].reset();
                                form.removeClass('was-validated');
                                updateSelectedEngineersText(); // Reset engineer selection display

                                // Hide modal after showing success
                                setTimeout(() => {
                                    $('#create-site-modal').modal('hide');
                                }, 1000);

                                // Reload after delay
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'An error occurred. Please try again.';

                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;

                                $.each(errors, function(field, messages) {
                                    // Handle array fields like engineer_ids
                                    let fieldName = field;
                                    if (field.includes('.')) {
                                        fieldName = field.split('.')[0];
                                    }

                                    // Find the input element
                                    const input = form.find(
                                        `[name="${fieldName}"], [name="${fieldName}[]"], #${fieldName}`
                                    );

                                    // Add error class
                                    input.addClass('is-invalid');

                                    // Find the error container
                                    const errorContainer = form.find(`#${fieldName}-error`);
                                    if (errorContainer.length) {
                                        errorContainer.text(messages.join(' '));
                                        errorContainer.show();
                                    }
                                });

                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;

                                $('#messageContainer').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>${errorMsg}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                            }

                        },
                        complete: function() {
                            // Reset button state
                            submitBtn.prop('disabled', false);
                            spinner.addClass('d-none');
                            submitText.text('Create Site');
                        }
                    });
                });

                // Clear validation when modal is hidden
                $('#create-site-modal').on('hidden.bs.modal', function() {
                    const form = $('#createSiteForm');
                    form.removeClass('was-validated');
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').text('');
                    $('#messageContainer').html('');
                    form[0].reset();
                    updateSelectedEngineersText();
                });

                // Clear validation when modal is shown
                $('#create-site-modal').on('shown.bs.modal', function() {
                    $('#site_name').focus();
                });
            });
        </script>
    @endpush




</x-app-layout>
