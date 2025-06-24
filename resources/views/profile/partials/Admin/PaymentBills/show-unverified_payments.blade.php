<x-app-layout>
    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        <!-- Flash messages will appear here -->
    </div>

    <x-breadcrumb :names="['Verify Payments']" :urls="['admin/pay-verification']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-money-check-alt me-2 text-primary"></i>
                            Payment Verification
                        </h5>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($payments))
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold">Date</th>
                                        <th class="fw-bold">Amount</th>
                                        <th class="fw-bold">Supplier Name</th>
                                        <th class="fw-bold">Site</th>
                                        <th class="fw-bold">Site Owner</th>
                                        <th class="fw-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $pay)
                                        <tr>
                                            <td>{{ $pay->created_at->format('D-m-Y') }}</td>
                                            <td>{{ $pay->amount }}</td>
                                            <td>{{ $pay->supplier->name ?? '--' }}</td>
                                            <td>{{ $pay->site->site_name ?? '--' }}</td>
                                            <td>{{ $pay->site->site_owner_name }}</td>
                                            <td class="text-center">
                                                @if ($pay->verified_by_admin)
                                                    <a href="#" class="verify-link btn btn-sm btn-info"
                                                        data-name="pay" data-id="{{ $pay->id }}" data-verified="0"
                                                        data-bs-toggle="tooltip" title="Mark as Unverified">
                                                        <i class="fas fa-check-circle"></i> Verified
                                                    </a>
                                                @else
                                                    <a href="#" class="verify-link btn btn-sm btn-danger"
                                                        data-name="pay" data-id="{{ $pay->id }}" data-verified="1"
                                                        data-bs-toggle="tooltip" title="Verify Payment">
                                                        <i class="fas fa-question-circle"></i> Verify
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-money-bill-wave fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Payment Records Found</h4>
                                    <p class="text-muted mb-4">There are no payment records available for verification.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($payments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $payments->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
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

            // Verification toggle
            $('.verify-link').on('click', function(e) {
                e.preventDefault();

                const $link = $(this);
                const recordId = $link.data('id');
                const verifiedStatus = $link.data('verified');
                const recordName = $link.data('name');
                const spinner = $(
                    '<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>'
                    );

                // Show loading state
                $link.prop('disabled', true);
                $link.append(spinner);

                $.ajax({
                    url: '{{ url('admin/verify-payments') }}',
                    type: 'PUT',
                    data: {
                        id: recordId,
                        verified: verifiedStatus,
                        name: recordName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update button appearance
                            if (verifiedStatus === 1) {
                                $link.removeClass('btn-danger')
                                    .addClass('btn-info')
                                    .html('<i class="fas fa-check-circle"></i> Verified')
                                    .data('verified', 0)
                                    .attr('title', 'Mark as Unverified')
                                    .tooltip('dispose')
                                    .tooltip();
                            } else {
                                $link.removeClass('btn-info')
                                    .addClass('btn-danger')
                                    .html('<i class="fas fa-question-circle"></i> Verify')
                                    .data('verified', 1)
                                    .attr('title', 'Verify Payment')
                                    .tooltip('dispose')
                                    .tooltip();
                            }

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
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Unable to update verification status';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
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
                        $link.prop('disabled', false);
                        spinner.remove();
                    }
                });
            });
        });
    </script>
</x-app-layout>
