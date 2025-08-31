<x-app-layout>

    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        <!-- Flash messages will appear here -->
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editPaymentModalBody">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="savePaymentChanges">Save changes</button>
                </div>
            </div>
        </div>
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

                <!-- Filter Section -->
                <div class="card-body  mb-3">
                    <form method="GET" action="{{ url()->current() }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="site_id" class="form-label small fw-bold text-muted">Site</label>
                                <select class="form-select form-select-sm text-black bg-white" id="site_id"
                                    name="site_id">
                                    <option value="">All Sites</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"
                                            {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                            {{ $site->site_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="phase_id" class="form-label small fw-bold text-muted">Phase</label>
                                <select class="form-select form-select-sm text-black bg-white" id="phase_id"
                                    name="phase_id">
                                    <option value="">All Phases</option>
                                    @if (request('site_id'))
                                        @foreach ($sites->firstWhere('id', request('site_id'))->phases ?? [] as $phase)
                                            <option value="{{ $phase->id }}"
                                                {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                                {{ $phase->phase_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="supplier_id" class="form-label small fw-bold text-muted">Supplier</label>
                                <select class="form-select form-select-sm text-black bg-white" id="supplier_id"
                                    name="supplier_id">
                                    <option value="">All Suppliers</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="date_from" class="form-label small fw-bold text-muted">From Date</label>
                                <input type="date" class="form-control form-control-sm" id="date_from"
                                    name="date_from" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="date_to" class="form-label small fw-bold text-muted">To Date</label>
                                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to"
                                    value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                    <i class="fas fa-times me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($payments))
                            <table class="table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Bill</th>
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
                                            <td>
                                                @if ($pay->screenshot)
                                                    <img src="{{ asset('storage/' . $pay->screenshot) }}"
                                                        style="max-width: 100px; max-height: 100px;"
                                                        class="img-thumbnail">
                                                @else
                                                    <span>No Bill found</span>
                                                @endif
                                            </td>
                                            <td>{{ $pay->created_at->format('D-m-Y') }}</td>
                                            <td>{{ $pay->amount }}</td>
                                            <td>{{ $pay->supplier->name ?? '--' }}</td>
                                            <td>{{ $pay->site->site_name ?? '--' }}</td>
                                            <td>{{ $pay->site->site_owner_name ?? '--' }}</td>
                                            <td class="text-center">
                                                @if ($user === 'admin')
                                                    <div class="d-flex justify-content-center gap-2">
                                                        @if ($pay->verified_by_admin)
                                                            <a href="#" class="verify-link text-success"
                                                                data-name="pay" data-id="{{ $pay->id }}"
                                                                data-verified="0" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Payment Verified - Click to unverify">
                                                                <i class="fas fa-check-circle fs-5"></i>
                                                            </a>
                                                        @else
                                                            <a href="#" class="verify-link text-warning"
                                                                data-name="pay" data-id="{{ $pay->id }}"
                                                                data-verified="1" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Payment Pending - Click to verify">
                                                                <i class="fas fa-question-circle fs-5"></i>
                                                            </a>
                                                        @endif

                                                        <!-- Edit Button -->
                                                        <a href="#" class="text-primary edit-payment"
                                                            data-id="{{ $pay->id }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="Edit Payment">
                                                            <i class="fas fa-edit fs-5"></i>
                                                        </a>

                                                        <!-- Delete Button -->
                                                        <a href="#" class="text-danger delete-payment"
                                                            data-id="{{ $pay->id }}" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" title="Delete Payment">
                                                            <i class="fas fa-trash-alt fs-5"></i>
                                                        </a>
                                                    </div>
                                                @else
                                                    @if ($user === 'user')
                                                        <button
                                                            class="btn btn-sm btn-outline-primary upload-screenshot"
                                                            data-payment-id="{{ $pay->id }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#uploadScreenshotModal">
                                                            <i class="fas fa-upload me-1"></i> Upload
                                                        </button>
                                                    @endif
                                            <td>
                                        </tr>
                                    @endif
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

                    <!-- Pagination -->
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

    <!-- Upload Screenshot Modal -->
    <div class="modal fade" id="uploadScreenshotModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Payment Screenshot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadScreenshotForm" enctype="multipart/form-data">
                        @csrf

                        

                        <input type="hidden" name="payment_id" id="payment_id">
                        <div class="mb-3">
                            <label for="screenshot" class="form-label">Select Image</label>
                            <input class="form-control" type="file" id="screenshot" name="screenshot"
                                accept="image/*" required>
                        </div>
                        <div class="progress mb-3 d-none" id="uploadProgress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="submitScreenshot">Upload</button>
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

                // Load phases when site changes
                $('#site_id').on('change', function() {
                    const siteId = $(this).val();
                    const phaseSelect = $('#phase_id');

                    if (siteId) {
                        $.get(`/admin/sites/${siteId}/phases`, function(data) {
                            phaseSelect.empty().append('<option value="">All Phases</option>');
                            data.forEach(phase => {
                                phaseSelect.append(
                                    `<option value="${phase.id}">${phase.name}</option>`);
                            });
                        });
                    } else {
                        phaseSelect.empty().append('<option value="">All Phases</option>');
                    }
                });

                // Initialize phase select if site is already selected
                @if (request('site_id'))
                    $('#site_id').trigger('change');
                @endif

                // Verification toggle
                $('.verify-link').on('click', function(e) {
                    e.preventDefault();
                    handleVerification($(this));
                });

                // Edit Payment
                $('.edit-payment').on('click', function(e) {
                    e.preventDefault();
                    const paymentId = $(this).data('id');
                    const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));

                    // Show loading state
                    $('#editPaymentModalBody').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading payment details...</p>
                    </div>
                `);

                    // Load edit form via AJAX
                    $.get(`/admin/pay-verification/${paymentId}/edit`, function(data) {
                        $('#editPaymentModalBody').html(data);
                        modal.show();
                    }).fail(function() {
                        $('#editPaymentModalBody').html(`
                        <div class="alert alert-danger">
                            Failed to load payment details. Please try again.
                        </div>
                    `);
                    });
                });

                // Save Payment Changes
                $('#savePaymentChanges').on('click', function() {
                    const form = $('#editPaymentForm');
                    const formData = new FormData(form[0]);
                    const paymentId = form.find('input[name="id"]').val();

                    $(this).prop('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving...
                `);

                    $.ajax({
                        url: `/admin/pay-verification/${paymentId}`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showMessage('success', response.message);
                            $('#editPaymentModal').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to update payment';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showMessage('danger', errorMsg);
                        },
                        complete: function() {
                            $('#savePaymentChanges').prop('disabled', false).text('Save changes');
                        }
                    });
                });

                // Delete Payment with SweetAlert
                $('.delete-payment').on('click', function(e) {
                    e.preventDefault();
                    const paymentId = $(this).data('id');
                    const paymentRow = $(this).closest('tr');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/admin/pay-verification/${paymentId}`,
                                type: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                beforeSend: function() {
                                    paymentRow.css('opacity', '0.5');
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        paymentRow.fadeOut(400, function() {
                                            $(this).remove();
                                            // Reload if last item
                                            if ($('tbody tr').length ===
                                                0) {
                                                location.reload();
                                            }
                                        });
                                    });
                                },
                                error: function(xhr) {
                                    paymentRow.css('opacity', '1');
                                    let errorMsg = 'Failed to delete payment';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    Swal.fire(
                                        'Error!',
                                        errorMsg,
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });

                // Helper function for verification
                function handleVerification($link) {
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
                                    $link.removeClass('text-warning').addClass('text-success')
                                        .html('<i class="fas fa-check-circle fs-5"></i>')
                                        .data('verified', 0)
                                        .attr('title', 'Payment Verified - Click to unverify')
                                        .tooltip('dispose')
                                        .tooltip();
                                } else {
                                    $link.removeClass('text-success').addClass('text-warning')
                                        .html('<i class="fas fa-question-circle fs-5"></i>')
                                        .data('verified', 1)
                                        .attr('title', 'Payment Pending - Click to verify')
                                        .tooltip('dispose')
                                        .tooltip();
                                }

                                showMessage('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = 'Unable to update verification status';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showMessage('danger', errorMsg);
                        },
                        complete: function() {
                            $link.prop('disabled', false);
                            spinner.remove();
                        }
                    });
                }


                // Set payment ID when upload button clicked
                $('.upload-screenshot').on('click', function() {
                    $('#payment_id').val($(this).data('payment-id'));
                });

                // Handle screenshot upload
                $('#submitScreenshot').on('click', function() {
                    const form = $('#uploadScreenshotForm')[0];
                    const formData = new FormData(form);
                    const progressBar = $('#uploadProgress');

                    progressBar.removeClass('d-none');

                    $.ajax({
                        url: '/{{ $user }}/pay-verification/upload-screenshot',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    progressBar.find('.progress-bar').css('width', percent +
                                        '%').text(percent + '%');
                                }
                            });
                            return xhr;
                        },
                        success: function(response) {
                            $('#uploadScreenshotModal').modal('hide');
                            showMessage('success', 'Screenshot uploaded successfully');
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to upload screenshot';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showMessage('danger', errorMsg);
                        },
                        complete: function() {
                            progressBar.addClass('d-none');
                        }
                    });
                });
            });

            // Helper function to show messages
            function showMessage(type, message) {
                $('#messageContainer').html(`
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                            <div>${message}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        </script>
    @endpush


</x-app-layout>
