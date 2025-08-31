<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp
        <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update' || session('status') === 'verify')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Verification Updated</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Site Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error' || session('status') === 'hasPaymentRecords')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Site Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Sites']" :urls="['admin/sites']"/>

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header  py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-info"></i>
                            Sites
                        </h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#create-site-modal">
                            <i class="fas fa-plus me-1"></i>
                            Create Site
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($sites))
                            <table class="table  align-middle">
                                <thead class="bg-light">
                                <tr>
                                    <th class="fw-bold">Status</th>
                                    <th class="fw-bold">Date</th>
                                    <th class="fw-bold">Site Name</th>
                                    <th class="fw-bold">Location</th>
                                    <th class="fw-bold">Contact No</th>
                                    <th class="fw-bold">Owner</th>
                                    <th class="fw-bold">Service Charge</th>
                                    <th class="fw-bold text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($sites as $site)
                                    <tr>
                                        <td>
                                                <span class="badge bg-{{ $site->is_on_going ? 'success' : 'danger' }}">
                                                    {{ $site->is_on_going ? 'Open' : 'Closed' }}
                                                </span>
                                        </td>
                                        <td>{{ $site->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            @if ($site->is_on_going)
                                                <a href="{{ route('sites.show', [base64_encode($site->id)]) }}"
                                                   class="fw-bold text-primary text-decoration-none">
                                                    {{ ucfirst($site->site_name) }}
                                                </a>
                                            @else
                                                <span>{{ $site->site_name }}</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($site->location) }}</td>
                                        <td>
                                            <a href="tel:{{ $site->contact_no }}" class="text-decoration-none">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                +91-{{ $site->contact_no }}
                                            </a>
                                        </td>
                                        <td>{{ ucfirst($site->site_owner_name) }}</td>
                                        <td>{{ $site->service_charge }}%</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <x-actions
                                                    editUrl="{{ url($user . '/sites/' . $site->id . '/edit') }}"
                                                    deleteUrl="{{ url($user . '/sites/' . $site->id) }}"
                                                    userType="{{ $user }}"
                                                    deleteMessage="Are you sure you want to delete this Site?"/>

                                                <form action="{{ url('/admin/sites/update-on-going', $site->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="border-0 bg-transparent p-0"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $site->is_on_going ? 'Verified' : 'Verify' }}">
                                                        <i
                                                            class="fas fa-{{ $site->is_on_going ? 'check text-success' : 'hourglass text-warning' }} fs-5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class=" alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Sites Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($sites->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $sites->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Site Modal -->
    <div id="create-site-modal" class="modal fade" aria-hidden="true" aria-labelledby="createSiteModalLabel"
         data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="createSiteModalLabel">Create New Site</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Message Container for Success/Error Messages -->
                    <div id="messageContainer"></div>

                    <form id="createSiteForm" class="forms-sample material-form" enctype="multipart/form-data">
                        @csrf

                        <!-- Site Name -->
                        <div class="form-group">
                            <input type="text" name="site_name" id="site_name" aria-describedby="site_name-error"/>
                            <label for="site_name" class="control-label">Site Name</label><i class="bar"></i>
                            <div id="site_name-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Service Charge -->
                        <div class="form-group">
                            <input type="number" min="0" max="100" step="0.01" name="service_charge" id="service_charge"

                                   aria-describedby="service_charge-help service_charge-error"/>
                            <label for="service_charge" class="control-label">Service Charge (%)</label><i
                                class="bar"></i>
                            <div id="service_charge-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Contact No -->
                        <div class="form-group">
                            <input type="text" name="contact_no" id="contact_no" inputmode="numeric" pattern="\d{10}"
                                   maxlength="10"
                                   aria-describedby="contact_no-help contact_no-error"/>
                            <label for="contact_no" class="control-label">Contact No</label><i class="bar"></i>
                                <div id="contact_no-error" class="error-message invalid-feedback"></div>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <input type="text" name="location" id="location"
                                   aria-describedby="location-error"/>
                            <label for="location" class="control-label">Location</label><i class="bar"></i>
                            <div id="location-error" class="error-message invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <!-- Engineer Selection / Creation -->
                            <div class="col-md-6">
                                <!-- Mode switcher -->
                                <div class="btn-group w-100 mb-2" role="group" aria-label="Engineer Mode">
                                    <input type="radio" class="btn-check" name="engineer_mode" id="mode_select"
                                           value="select" autocomplete="off" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="mode_select"><i
                                            class="fas fa-users me-1"></i>Select Existing</label>

                                    <input type="radio" class="btn-check" name="engineer_mode" id="mode_create"
                                           value="create" autocomplete="off">
                                    <label class="btn btn-outline-secondary btn-sm" for="mode_create"><i
                                            class="fas fa-user-plus me-1"></i>Create New</label>
                                </div>

                                <!-- Select existing engineers dropdown -->
                                <div id="engineerSelectWrap" class="dropdown">
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
                                        <div class="p-2 border-bottom position-sticky top-0 bg-white"
                                             style="z-index: 1;">
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
                                                               @if (isset($site) && $site->users->contains($user->id)) checked
                                                               @endif
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
                                            <button type="button" class="btn btn-sm" onclick="closeEngineerDropdown()">
                                                <i class="fas fa-check me-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Create new engineer inline -->
                                <div id="engineerCreateWrap" class="border rounded p-2 d-none">
                                    <div class="form-group mb-2">
                                        <input type="text" name="new_engineer[name]" id="new_engineer_name"
                                               minlength="3"
                                               aria-describedby="new_engineer_name-error"/>
                                        <label for="new_engineer_name" class="control-label">Engineer Name</label><i
                                            class="bar"></i>
                                        <div id="new_engineer_name-error" class="error-message invalid-feedback"></div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="text" name="new_engineer[username]" id="new_engineer_username"
                                               minlength="6"
                                               aria-describedby="new_engineer_username-error"/>
                                        <label for="new_engineer_username" class="control-label">Username</label><i
                                            class="bar"></i>
                                        <div id="new_engineer_username-error"
                                             class="error-message invalid-feedback"></div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="password" name="new_engineer[password]" id="new_engineer_password"
                                                minlength="6"
                                               aria-describedby="new_engineer_password-error"/>
                                        <label for="new_engineer_password" class="control-label">Password</label><i
                                            class="bar"></i>
                                        <div id="new_engineer_password-error"
                                             class="error-message invalid-feedback"></div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="password" name="new_engineer[password_confirmation]"
                                               id="new_engineer_password_confirmation"
                                               minlength="6"
                                               aria-describedby="new_engineer_password_confirmation-error"/>
                                        <label for="new_engineer_password_confirmation" class="control-label">Confirm
                                            Password</label><i class="bar"></i>
                                        <div id="new_engineer_password_confirmation-error"
                                             class="error-message invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Client Selection -->
                            <div class="col-md-6">
                                <select name="client_id" id="client_id" class="form-select text-black form-select-sm"
                                        style="cursor: pointer"  aria-describedby="client_id-error">
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
                                    data-bs-dismiss="modal">Cancel
                            </button>
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
            $(document).ready(function () {
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

                // Engineer mode toggle
                function setEngineerMode(mode) {
                    if (mode === 'create') {
                        $('#engineerSelectWrap').addClass('d-none');
                        $('#engineerCreateWrap').removeClass('d-none');
                        // clear selected engineers
                        clearAllEngineers();
                    } else {
                        $('#engineerCreateWrap').addClass('d-none');
                        $('#engineerSelectWrap').removeClass('d-none');
                        // clear create fields
                        $('#new_engineer_name, #new_engineer_username, #new_engineer_password, #new_engineer_password_confirmation').val('');
                    }
                }

                $(document).on('change', 'input[name="engineer_mode"]', function () {
                    setEngineerMode($(this).val());
                });
                setEngineerMode($('input[name="engineer_mode"]:checked').val());

                // Form validation and submission
                $('#createSiteForm').submit(function (e) {
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
                        success: function (response) {
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
                                    window.location.reload();
                                }, 1000);

                                // Optionally, you can append the new site to the table without reload.
                                // For now, we just keep the success message and close the modal to avoid page reload as requested.
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = 'An error occurred. Please try again.';

                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;

                                $.each(errors, function (field, messages) {
                                    // Support nested and array fields. Laravel uses dot notation for nested fields.
                                    // Examples:
                                    //  - engineer_ids.0 => name="engineer_ids[]"
                                    //  - new_engineer.name => name="new_engineer[name]"
                                    let selector = '';
                                    let errorId = '';

                                    if (field.includes('.')) {
                                        const parts = field.split('.');
                                        if (parts.length === 2 && parts[0] === 'new_engineer') {
                                            // Map to bracket notation and specific error id
                                            selector = `[name="new_engineer[${parts[1]}]"]`;
                                            errorId = `#new_engineer_${parts[1]}-error`;
                                        } else if (parts[0] === 'engineer_ids') {
                                            // Any error on engineer_ids array
                                            selector = `[name="engineer_ids[]"]`;
                                            errorId = `#engineer_ids-error`;
                                        } else {
                                            // Fallback: look for base field
                                            selector = `#${parts[0]}`;
                                            errorId = `#${parts[0]}-error`;
                                        }
                                    } else {
                                        // Simple fields
                                        selector = `[name="${field}"], #${field}`;
                                        errorId = `#${field}-error`;
                                    }

                                    const input = form.find(selector);
                                    input.addClass('is-invalid');

                                    const errorContainer = form.find(errorId);
                                    if (errorContainer.length) {
                                        errorContainer.text(messages.join(' '));
                                        errorContainer.show();
                                    }
                                });

                                errorMsg = 'Please check the form for errors and try again.';
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
                        complete: function () {
                            // Reset button state
                            submitBtn.prop('disabled', false);
                            spinner.addClass('d-none');
                            submitText.text('Create Site');
                        }
                    });
                });

                // Clear validation when modal is hidden
                $('#create-site-modal').on('hidden.bs.modal', function () {
                    const form = $('#createSiteForm');
                    form.removeClass('was-validated');
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').text('');
                    $('#messageContainer').html('');
                    form[0].reset();
                    updateSelectedEngineersText();
                });

                // Clear validation when modal is shown
                $('#create-site-modal').on('shown.bs.modal', function () {
                    $('#site_name').focus();
                });
            });
        </script>
    @endpush


</x-app-layout>
