<x-app-layout>


    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }
    </style>

    <div class="row">
        <!-- Stats Overview -->
        <div class="row mb-4">
            <!-- Statistics Cards -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Sites Open</h6>
                                        <h2 class="mb-0">{{ $ongoingSites }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                        <i class="fas fa-building fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Closed Sites</h6>
                                        <h2 class="mb-0">{{ $completedSites }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-danger rounded-circle p-3">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="d-flex flex-column h-100 gap-2">
                    <a class="btn btn-outline-info btn-sm w-100 d-flex align-items-center justify-content-center"
                        href="{{ route('suppliers.dashboard') }}">
                        <i class="fas fa-exchange-alt me-2"></i> Switch Suppliers
                    </a>
                    <button class="btn  btn-success btn-sm w-100 d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#create-site-modal">
                        <i class="fas fa-plus me-2"></i> Create Site
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Search and Filters -->
                    <div class="p-3 ">
                        <form method="GET" action="{{ url()->current() }}">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search for customers or sites..."
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Search</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ url()->current() }}"
                                        class="btn btn-sm btn-outline-secondary text-black w-100">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sites List -->
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Site Name</th>
                                    <th>Client</th>
                                    <th>Created</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sites as $site)
                                    @php
                                        $baseCost = 0;
                                        foreach ($site->phases as $phase) {
                                            $baseCost +=
                                                ($phase->total_material_billing ?? 0) +
                                                ($phase->total_square_footage ?? 0) +
                                                ($phase->total_daily_expenses ?? 0) +
                                                ($phase->total_labour_cost ?? 0) +
                                                ($phase->total_wasta_cost ?? 0);
                                        }

                                        $servicePercentage = $site->service_charge ?? 0;
                                        $serviceAmount = ($baseCost * $servicePercentage) / 100;
                                        $totalCost = $baseCost + $serviceAmount;

                                        $paid = $site->total_payments ?? 0;
                                        $balance = $totalCost - $paid;
                                        $status = $balance <= 0 ? 'Completed' : 'In Progress';
                                    @endphp

                                    <tr>
                                        <td>
                                            <a href="{{ url('/admin/sites/' . base64_encode($site->id)) }}"
                                                class="fw-bold text-decoration-none">
                                                {{ $site->site_name }}
                                            </a>
                                        </td>
                                        <td>{{ $site->client->name }}</td>
                                        <td>{{ $site->created_at->diffForHumans() }}</td>
                                        <td class="text-end text-success fw-bold">{{ number_format($paid, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($balance, 2) }}</td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($sites->hasPages())
                        <div class="p-3 border-top">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($sites->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $sites->previousPageUrl() }}"
                                                rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($sites->getUrlRange(1, $sites->lastPage()) as $page => $url)
                                        @if ($page == $sites->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($sites->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $sites->nextPageUrl() }}"
                                                rel="next">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">&raquo;</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="messageContainer"></div>

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
                                            <button type="button" class="btn btn-sm"
                                                onclick="clearAllEngineers()">
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
