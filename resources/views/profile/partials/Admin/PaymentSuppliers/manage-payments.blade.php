@php
    use App\Models\Site;
    use Carbon\Carbon;

    $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

@endphp


{{--<select style="cursor: pointer"--}}
{{--        class="bg-white text-black form-select form-select-sm" name="wager_id"--}}
{{--        onchange="document.getElementById('filterForm').submit();">--}}
{{--    <option value="all" {{ request('wager_id') === 'all' ? 'selected' : '' }}>--}}
{{--        All Wagers--}}
{{--    </option>--}}
{{--    @foreach ($wagers as $wager)--}}
{{--        <option value="{{ $wager['wager_id'] }}"--}}
{{--            {{ request('wager_id') == $wager['wager_id'] ? 'selected' : '' }}>--}}
{{--            {{ $wager['description'] }}--}}
{{--        </option>--}}
{{--    @endforeach--}}
{{--</select>--}}

{{--<select style="cursor: pointer"--}}
{{--        class="bg-white text-black form-select form-select-sm" name="supplier_id"--}}
{{--        onchange="document.getElementById('filterForm').submit();">--}}
{{--    <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>--}}
{{--        All Suppliers--}}
{{--    </option>--}}
{{--    @foreach ($suppliers as $supplier)--}}
{{--        <option value="{{ $supplier['supplier_id'] }}"--}}
{{--            {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>--}}
{{--            {{ $supplier['supplier'] }}--}}
{{--        </option>--}}
{{--    @endforeach--}}
{{--</select>--}}

{{--<select style="cursor: pointer"--}}
{{--        class="bg-white text-black form-select form-select-sm" name="date_filter"--}}
{{--        id="date_filter" onchange="document.getElementById('filterForm').submit();">--}}
{{--    <option value="today"--}}
{{--        {{ request('date_filter') === 'today' ? 'selected' : '' }}>--}}
{{--        Today</option>--}}
{{--    <option value="yesterday"--}}
{{--        {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>--}}
{{--        Yesterday</option>--}}
{{--    <option value="this_week"--}}
{{--        {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>--}}
{{--        This Week</option>--}}
{{--    <option value="this_month"--}}
{{--        {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>--}}
{{--        This Month</option>--}}
{{--    <option value="this_year"--}}
{{--        {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>--}}
{{--        This Year</option>--}}
{{--    <option value="lifetime"--}}
{{--        {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>--}}
{{--        All Data--}}
{{--    </option>--}}
{{--</select>--}}


{{--</div>--}}


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

{{--            <div class="row mt-4 mb-4">--}}
{{--                <div class="col-12 col-md-10 d-flex flex-column flex-md-row gap-2 align-items-center">--}}

{{--                    <form class="d-flex flex-column flex-md-row gap-2 w-100"--}}
{{--                          action="{{ url($user . '/manage-payment') }}" method="GET" id="filterForm">--}}

{{--                        <select style="cursor: pointer" onchange="document.getElementById('filterForm').submit();"--}}
{{--                                class="bg-white text-black form-select form-select-sm" name="id">--}}
{{--                            <option value="all" {{ request('id') === 'all' ? 'selected' : '' }}>--}}
{{--                                Select Site--}}
{{--                            </option>--}}
{{--                            @foreach ($sites as $data)--}}
{{--                                <option--}}
{{--                                    value="site-{{ $data['id'] }}" {{ request('id') == 'site-' . $data['id'] ? 'selected' : '' }}>--}}
{{--                                    {{ $data['site_name'] }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}

{{--                        <select style="cursor: pointer" onchange="document.getElementById('filterForm').submit();"--}}
{{--                                class="bg-white text-black form-select form-select-sm" name="id">--}}
{{--                            <option value="all" {{ request('id') === 'all' ? 'selected' : '' }}>--}}
{{--                                Select Supplier--}}
{{--                            </option>--}}
{{--                            @foreach ($suppliers as $data)--}}
{{--                                <option--}}
{{--                                    value="supplier-{{ $data['id'] }}" {{ request('id') == 'supplier-' . $data['id'] ? 'selected' : '' }}>--}}
{{--                                    {{ $data['name'] }}--}}
{{--                                </option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}

{{--                    </form>--}}

{{--                </div>--}}

{{--            </div>--}}

            {{--            <div class="d-flex justify-content-end">--}}

            {{--                <button onclick="createNewPayment()" class="btn btn-info">Make Payment</button>--}}


            {{--            </div>--}}


            <div class="table-responsive mt-4">


                @if (count($payments))

                    <table class="table table-bordered">

                        <thead>

                        <tr>

                            <td colspan="1">
                                <div class="p-3 d-flex flex-column gap-2 text-danger">
                                    <small>
                                        <h4>
                                            Total Amount
                                        </h4>
                                    </small>
                                    <h4 class="fw-bold">
                                        {{ Number::currency($total_amount, 'INR') }}
                                    </h4>
                                </div>
                            </td>

                        </tr>

                        <tr>
                            <th class="bg-info text-white fw-bold">Date</th>
                            <th class="bg-info text-white fw-bold">Entity</th>
                            <th class="bg-info text-white fw-bold">Name</th>
                            <th class="bg-info text-white fw-bold">Transaction Type</th>
                            <th class="bg-info text-white fw-bold">Amount</th>
                            <th class="bg-info text-white fw-bold">Make Payment</th>

                        </tr>

                        </thead>

                        <tbody>

                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ Carbon::parse($payment['created_at'])->format('d-m-Y') }}</td>
                                <td>{{ $payment['entity_type'] === Site::class ? 'Site' : 'Supplier' }}</td>
                                <td>{{ ucfirst( $payment['entity_type'] === Site::class ? $payment['entity']->site_name : $payment['entity']->name) }}</td>

                                <td>{{ $payment['transaction_type'] === 1 ? 'Sent' : 'Received' }}</td>
                                <td>{{ $payment['amount'] }}</td>

                                <td>
                                    <!-- Payment Button -->
                                    <button data-bs-toggle="modal" data-bs-target="#payment_model"
                                            onclick="makePayment(
                                            {{ $payment['id'] }},
                                            '{{$payment['entity']->id}}',
                                            '{{ $payment['entity_type'] == Site::class ? 'site' : 'supplier' }}',
                                            '{{$payment['amount']}}',
                                            '{{ $payment['transaction_type'] }}'

                                            )"
                                            class="badge badge-success rounded fw-bold">
                                        Pay {{ ucfirst( $payment['entity_type'] === Site::class ? $payment['entity']->site_name : $payment['entity']->name)  }}
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

                {{ $payments->links() }}

            </div>

        </div>

    </div>


    <div id="messageContainer"></div>

    <!-- Modal -->
{{--    <div id="bankModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="bankModalLabel"--}}
{{--         aria-hidden="true">--}}
{{--        <div class="modal-dialog" role="document">--}}
{{--            <div class="modal-content">--}}
{{--                <form id="payment-bank-form">--}}
{{--                    <div class="modal-header">--}}
{{--                        <h5 class="modal-title" id="bankModalLabel">Manage Payment</h5>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                    </div>--}}

{{--                    <div class="modal-body">--}}
{{--                        <!-- Hidden Field for Payment ID -->--}}

{{--                        <!-- Amount -->--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="modal_amount">Amount</label>--}}
{{--                            <input type="number" step="0.01" id="modal_amount" name="amount" class="form-control"/>--}}
{{--                        </div>--}}

{{--                        <!-- Entity Dropdown -->--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="modal_from">Entity</label>--}}
{{--                            <select id="modal_from" name="entity" class="form-select text-black"--}}
{{--                                    style="cursor: pointer">--}}
{{--                                <option value="">Select Entity</option>--}}
{{--                                @foreach ($payments as $entity)--}}
{{--                                    <option value="{{ $entity['type'] }}-{{ $entity['id'] }}">--}}
{{--                                        {{ $entity['id'] }} - {{ $entity['name'] }}--}}
{{--                                    </option>--}}
{{--                                @endforeach--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <!-- Transaction Type -->--}}
{{--                    <div class="container">--}}
{{--                        <div class="form-group">--}}
{{--                            <label class="form-label d-block">Transaction Type</label>--}}
{{--                            <div>--}}
{{--                                <input type="radio" name="transaction_type" id="sent" value="1" checked>--}}
{{--                                <label for="sent" class="form-check-label">Sent</label>--}}
{{--                            </div>--}}
{{--                            <div>--}}
{{--                                <input type="radio" name="transaction_type" id="received" value="0">--}}
{{--                                <label for="received" class="form-check-label">Received</label>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="modal-footer">--}}
{{--                        <!-- Submit button -->--}}
{{--                        <button type="submit" class="btn btn-primary">Save</button>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}


    <div id="payment_model" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="makePaymentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="payment_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="makePaymentModalLabel">Make Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden Fields -->
                        <input type="text" id="payment_id" name="payment_id"/>
                        <input type="text" id="transaction_type" name="transaction_type"/>
                        <input type="text" id="entity_id" name="entity_id"/>
                        <input type="text" id="entity_type" name="entity_type"/>
                        <!-- Payment Amount -->
                        <div class="form-group mb-3">
                            <label for="payment_amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" id="payment_amount" name="amount" class="form-control"
                                   placeholder="Enter payment amount"/>
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

        const messageContainer = $('#messageContainer');
        function makePayment(id, entity_id, entityType, amount, transactionType) {

            $('#payment_id').val(id);
            $('#entity_id').val(entity_id);
            $('#entity_type').val(entityType);
            $('#payment_amount').val(amount);
            $('#transaction_type').val(transactionType);
        }

        $('.payment_form').submit(function (event) {
            event.preventDefault(); // Prevent default form submission

            const formData = new FormData(this); // Collect form data
            const url = `/admin/payments`; // Define endpoint

            // Perform AJAX request
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false, // Ensure the correct form data type
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // CSRF token
                },
                success: function (response) {
                    // Handle successful response
                    displayAlert('success', response.message);

                    // Auto-hide success message and reload page
                    setTimeout(() => {
                        location.reload(); // Reload to reflect changes
                    }, 3000);
                },
                error: function (xhr) {
                    // Handle error responses

                    console.log(xhr);

                    if (xhr.status === 422) {


                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        for (const [field, messages] of Object.entries(errors)) {
                            displayAlert('danger', `${field}: ${messages.join(', ')}`);
                        }
                    } else if (xhr.status === 404) {
                        // Admin payment entry not found
                        displayAlert('danger', `${xhr.responseJSON.error}`);
                    } else if (xhr.status === 500) {
                        // Generic server error
                        displayAlert('danger', 'Payment could not be made. Something went wrong, please try again later.');
                    } else {
                        // Other errors
                        displayAlert('danger', 'An unexpected error occurred. Please try again later.');
                    }
                },
            });
        });

        function displayAlert(type, message) {
            const messageContainer = $('#messageContainer');
            messageContainer.append(`
        <div class="alert alert-${type} text-white border-0 mb-3" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <strong><i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} me-2"></i></strong>
                    ${message}
                </div>
            </div>
        </div>
    `);

            // Auto-hide message after 3 seconds
            setTimeout(() => {
                messageContainer.find('.alert').alert('close'); // Close the alert
            }, 3000);
        }



    </script>
</x-app-layout>


{{----}}
{{--        $('.make_payment_form').submit(function (event) {--}}

{{--            event.preventDefault();--}}

{{--            let formData = new FormData(this);--}}
{{--            formData.append('_method', 'PUT');--}}

{{--            const payment_id = $('#payment_id').val();--}}

{{--            const url = `/admin/payments/${payment_id}`;--}}

{{--            $.ajax({--}}
{{--                url: url,--}}
{{--                method: 'POST',--}}
{{--                data: formData,--}}
{{--                processData: false,--}}
{{--                contentType: false,--}}
{{--                headers: {--}}
{{--                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),--}}
{{--                },--}}
{{--                success: function (response) {--}}

{{--                    $('#make_payment_modal').modal('hide');--}}
{{--                },--}}
{{--                error: function (xhr) {--}}
{{--                    if (xhr.status === 422) {--}}

{{--                        const errors = xhr.responseJSON.errors;--}}

{{--                        for (const [field, messages] of Object.entries(errors)) {--}}
{{--                            alert(`${field}: ${messages.join(', ')}`);--}}
{{--                        }--}}

{{--                    } else {--}}
{{--                        alert('An error occurred while making the payment. Please try again.');--}}
{{--                    }--}}
{{--                }--}}
{{--            });--}}

{{--        });--}}

{{--        // Function to Open Modal for Adding New Payment--}}
{{--        function createNewPayment() {--}}
{{--            // Reset form and open modal--}}
{{--            $('#payment-bank-form')[0].reset();--}}
{{--            $('#payment_id').val(''); // Ensure hidden ID is cleared--}}

{{--            $('#bankModal').modal('show');--}}
{{--        }--}}
