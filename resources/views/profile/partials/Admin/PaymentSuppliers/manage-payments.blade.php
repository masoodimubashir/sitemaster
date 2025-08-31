@php
    use Carbon\Carbon;
@endphp

<x-app-layout>

    {{-- Flash Messages --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container py-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Payment Management</h3>
            </div>
            <button class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-plus me-1"></i> Make Payment
            </button>
        </div>

        {{-- Filters Section --}}
        <form id="filterForm" method="GET" action="{{ url('admin/manage-payment') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted">From Date</label>
                    <input type="date" name="start_date" class="form-control shadow-sm"
                        value="{{ $start_date ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted">To Date</label>
                    <input type="date" name="end_date" class="form-control shadow-sm" value="{{ $end_date ?? '' }}">
                </div>
                <div class="col-md-3">

                    <a href="{{ url('admin/manage-payment') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Summary Card --}}
        <div class="row mb-4 mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Total Amount</small>
                            <h3 class="fw-bold text-primary mb-0">₹{{ number_format($total_amount) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-wallet text-white fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">Total Payments</small>
                            <h3 class="fw-bold text-success mb-0">{{ $payments->total() ?? $payments->count() }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-receipt text-white fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Payments Table --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                @if ($payments->count())
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">S.NO</th>
                                <th class="fw-semibold">Date</th>
                                <th class="fw-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $index => $payment)
                                <tr>
                                    <td class="text-muted">{{ $payments->firstItem() + $index }}</td>
                                    <td>
                                        <span
                                            class="fw-medium">{{ Carbon::parse($payment->created_at)->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">₹{{ number_format($payment->amount) }}</span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Pagination --}}
                    @if (method_exists($payments, 'links'))
                        <div class="card-footer bg-white border-0">
                            {{ $payments->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-receipt fa-3x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted">No Payments Found</h5>

                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Payment Modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <form id="paymentForm" enctype="multipart/form-data" class="modal-content shadow-sm border-0">
                @csrf
                <div class="modal-header  text-black">
                    <h5 class="modal-title"><i class="fas fa-plus me-1"></i> Add Payment</h5>
                </div>
                <div class="modal-body">

                    {{-- Date --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="created_at" class="form-control"
                            value="{{ Carbon::now()->format('Y-m-d') }}">
                        <div class="invalid-feedback d-block" id="error-created_at"></div>
                    </div>

                    <div>

                        {{-- Supplier --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supplier</label>
                            <select name="supplier_id" class="form-select text-black">
                                <option value="">Select Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="error-supplier_id"></div>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="amount" step="0.01" min="0" class="form-control">
                            </div>
                            <div class="invalid-feedback d-block" id="error-amount"></div>
                        </div>
                    </div>

                    {{-- Narration --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Narration</label>
                        <textarea name="narration" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>

                    {{-- Screenshot --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Screenshot (Optional)</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/*"
                            id="screenshotInput">
                        <small class="text-muted">JPG, PNG only, max 2MB</small>
                        <div id="screenshotPreview" class="mt-2"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="savePaymentBtn" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-fetch on date change
            $('input[name="start_date"], input[name="end_date"]').on('change', function() {
                $('#filterForm').submit();
            });

            // Preview Screenshot
            $('#screenshotInput').on('change', function(event) {
                const file = event.target.files[0];
                const preview = $('#screenshotPreview');
                preview.html('');
                if (file) {
                    const img = $('<img>').attr('src', URL.createObjectURL(file))
                        .addClass('img-thumbnail').css('maxHeight', '80px');
                    preview.append(img);
                }
            });

            // Save Payment AJAX
            $('#paymentForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $('#paymentForm .invalid-feedback').text('');
                $('#savePaymentBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

                $.ajax({
                    url: '/admin/payments',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        location.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(field) {
                                $('#error-' + field).text(errors[field][0]);
                            });
                        } else {
                            alert('Error: ' + (xhr.responseJSON?.message || 'Please try again.'));
                        }
                    },
                    complete: function() {
                        $('#savePaymentBtn').prop('disabled', false).html(
                            '<i class="fas fa-save me-1"></i> Save Payment');
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
