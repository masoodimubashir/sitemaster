<x-app-layout>



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

            <div class="row">
                <div class="col-12 col-md-10 d-flex flex-column flex-md-row gap-2 align-items-center">

                    {{-- <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url($user . '/payments') }}"
                        method="GET" id="filterForm">

                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="site_id" onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ request('site_id') === 'all' ? 'selected' : '' }}>
                                All Sites
                            </option>
                            @foreach ($sites as $site)
                                <option value="{{ $site['site_id'] }}"
                                    {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
                                    {{ $site['site'] }}
                                </option>
                            @endforeach
                        </select>

                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="wager_id" onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ request('wager_id') === 'all' ? 'selected' : '' }}>
                                All Wagers
                            </option>
                            @foreach ($wagers as $wager)
                                <option value="{{ $wager['wager_id'] }}"
                                    {{ request('wager_id') == $wager['wager_id'] ? 'selected' : '' }}>
                                    {{ $wager['description'] }}
                                </option>
                            @endforeach
                        </select>

                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="supplier_id" onchange="document.getElementById('filterForm').submit();">
                            <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>
                                All Suppliers
                            </option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier['supplier_id'] }}"
                                    {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                                    {{ $supplier['supplier'] }}
                                </option>
                            @endforeach
                        </select>

                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="date_filter" id="date_filter"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                Today</option>
                            <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                Yesterday</option>
                            <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>
                                This Week</option>
                            <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>
                                This Month</option>
                            <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>
                                This Year</option>
                            <option value="lifetime" {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                All Data
                            </option>
                        </select>

                        <div class="d-flex gap-2">

                            <button type="button" class="btn btn-success  text-white mt-2"
                                onclick="resetForm()">Reset</button>
                        </div>

                    </form> --}}
                </div>

                {{-- <div class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end align-items-center">
                    <form action="{{ url($user . '/ledger/report') }}" method="GET">
                        <input type="hidden" name="site_id" value="{{ request('site_id', 'all') }}">
                        <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                        <input type="hidden" name="wager_id" value="{{ request('wager_id', 'all') }}">
                        <button type="submit" class="btn btn-info text-white btn-sm">
                            Generate PDF Report
                        </button>
                    </form>
                </div> --}}
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

                                <td colspan="1">
                                    <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">
                                        <small>
                                            <b>
                                                Ongoing Sites
                                            </b>
                                        </small>
                                        <h4>
                                            {{-- {{ $is_ongoing_count }} --}}
                                        </h4>

                                    </div>
                                </td>

                                <td>
                                    <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">
                                        <small>
                                            <b>
                                                Closed Sites
                                            </b>
                                        </small>
                                        <h4>
                                            {{-- {{ $is_not_ongoing_count }} --}}
                                        </h4>

                                    </div>
                                </td>

                            </tr>

                            <tr>
                                <th class="bg-info text-white fw-bold"> Status </th>
                                <th class="bg-info text-white fw-bold">Date</th>
                                <th class="bg-info text-white fw-bold">From</th>
                                <th class="bg-info text-white fw-bold"> To </th>
                                <th class="bg-info text-white fw-bold">Amount</th>
                                <th class="bg-info text-white fw-bold">Actions</th>
                            </tr>

                        </thead>

                        <tbody>
                            @foreach ($payment_banks as $payment)
                                <tr>
                                    <td class="fw-bold {{ $payment->is_on_going ? 'text-success' : 'text-danger' }}">
                                        {{ $payment->is_on_going ? 'On Going' : 'Not Going' }}
                                    </td>
                                    <td>{{ $payment->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $payment->from_type }}</td>
                                    <td>{{ $payment->to_type }}</td>
                                    <td>{{ $payment->amount }}</td>
                                    <td class="space-x-4">

                                        <!-- Edit Button -->
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#bankModal"
                                            onclick="fetchPaymentData({{ $payment->id }})">
                                            <i
                                                class="fa-solid fa-edit text-xl text-primary bg-white rounded-full px-2 py-1"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <a href="#"
                                            onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this payment?')) 
                                            document.getElementById('delete-form-{{ $payment->id }}').submit();">
                                            <i
                                                class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
                                        </a>

                                        <form id="delete-form-{{ $payment->id }}"
                                            action="{{ route('payments.destroy', [base64_encode($payment->id)]) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
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


    <div id="messageContainer"> </div>

    <!-- Modal -->
    <div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="payment-bank-form">
                        @csrf

                        <!-- Hidden Field for Payment ID -->
                        <input type="hidden" name="payment_id" id="payment_id">

                        <!-- Payment Amount -->
                        <div class="form-group mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" id="modal_amount" min="0"
                                step="0.01" placeholder="Enter amount" />
                        </div>

                        <!-- From -->
                        <div class="form-group mb-3">
                            <label for="from" class="form-label">From</label>
                            <select class="form-select" name="from" id="modal_from">
                                <option value="">Select Source</option>
                                @foreach ($collection as $data)
                                    <option value="{{ $data['name'] . '_' . $data['id'] }}">
                                        {{ $data['name'] }} ({{ $data['category'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- To -->
                        <div class="form-group mb-3">
                            <label for="to" class="form-label">To</label>
                            <select class="form-select" name="to" id="modal_to">
                                <option value="">Select Destination</option>
                                @foreach ($collection as $data)
                                    <option value="{{ $data['name'] . '_' . $data['id'] }}">
                                        {{ $data['name'] }} ({{ $data['category'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-3 d-flex justify-content-start align-items-center">
                            <input type="checkbox" name="is_on_going" id="modal_is_on_going" value="1">
                            <label for="modal_is_on_going" class="ms-2 mb-0">Is On Going</label>
                        </div>

                        <!-- Submit Button -->
                            <button type="submit" class="btn btn-info btn-sm mt-2">Update Payment</button>
                    </form>
                </div>

            </div>
        </div>
    </div>



    <script>
        function fetchPaymentData(paymentId) {
            $.ajax({
                url: `/admin/manage-payment/${paymentId}/edit`, // Replace with your route
                method: 'GET',
                success: function(response) {
                    // Populate modal fields
                    $('#payment_id').val(response.id);
                    $('#modal_amount').val(response.amount);
                    $('#modal_from').val(`${response.from_type}_${response.from}`);
                    $('#modal_to').val(`${response.to_type}_${response.to}`);
                    $('#modal_is_on_going').prop('checked', response.is_on_going);
                },
                error: function() {
                    alert('Failed to fetch payment details. Please try again.');
                }
            });
        }


        $(document).ready(function() {



            // Reset form fields when the modal is opened
            $('#bankModal').on('show.bs.modal', function() {
                const form = $('#payment-bank-form')[0]; 
                form.reset();
            });

            // Reset form fields when the modal is closed
            $('#bankModal').on('hidden.bs.modal', function() {
                const form = $('#payment-bank-form')[0]; 
                form.reset();
            });


            $('#payment-bank-form').submit(function(event) {

                event.preventDefault();

                const product_id = $('#product_id').val();
                const formData = new FormData(this);
                
                if (product_id) {
                    formData.append('_method', 'PUT');
                }

                const url = product_id ? '/admin/manage-payment/update' : '/admin/manage-payment';

                $.ajax({
                    
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function(response) {
                        if (response.status) {

                            alert('Payment updated successfully!');
                            location.reload();
                            $('#payment-bank-form')[0].reset();

                        } else {

                            alert('Failed to update payment. ' + (response.message || ''));
                            $('#payment-bank-form')[0].reset();

                        }
                    },

                    error: function(xhr) {

                        const errors = xhr.responseJSON?.errors || {};
                        const messages = Object.values(errors).flat().join('\n');
                        alert(messages || 'An error occurred. Please try again.');
                        $('#payment-bank-form')[0].reset();

                    }
                
                });

            });

        });
    </script>
</x-app-layout>
