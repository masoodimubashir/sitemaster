@php use App\Models\Site; @endphp
<x-app-layout>
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
                            <th class="bg-info text-white fw-bold">Actions</th>
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
                                <td class="space-x-4">

                                    <!-- Edit Button -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#bankModal"
                                       onclick="fetchPaymentData({{ $payment->id }})">
                                        <i
                                            class="fa-solid fa-edit text-xl text-primary bg-white rounded-full px-2 py-1"></i>
                                    </a>


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

                    </div>
                    <div class="modal-body">

                        <input type="hidden" id="payment_id" name="payment_id"/>

                        <div class="form-group">
                            <label for="modal_amount">Amount</label>
                            <input type="number" step="0.01" id="modal_amount" name="amount" class="form-control"/>
                        </div>

                        <div class="form-group">
                            <select id="modal_from" name="entity" class="form-select">
                                <option value="">Select Entity</option>
                                @foreach ($entities as $entity)
                                    <option value="{{ $entity['type'] }}-{{ $entity['id'] }}">
                                        {{ $entity['id'] }} - {{ $entity['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <script>

        const is_out_going = false;
        const is_in_coming = false;

        function fetchPaymentData(paymentId) {
            $.ajax({
                url: `/admin/manage-payment/${paymentId}/edit`,
                method: 'GET',
                success: function (response) {

                    $('#payment_id').val(response.id);
                    $('#modal_amount').val(response.amount);

                    $('#payment_type').val(response.payment_type).change();

                    // On edit, reset the dropdown to the first option
                    $('#modal_from').val("").change();



                    // Open the modal
                    $('#bankModal').modal('show');
                },
                error: function () {
                    messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.
                        </div>
                    `);

                    setTimeout(function () {
                        messageContainer.find('.alert').alert('close');
                    }, 3000);

                }
            });
        }

        $(document).ready(function () {

            $('#bankModal').on('show.bs.modal', function () {
                $('#payment-bank-form')[0].reset();
                $('#payment_id').val('');
            });

            $('#payment-bank-form').submit(function (event) {
                event.preventDefault();

                const messageContainer = $('#messageContainer');

                const payment_id = $('#payment_id').val();
                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');

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
        });
    </script>
</x-app-layout>
