@php use App\Models\Site; @endphp
<x-app-layout>
    @if (session('status') === 'update')
        <x-success-message message="Your Record has been updated..."/>
    @endif

    @if (session('status') === 'delete')
        <x-success-message message="Your Record has been deleted..."/>
    @endif

    @if (session('status') === 'not_found')
        <x-success-message message="No Site Payments Available..."/>
    @endif

    @if (session('status') === 'error')
        <x-success-message message="Something went wrong! try again..."/>
    @endif

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 9999999;
        }
    </style>

    <x-breadcrumb :names="['Manage Payment']" :urls="['admin/manage-payment']"></x-breadcrumb>


    <div class="row">

        <div class="col-sm-12">

            <div class="d-flex justify-content-end">

                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#bankModal">
                    Add Payment
                </button>

            </div>


            <div class="table-responsive mt-4">


                @if (count($payment_banks))

                    <table class="table table-bordered">

                        <thead>

                        <tr>

                            <td colspan="1">
                                <div class="p-3 d-flex flex-column gap-2 text-danger">
                                    <small>
                                        <b>
                                            Total Aamount
                                        </b>
                                    </small>
                                    <h4 class="fw-bold">
                                        {{ Number::currency($total_amount, 'INR') }}
                                    </h4>
                                </div>
                            </td>

                            <td colspan="1">
                                <div class="p-3 d-flex flex-column gap-2 text-warning">
                                    <small>
                                        <b>
                                            Total Due
                                        </b>
                                    </small>
                                    <h4 class="fw-bold">
                                        {{-- {{ Number::currency($total_due, 'INR') }} --}}
                                    </h4>
                                </div>
                            </td>

                            <td>
                                <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">

                                    <small>
                                        <b>
                                            Effective Balance
                                        </b>
                                    </small>
                                    <h4>
                                        {{-- {{ Number::currency($effective_balance, 'INR') }} --}}
                                    </h4>

                                </div>
                            </td>

                            <td colspan="1">
                                <div class="p-3 d-flex flex-column gap-2 text-success">
                                    <small>
                                        <b>
                                            Total Paid
                                        </b>
                                    </small>
                                    <h4 class="fw-bold">
                                        {{-- {{ Number::currency($total_paid, 'INR') }} --}}
                                    </h4>
                                </div>
                            </td>

                        </tr>

                        <tr>
                            <th class="bg-info text-white fw-bold">Date</th>
                            <th class="bg-info text-white fw-bold">Entity</th>
                            <th class="bg-info text-white fw-bold">Amount</th>
                            <th class="bg-info text-white fw-bold">Edit</th>
                            <th class="bg-info text-white fw-bold">Make Payment</th>

                        </tr>

                        </thead>

                        <tbody>
                        @foreach ($payment_banks as $payment)
                            <tr>

                                <td>{{ $payment->created_at->format('d-m-Y') }}</td>
                                <td>
                                    {{
                                        $payment->entity_type  === Site::class ?
                                        $payment->entity->site_name  : $payment->entity->name
                                    }}
                                </td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ $payment->transaction_type === 1 ? 'Sent' : 'Received' }}</td>
                                <td class="space-x-4">

                                    <!-- Edit Button -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#bankModal"
                                       onclick="fetchPaymentData({{ $payment->id }})">
                                        <i
                                            class="fa-solid fa-edit text-xl text-primary bg-white rounded-full px-2 py-1"></i>
                                    </a>


                                </td>
                                <td class="space-x-4">

                                    <!-- Edit Button -->
                                    <button data-bs-toggle="modal" data-bs-target="#make_payment_modal"
                                            onclick="makePayment({{ $payment->id }})"
                                            class="badge badge-success rounded fw-bold">
                                         Pay  {{ $payment->entity->name ?? $payment->entity->site_name }}
                                    </button>


                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>

                @else

                    <table class="table table-bordered">
                        <thead></thead>
                        <tbody>
                        <tr>
                            <td class="text-danger fw-bold text-center">No Sites Found...</td>
                        </tr>
                        </tbody>
                    </table>

                @endif
            </div>

            <div class="mt-4">

                {{ $payment_banks->links() }}

            </div>

        </div>

    </div>


    <div id="messageContainer"></div>

    <!-- Modal -->
        <div id="bankModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bankModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="payment-bank-form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bankModalLabel">Manage Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Hidden Field for Payment ID -->

                            <!-- Amount -->
                            <div class="form-group">
                                <label for="modal_amount">Amount</label>
                                <input type="number" step="0.01" id="modal_amount" name="amount" class="form-control" />
                            </div>

                            <!-- Entity Dropdown -->
                            <div class="form-group">
                                <label for="modal_from">Entity</label>
                                <select id="modal_from" name="entity" class="form-select text-black" style="cursor: pointer">
                                    <option value="">Select Entity</option>
                                    @foreach ($entities as $entity)
                                        <option value="{{ $entity['type'] }}-{{ $entity['id'] }}">
                                            {{ $entity['id'] }} - {{ $entity['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transaction Type -->
                        <div class="container">
                            <div class="form-group">
                                <label class="form-label d-block">Transaction Type</label>
                                <div>
                                    <input type="radio" name="transaction_type" id="sent" value="1" checked>
                                    <label for="sent" class="form-check-label">Sent</label>
                                </div>
                                <div>
                                    <input type="radio" name="transaction_type" id="received" value="0">
                                    <label for="received" class="form-check-label">Received</label>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div id="make_payment_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="makePaymentModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form class="make_payment_form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="makePaymentModalLabel">Make Payment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Hidden Fields -->
                            <input type="hidden" id="payment_id" name="payment_id" />
                            <input type="hidden" id="transaction_type" name="transaction_type" />
                            <input type="hidden" id="entity_id" name="entity_id" />
                            <input type="hidden" id="entity_type" name="entity_type" />

                            <!-- Payment Amount -->
                            <div class="form-group mb-3">
                                <label for="payment_amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" id="payment_amount" name="amount" class="form-control"
                                       placeholder="Enter payment amount" required />
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-info btn-sm">Make Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>



        function fetchPaymentData(paymentId) {
            $.ajax({
                url: `/admin/manage-payment/${paymentId}/edit`, // Backend endpoint
                method: 'GET',
                success: function (response) {
                    $('#payment_id').val(response.id);
                    $('#modal_amount').val(response.amount);
                    $('#modal_from').val(`${response.entity_type}-${response.entity_id}`).change(); // Preselect entity

                    // Set transaction type
                    if (response.transaction_type === 1) {
                        $('#sent').prop('checked', true);
                    } else {
                        $('#received').prop('checked', true);
                    }

                    $('#bankModal').modal('show'); // Open the modal
                },
                error: function () {
                    alert('Failed to fetch payment details. Please try again.');
                }
            });
        }

        function makePayment(paymentId) {
            $.ajax({
                url: `/admin/manage-payment/${paymentId}/edit`,
                method: 'GET',
                success: function (response) {
                    $('#payment_id').val(response.id);
                    $('#transaction_type').val(response.transaction_type);
                    $('#entity_id').val(response.entity_id);
                    $('#entity_type').val(response.entity_type);
                    $('#payment_amount').val(response.amount || '');

                    $('#make_payment_modal').modal('show');
                },
                error: function () {
                    alert('Failed to fetch payment data. Please try again.');
                }
            });
        }

        $('#payment-bank-form').submit(function (event) {
            event.preventDefault();

            const messageContainer = $('#messageContainer');

            const payment_id = $('#payment_id').val();
            const formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            console.log(payment_id);

            const url = payment_id
                ? `/admin/manage-payment/${payment_id}`
                : '/admin/manage-payment';

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {

                    $('#bankModal').modal('hide');

                    messageContainer.append(`
                            <div class="alert align-items-center text-white bg-success border-0" role="alert">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                    </div>
                                </div>
                            </div>
                        `);

                    setTimeout(function () {
                        messageContainer.find('.alert').alert('close');
                        location.reload();
                    }, 2000);
                },
                error: function (error) {

                    if (error.status === 422) {
                        const errorMessages = error.responseJSON.errors;

                        for (const field in errorMessages) {
                            if (errorMessages.hasOwnProperty(field)) {
                                const errorElement = `
                                <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                    <strong>${errorMessages[field]}</strong>
                                </div>
                            `;
                                messageContainer.append(errorElement);
                            }
                        }
                    } else {

                        messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.
                        </div>
                    `);
                    }

                    setTimeout(function () {
                        messageContainer.find('.alert').alert('close');
                    }, 3000);
                }
            });
        });

        $('.make_payment_form').submit(function (event) {

            event.preventDefault();

            let formData = new FormData(this);
            formData.append('_method', 'PUT');


            const payment_id = $('#payment_id').val();

            const url = `/admin/payments/${payment_id}`;

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {

                    $('#make_payment_modal').modal('hide');
                },
                error: function (xhr) {
                    if (xhr.status === 422) {

                        const errors = xhr.responseJSON.errors;

                        for (const [field, messages] of Object.entries(errors)) {
                            alert(`${field}: ${messages.join(', ')}`);
                        }

                    } else {
                        alert('An error occurred while making the payment. Please try again.');
                    }
                }
            });

        });

        $('#bankModal').on('show.bs.modal', function () {
            $('#payment-bank-form')[0].reset();
            $('#payment_id').val('');
        });


    </script>
</x-app-layout>
