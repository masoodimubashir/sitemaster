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
                            <i class="fas fa-money-check-alt me-2 text-info"></i>
                            Payment Verification
                        </h5>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($payments))
                            <table class="table  align-middle">
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
                                            <td>{{ $pay->site->site_owner_name?? '--' }}</td>
                                            <td class="text-center">
                                                @if ($pay->verified_by_admin)
                                                    <a href="#" class="verify-link text-success" data-name="pay"
                                                        data-id="{{ $pay->id }}" data-verified="0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Payment Verified - Click to unverify">
                                                        <i class="fas fa-check-circle fs-5"></i>
                                                    </a>
                                                @else
                                                    <a href="#" class="verify-link text-warning" data-name="pay"
                                                        data-id="{{ $pay->id }}" data-verified="1"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Payment Pending  - Click to verify">
                                                        <i class="fas fa-question-circle fs-5"></i>
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
                                </div>
                            </div>
                        @endif
                    </div>

            

                    <!-- Style 2: Compact with Ellipsis -->
                    @if ($payments->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}
                                ({{ $payments->total() }} total results)
                            </div>

                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-compact mb-0">
                                    {{-- First Page --}}
                                    @if ($payments->currentPage() > 3)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $payments->url(1) }}">1</a>
                                        </li>
                                        @if ($payments->currentPage() > 4)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif

                                    {{-- Previous Page --}}
                                    @if ($payments->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="fas fa-angle-left"></i>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $payments->previousPageUrl() }}">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Current Page Range --}}
                                    @for ($i = max(1, $payments->currentPage() - 1); $i <= min($payments->lastPage(), $payments->currentPage() + 1); $i++)
                                        @if ($i == $payments->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $i }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $payments->url($i) }}">{{ $i }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    {{-- Next Page --}}
                                    @if ($payments->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $payments->nextPageUrl() }}">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="fas fa-angle-right"></i>
                                            </span>
                                        </li>
                                    @endif

                                    {{-- Last Page --}}
                                    @if ($payments->currentPage() < $payments->lastPage() - 2)
                                        @if ($payments->currentPage() < $payments->lastPage() - 3)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="{{ $payments->url($payments->lastPage()) }}">{{ $payments->lastPage() }}</a>
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
                                    .html('<i class="fas fa-check-circle fs-5"></i>')
                                    .data('verified', 0)
                                    .attr('title', '..')
                                    .tooltip('dispose')
                                    .tooltip();
                            } else {
                                $link.removeClass('btn-info')
                                    .html(
                                        '<i class="fas fa-question-circle fs-5 text-danger"></i>'
                                    )
                                    .data('verified', 1)
                                    .attr('title', '?')
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
