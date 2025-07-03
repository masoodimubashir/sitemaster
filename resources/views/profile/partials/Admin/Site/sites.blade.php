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

    <x-breadcrumb :names="['Sites']" :urls="['admin/sites']" />

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
                                                        deleteMessage="Are you sure you want to delete this Site?" />

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
    <div id="create-site-modal" class="modal fade" tabindex="-1" aria-hidden="true"   data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-black">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Site
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createSiteForm" class="needs-validation" novalidate enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" required>
                            <div class="invalid-feedback">Please provide a site name.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="service_charge" class="form-label">Service Charge (%)</label>
                                <input type="number" class="form-control" id="service_charge" name="service_charge"
                                    min="0" step="0.01" required>
                                <div class="invalid-feedback">Please provide a valid service charge.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact_no" class="form-label">Contact No</label>
                                <input type="tel" class="form-control" id="contact_no" name="contact_no"
                                    required>
                                <div class="invalid-feedback">Please provide a contact number.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                            <div class="invalid-feedback">Please provide a location.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                
                                <select class="form-select form-select-sm text-black" style="cursor: pointer" id="user_id" name="user_id" required>
                                    <option value="" selected disabled>Select Engineer</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select an engineer.</div>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select form-select-sm text-black" style="cursor: pointer" id="client_id" name="client_id" required>
                                    <option value="" selected disabled>Select Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a client.</div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" id="submitSiteBtn">
                                <span class="submit-text">Create Site</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"
                                    aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);

            // Form validation and submission
            $('#createSiteForm').submit(function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = $('#submitSiteBtn');
                const spinner = submitBtn.find('.spinner-border');
                const submitText = submitBtn.find('.submit-text');

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
                            $('#create-site-modal').modal('hide');

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
                            form.find('.is-invalid').removeClass('is-invalid');

                            $.each(errors, function(field, messages) {
                                const input = form.find(`[name="${field}"]`);
                                const feedback = input.next('.invalid-feedback');

                                input.addClass('is-invalid');
                                if (feedback.length) {
                                    feedback.text(messages.join(' '));
                                } else {
                                    input.after(
                                        `<div class="invalid-feedback">${messages.join(' ')}</div>`
                                    );
                                }
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        $('#messageContainer').html(`
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div>${errorMsg}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                        submitText.text('Create Site');
                    }
                });
            });

            // Clear validation when modal is hidden
            $('#create-site-modal').on('hidden.bs.modal', function() {
                $('#createSiteForm').removeClass('was-validated');
                $('#createSiteForm').find('.is-invalid').removeClass('is-invalid');
            });
        });
    </script>
</x-app-layout>
