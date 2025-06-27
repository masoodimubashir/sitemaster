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
                    <div>Phase Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Phase Updated Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Phase Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'data')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Phase Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Phases']" :urls="['admin/phase']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header  py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-layer-group me-2 text-info"></i>
                            Phases 
                        </h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#phaseModal">
                            <i class="fas fa-plus me-1"></i>
                            Create Phase
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($phases))
                            <table class="table  align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold">Date</th>
                                        <th class="fw-bold">Phase Name</th>
                                        <th class="fw-bold">Site Name</th>
                                        <th class="fw-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($phases as $phase)
                                        <tr>
                                            <td>{{ $phase->created_at->format('D-m-Y') }}</td>
                                            <td class="fw-semibold">{{ $phase->phase_name }}</td>
                                            <td>{{ $phase->site->site_name }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <x-actions
                                                        editUrl="{{ url($user . '/phase/' . base64_encode( $phase->id)  . '/edit') }}"
                                                        deleteUrl="{{ url($user . '/phase/' . base64_encode( $phase->id) ) }}"
                                                        userType="{{ $user }}"
                                                        deleteMessage="Are you sure you want to delete this Phase?" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-layer-group fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Phases Found</h4>
                                    <p class="text-muted mb-4">There are no phase records available at the moment.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#phaseModal">
                                        <i class="fas fa-plus me-1"></i>
                                        Create New Phase
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($phases->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $phases->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Phase Modal -->
    <div class="modal fade" id="phaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-black">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Phase
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="phaseForm" class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="phase_name" class="form-label">Phase Name</label>
                            <input type="text" class="form-control" id="phase_name" name="phase_name" required>
                            <div class="invalid-feedback">Please provide a phase name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="site_id" class="form-label">Site</label>
                            <select class="form-select form-select-sm text-black" style="cursor: pointer;" id="site_id" name="site_id" required>
                                <option value="" selected disabled>Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ ucfirst($site->site_name) }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a site.</div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" id="submitPhaseBtn">
                                <span class="submit-text">Create Phase</span>
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
            $('#phaseForm').submit(function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = $('#submitPhaseBtn');
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

                // AJAX request
                $.ajax({
                    url: '{{ url('admin/phase') }}',
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
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
                        $('#phaseModal').modal('hide');

                        // Reload after delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
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
                        submitText.text('Create Phase');
                    }
                });
            });

            // Clear validation when modal is hidden
            $('#phaseModal').on('hidden.bs.modal', function() {
                $('#phaseForm').removeClass('was-validated');
                $('#phaseForm').find('.is-invalid').removeClass('is-invalid');
            });
        });
    </script>
</x-app-layout>
