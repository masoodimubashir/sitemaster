<x-app-layout>

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 9999999;
        }
    </style>

    <x-breadcrumb :names="['Ledger']" :urls="['admin/payments']"></x-breadcrumb>

    <div class="row">
        <div class="col-sm-12">
            <div class="home-tab">

                <div class="d-sm-flex align-items-center justify-content-between border-bottom">

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="workforce-tab" data-bs-toggle="tab" href="#workforce"
                                role="tab" aria-controls="workforce" aria-selected="true">Ledger</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="material-tab" data-bs-toggle="tab" href="#material" role="tab"
                                aria-controls="material" aria-selected="false">Payments History</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content mt-3">

                    <div class="tab-pane fade show active" id="workforce" role="tabpanel"
                        aria-labelledby="workforce-tab">

                        <div class="table-responsive mt-4">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-danger">
                                                <small>
                                                    <b>
                                                        Total Balance
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    {{ Number::currency($total_balance, 'INR') }}
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-warning">
                                                <small>
                                                    <b>
                                                        Total Due
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    {{ Number::currency($total_due, 'INR') }}
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-success">
                                                <small>
                                                    <b>
                                                        Total Paid
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    {{ Number::currency($total_paid, 'INR') }}
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">
                                                <small>
                                                    <b>
                                                        Ongoing Sites
                                                    </b>
                                                </small>
                                                <h4>
                                                    {{ $is_ongoing_count }}
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
                                                    {{ $is_not_ongoing_count }}
                                                </h4>

                                            </div>
                                        </td>

                                        <td colspan="4" style="background: #F4F5F7; border:none">

                                            <div class="row">

                                                <form class="col " action="{{ url('admin/payments') }}" method="GET"
                                                    id="filterForm">
                                                    <select class="form-select form-select-sm bg-white text-black"
                                                        style="cursor: pointer" name="date_filter" id="date_filter"
                                                        onchange="document.getElementById('filterForm').submit();" >
                                                        <option value="today"
                                                            {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                                            Today</option>
                                                        <option value="yesterday"
                                                            {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                                            Yesterday</option>
                                                        <option value="this_week"
                                                            {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>
                                                            This Week</option>
                                                        <option value="this_month"
                                                            {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>
                                                            This Month</option>
                                                        <option value="this_year"
                                                            {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>
                                                            This Year</option>
                                                        <option value="lifetime"
                                                            {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                                            All Data
                                                        </option>
                                                    </select>

                                                </form>


                                                <form class="col" action="{{ url('admin/ledger/report') }}"
                                                    method="GET" id="ledger-report">
                                                    <select class="form-select form-select-sm bg-white text-black"
                                                        style="cursor: pointer" name="date_filter" id="date_filter"
                                                        onchange="document.getElementById('ledger-report').submit();">
                                                        <option value="">Generate Report</option>
                                                        <option value="today">
                                                            Generate Today's Report</option>
                                                        <option value="yesterday">
                                                            Generate Yesterday's Report</option>
                                                        <option value="this_week">
                                                            Generate This Week's Report</option>
                                                        <option value="this_month">
                                                            Generate This Month's Report</option>
                                                        <option value="this_year">
                                                            Generate This Year's Report</option>
                                                        <option value="lifetime">
                                                            Generate Full Report</option>
                                                    </select>

                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-info text-white fw-bold ">Date | Time</th>
                                        <th class="bg-info text-white fw-bold ">Supplier Name</th>
                                        <th class="bg-info text-white fw-bold ">Phase</th>
                                        <th class="bg-info text-white fw-bold ">Site Name</th>
                                        <th class="bg-info text-white fw-bold ">Type</th>
                                        <th class="bg-info text-white fw-bold">Information</th>
                                        <th class="bg-info text-white fw-bold ">Debit</th>
                                        <th class="bg-info text-white fw-bold ">Credit</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($paginatedLedgers))


                                        @foreach ($paginatedLedgers as $key => $ledger)
                                            @php
                                                // $balance =  $ledger['amount'] - $ledger['payment_amounts'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $ledger['created_at'] }}
                                                </td>
                                                <td>
                                                    {{ ucwords($ledger['supplier']) }}
                                                </td>
                                                <td>
                                                    {{ ucwords($ledger['phase']) }}
                                                </td>
                                                <td>
                                                    {{ ucwords($ledger['site']) }}
                                                </td>
                                                <td>
                                                    {{ $ledger['category'] }}
                                                </td>
                                                <td>
                                                    {{ ucwords($ledger['description']) }}
                                                </td>
                                                <td>
                                                    {{ $ledger['debit'] }}
                                                </td>
                                                <td>
                                                    {{ $ledger['credit'] }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-danger fw-bold text-center" colspan="8">No Records
                                                Awailable...</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>

                        <div class="mt-4">
                            {{ $paginatedLedgers->links() }}
                        </div>
                    </div>
                    <div class="tab-pane fade" id="material" role="tabpanel" aria-labelledby="material-tab">


                        <div class="table-responsive mt-4">

                            @if (count($paginatedLedgers))

                                <table class="table table-bordered">

                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>
                                            <th class="bg-info fw-bold text-white"> Supplier </th>
                                            <th class="bg-info fw-bold text-white"> Site Name </th>
                                            <th class="bg-info fw-bold text-white"> Site Owner </th>
                                            <th class="bg-info fw-bold text-white"> Contact No </th>
                                            <th class="bg-info fw-bold text-white">Payment Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @if (count($paginatedLedgers))


                                            @foreach ($paginatedLedgers as $key => $ledger)
                                                @if ($ledger['category'] === 'Payments')
                                                    <tr>
                                                        <td>
                                                            {{ $ledger['created_at'] }}
                                                        </td>

                                                        <td>
                                                            {{ ucwords($ledger['supplier']) }}
                                                        </td>

                                                        <td>
                                                            {{ ucwords($ledger['site']) }}
                                                        </td>

                                                        <td>
                                                            {{ ucwords($ledger['site_owner']) }}
                                                        </td>

                                                        <td>
                                                            {{ ucwords($ledger['contact_no']) }}
                                                        </td>

                                                        <td>
                                                            {{ $ledger['credit'] }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-danger fw-bold text-center" colspan="8">No Records Available...</td>
                                            </tr>
                                        @endif

                                    </tbody>


                                </table>
                            @else
                                <h1 class="display-4 bg-white p-2 text-center fw-3 text-danger">No records found..</h1>
                            @endif
                        </div>

                        <div class="mt-4">
                            {{-- {{ $payments->links() }} --}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Payment Supplier --}}
    <div id="payment-supplier" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-body">

                    <form id="payment_supplierForm" class="forms-sample material-form">

                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="number" min="0" name="amount" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Site -->
                        <div class="form-group">
                            {{-- <input type="hidden" name="site_id" value="{{ $site->id }}" /> --}}
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        {{-- Supplier --}}

                        <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                            <option value="">Select Supplier</option>
                            {{-- @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach --}}
                        </select>
                        @error('supplier_id')
                            <x-input-error :messages="$message" class="mt-2" />
                        @enderror


                        <!-- Is Verified -->
                        {{-- <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="is_verified"> Verify
                            </label>
                            @error('is_verified')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div> --}}

                        {{-- Screenshot --}}
                        <div class="mt-3">
                            <input class="form-control form-control-md" id="image" type="file"
                                name="screenshot">
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Pay') }}
                            </x-primary-button>
                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

    <div id="messageContainer"> </div>


    <script>
        $(document).ready(function() {
            $('form[id="payment_supplierForm"]').on('submit', function(e) {
                e.preventDefault();
                console.log(e);


                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove(); // Clear previous error messages

                $.ajax({
                    url: '{{ route('supplier-payments.store') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.relord();
                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) {

                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show " role="alert">

                            ${response.responseJSON.errors}
                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show " role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });
        })
    </script>
</x-app-layout>
