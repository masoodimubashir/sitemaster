@php
    use App\Models\Site;
    use Carbon\Carbon;

    $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
@endphp

<x-app-layout>
    <!-- Flash Messages -->
    <div class="flash-messages">
        @if (session('status') === 'update')
            <x-success-message message="Your Record has been updated..." />
        @endif

        @if (session('status') === 'delete')
            <x-success-message message="Your Record has been deleted..." />
        @endif

        @if (session('status') === 'not_found')
            <x-success-message message="No Site Payments Available..." />
        @endif

        @if (session('status') === 'error')
            <x-success-message message="Something went wrong! try again..." />
        @endif
    </div>

    <style>
        .stat-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }

        .payment-actions .btn {
            min-width: 100px;
        }

        #messageContainer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }

        .modal-payment {
            border-radius: 15px;
            overflow: hidden;
        }

        .badge-transaction {
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            border-radius: 50px;
        }
    </style>

    <x-breadcrumb :names="['Manage Payment']" :urls="['admin/manage-payment']"></x-breadcrumb>

    <div>
        <div class="row">
            <div class="col-12">
                <div class="mb-4 border-0">
                    <div class="py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-money-bill-transfer me-2 text-primary"></i>
                                Payment Transactions
                            </h5>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#createPaymentModal">
                                <i class="fas fa-plus me-1"></i> New Payment
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4 g-4">
                            <div class="col-md-3">
                                <div class="stat-card bg-white p-3 border-start border-4 border-danger">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Amount</h6>
                                            <h3 class="fw-bold text-danger">{{ Number::currency($total_amount, 'INR') }}
                                            </h3>
                                        </div>
                                        <i class="fas fa-wallet fa-2x text-danger opacity-25"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="stat-card bg-white p-3 border-start border-4 border-warning">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Sent</h6>
                                            <h3 class="fw-bold text-warning">
                                                {{ Number::currency($payments->where('transaction_type', 1)->sum('amount'), 'INR') }}
                                            </h3>
                                        </div>
                                        <i class="fas fa-arrow-up fa-2x text-warning opacity-25"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="stat-card bg-white p-3 border-start border-4 border-success">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Received</h6>
                                            <h3 class="fw-bold text-success">
                                                {{ Number::currency($payments->where('transaction_type', 2)->sum('amount'), 'INR') }}
                                            </h3>
                                        </div>
                                        <i class="fas fa-arrow-down fa-2x text-success opacity-25"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="stat-card bg-white p-3 border-start border-4 border-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Transactions</h6>
                                            <h3 class="fw-bold text-info">{{ $payments->total() }}</h3>
                                        </div>
                                        <i class="fas fa-exchange-alt fa-2x text-info opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="table-responsive rounded">
                            @if (count($payments))
                                <table class="table payment-table align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="fw-bold ps-4">Date</th>
                                            <th class="fw-bold">Entity</th>
                                            <th class="fw-bold">Name</th>
                                            <th class="fw-bold">Type</th>
                                            <th class="fw-bold text-end">Amount</th>
                                            <th class="fw-bold text-center pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payments as $payment)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-bold">{{ Carbon::parse($payment['created_at'])->format('d M Y') }}</span>

                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge-transaction bg-{{ $payment['entity_type'] === Site::class ? 'info' : 'primary' }}-light text-{{ $payment['entity_type'] === Site::class ? 'info' : 'primary' }}">
                                                        {{ $payment['entity_type'] === Site::class ? 'Site' : 'Supplier' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold">
                                                        {{ ucfirst($payment['entity_type'] === Site::class ? $payment['entity']->site_name : $payment['entity']->name) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge-transaction bg-{{ $payment['transaction_type'] === 1 ? 'warning' : 'success' }}-light text-{{ $payment['transaction_type'] === 1 ? 'warning' : 'success' }}">
                                                        <i
                                                            class="fas fa-{{ $payment['transaction_type'] === 1 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                        {{ $payment['transaction_type'] === 1 ? 'Sent' : 'Received' }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="text-end fw-bold {{ $payment['transaction_type'] === 1 ? 'text-warning' : 'text-success' }}">
                                                    {{ Number::currency($payment['amount'], 'INR') }}
                                                </td>
                                                <td class="text-center pe-4 payment-actions">
                                                    <button data-bs-toggle="modal" data-bs-target="#payment_model"
                                                        onclick="makePayment(
                                                            {{ $payment['id'] }},
                                                            '{{ $payment['entity']->id }}',
                                                            '{{ $payment['entity_type'] == Site::class ? 'site' : 'supplier' }}',
                                                            '{{ $payment['amount'] }}',
                                                            '{{ $payment['transaction_type'] }}'
                                                        )"
                                                        class="btn btn-sm btn-{{ $payment['transaction_type'] === 1 ? 'warning' : 'success' }}">
                                                        Pay
                                                    </button>

                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-light text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-money-bill-wave fa-4x text-muted mb-4"></i>
                                        <h4 class="text-muted">No Payment Transactions Found</h4>
                                        <p class="text-muted mb-4">There are no payment records available at the moment.
                                        </p>
                                        <button class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createPaymentModal">
                                            <i class="fas fa-plus me-1"></i> Create New Payment
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Pagination -->
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
    </div>

    <!-- Payment Modal -->
    <div id="payment_model" class="modal fade" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-payment">
                <form class="payment_form">
                    <div class="modal-header  text-black">
                        <h5 class="modal-title" id="paymentModalLabel">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Process Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden Fields -->
                        <input type="hidden" id="payment_id" name="payment_id">
                        <input type="hidden" id="transaction_type" name="transaction_type">
                        <input type="hidden" id="entity_id" name="entity_id">
                        <input type="hidden" id="entity_type" name="entity_type">

                        <!-- Payment Details -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Transaction Type:</span>
                                <span id="display_transaction_type" class="badge bg-warning text-white"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Entity:</span>
                                <span id="display_entity_type" class="badge bg-info"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Original Amount:</span>
                                <span id="display_original_amount" class="fw-bold"></span>
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Amount -->
                        <div class="form-group mb-3">
                            <label for="payment_amount" class="form-label fw-bold">Payment Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" id="payment_amount" name="amount"
                                    class="form-control" placeholder="Enter payment amount">
                            </div>
                            <small class="text-muted">Enter the amount to be processed</small>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i>
                            Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Message Container -->
    <div id="messageContainer"></div>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Entity type change handler for create payment form
            $('#entity_type_new').change(function() {
                const entityType = $(this).val();
                const entitySelect = $('#entity_id_new');

                if (entityType) {
                    // Clear existing options
                    entitySelect.empty().append('<option value="">Loading...</option>');

                    // Fetch entities based on type
                    $.get(`/admin/get-entities/${entityType}`, function(data) {
                        entitySelect.empty().append('<option value="">Select ' + entityType.charAt(
                            0).toUpperCase() + entityType.slice(1) + '</option>');

                        data.forEach(entity => {
                            const name = entityType === 'site' ? entity.site_name : entity
                                .name;
                            entitySelect.append(
                                `<option value="${entity.id}">${name}</option>`);
                        });
                    });
                } else {
                    entitySelect.empty().append('<option value="">Select Entity</option>');
                }
            });
        });

        function makePayment(id, entity_id, entityType, amount, transactionType) {
            $('#payment_id').val(id);
            $('#entity_id').val(entity_id);
            $('#entity_type').val(entityType);
            $('#payment_amount').val(amount);
            $('#transaction_type').val(transactionType);

            // Update display fields
            $('#display_transaction_type').text(transactionType === '1' ? 'Payment Sent' : 'Payment Received');
            $('#display_entity_type').text(entityType === 'site' ? 'Site' : 'Supplier');
            $('#display_original_amount').text('₹' + parseFloat(amount).toFixed(2));

            // Set badge colors
            $('#display_transaction_type').removeClass('bg-warning bg-success')
                .addClass(transactionType === '1' ? 'bg-warning' : 'bg-success');
        }

        $('.payment_form').submit(function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const submitBtn = $(this).find('[type="submit"]');

            // Disable button and show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

            $.ajax({
                url: '/admin/payments',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    displayAlert('success', response.message);
                    $('#payment_model').modal('hide');

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred. Please try again.';

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    displayAlert('danger', errorMessage);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(
                        '<i class="fas fa-paper-plane me-1"></i> Submit Payment');
                }
            });
        });

        function displayAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            $('#messageContainer').append(alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    </script>
</x-app-layout>
