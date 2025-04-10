<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 9999999;
        }
    </style>

    <x-breadcrumb :names="['Ledger']" :urls="[$user . '/payments']"></x-breadcrumb>

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

                        <div class="row">

                            <div class="col-12 col-md-10 d-flex flex-column flex-md-row gap-2 align-items-center">

                                <form class="d-flex flex-column flex-md-row gap-2 w-100"
                                    action="{{ url($user . '/site/ledger/' . $site->id ) }}" method="GET" id="filterForm">

                                    <select style="cursor: pointer"
                                        class="bg-white text-black form-select form-select-sm" name="supplier_id"
                                        onchange="document.getElementById('filterForm').submit();">
                                        <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>
                                            All Suppliers
                                        </option>
                                        @foreach ($suppliers as $supplier)
                                            @if ($supplier['supplier_id'] != '--')
                                                <option value="{{ $supplier['supplier_id'] }}"
                                                    {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                                                    {{ $supplier['supplier'] }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>

                                    <select style="cursor: pointer"
                                        class="bg-white text-black form-select form-select-sm" name="date_filter"
                                        id="date_filter" onchange="document.getElementById('filterForm').submit();">
                                        <option value="today"
                                            {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                            Today
                                        </option>
                                        <option value="yesterday"
                                            {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                            Yesterday
                                        </option>
                                        <option value="this_week"
                                            {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>
                                            This Week
                                        </option>
                                        <option value="this_month"
                                            {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>
                                            This Month
                                        </option>
                                        <option value="this_year"
                                            {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>
                                            This Year
                                        </option>
                                        <option value="lifetime"
                                            {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                            All Data
                                        </option>
                                    </select>


                                </form>
                            </div>

                            <div
                                class="col-12 col-md-2 d-flex justify-content-start justify-content-md-end align-items-center">
                                <form action="{{ url($user . '/ledger/report') }}" method="GET">
                                    <input type="hidden" name="site_id" value="{{ request('site_id', $site->id) }}">
                                    <input type="hidden" name="date_filter"
                                        value="{{ request('date_filter', 'today') }}">
                                    <input type="hidden" name="supplier_id"
                                        value="{{ request('supplier_id', 'all') }}">
                                    <input type="hidden" name="wager_id" value="{{ request('wager_id', 'all') }}">
                                    <button type="submit" class="btn btn-info text-white btn-sm">
                                        Generate PDF Report
                                    </button>
                                </form>
                            </div>

                        </div>


                        <div class="table-responsive mt-4">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>

                                        <td colspan="1">
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

                                        <td colspan="1">
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
                                            <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">

                                                <small>
                                                    <b>
                                                        Effective Balance
                                                    </b>
                                                </small>
                                                <h4>
                                                    {{ Number::currency($effective_balance, 'INR') }}
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
                                                    {{ Number::currency($total_paid, 'INR') }}
                                                </h4>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th class="bg-info text-white fw-bold ">Date | Time</th>
                                        <th class="bg-info fw-bold text-white">Transaction Type</th>
                                        <th class="bg-info text-white fw-bold ">Supplier Name</th>
                                        <th class="bg-info text-white fw-bold ">Phase</th>
                                        <th class="bg-info text-white fw-bold ">Type</th>
                                        <th class="bg-info text-white fw-bold">Information</th>
                                        <th class="bg-info text-white fw-bold ">Debit</th>
                                        <th class="bg-info fw-bold text-white">Credit</th>

                                    </tr>
                                </thead>
                                <tbody>


                                    @if (count($paginatedLedgers))

                                        @foreach ($paginatedLedgers as $key => $ledger)
                                            <tr>

                                                <td>
                                                    {{ $ledger['created_at'] }}
                                                </td>

                                                <td>
                                                    {{ $ledger['transaction_type'] }}
                                                </td>

                                                <td>
                                                    {{ ucwords($ledger['supplier']) }}
                                                </td>

                                                <td>
                                                    {{ ucwords($ledger['phase']) }}
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
                                                Available...</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>

                        <div class="mt-4">
                            {{ $paginatedLedgers->links() }}
                        </div>

                    </div>
                    {{--                    <div class="tab-pane fade" id="material" role="tabpanel" aria-labelledby="material-tab"> --}}


                    {{--                        <div class="table-responsive mt-4"> --}}

                    {{--                            @if (count($paginatedLedgers)) --}}

                    {{--                                <table class="table table-bordered"> --}}

                    {{--                                    <thead> --}}
                    {{--                                        <tr> --}}

                    {{--                                            <th class="bg-info fw-bold text-white">Date</th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white">Site Total</th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white">Supplier Total</th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white">Payment Mode</th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white"> Supplier </th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white"> Site Name </th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white"> Site Owner </th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white"> Contact No </th> --}}
                    {{--                                            <th class="bg-info fw-bold text-white">Payment Amount</th> --}}
                    {{--                                        </tr> --}}
                    {{--                                    </thead> --}}

                    {{--                                    <tbody> --}}

                    {{--                                        @if (count($paginatedLedgers)) --}}


                    {{--                                            @foreach ($paginatedLedgers as $key => $ledger) --}}


                    {{--                                                @if ($ledger['category'] === 'Payments') --}}


                    {{--                                                    <tr> --}}
                    {{--                                                        <td> --}}
                    {{--                                                            {{ $ledger['created_at'] }} --}}
                    {{--                                                        </td> --}}



                    {{--                                                        <td> --}}
                    {{--                                                            {{ $ledger['payment_mode'] }} --}}
                    {{--                                                        </td> --}}


                    {{--                                                        <td> --}}
                    {{--                                                            {{ $ledger['site_payments_total'] }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ $ledger['supplier_payments_total'] }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ ucwords($ledger['supplier']) }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ ucwords($ledger['site']) }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ ucwords($ledger['site_owner']) }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ ucwords($ledger['contact_no']) }} --}}
                    {{--                                                        </td> --}}

                    {{--                                                        <td> --}}
                    {{--                                                            {{ $ledger['credit'] }} --}}
                    {{--                                                        </td> --}}
                    {{--                                                    </tr> --}}
                    {{--                                                @endif --}}
                    {{--                                            @endforeach --}}
                    {{--                                        @else --}}
                    {{--                                            <tr> --}}
                    {{--                                                <td class="text-danger fw-bold text-center" colspan="8">No Records --}}
                    {{--                                                    Available...</td> --}}
                    {{--                                            </tr> --}}
                    {{--                                        @endif --}}

                    {{--                                    </tbody> --}}


                    {{--                                </table> --}}
                    {{--                            @else --}}
                    {{--                                <h1 class="display-4 bg-white p-2 text-center fw-3 text-danger">No records found..</h1> --}}
                    {{--                            @endif --}}
                    {{--                        </div> --}}

                    {{--                        <div class="mt-4"> --}}
                    {{--                            {{ $paginatedLedgers->links() }} --}}
                    {{--                        </div> --}}
                    {{--                    </div> --}}
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
        function resetForm() {
            document.querySelector('select[name="date_filter"]').value = 'today';
            document.querySelector('select[name="supplier_id"]').value = 'all';
            document.querySelector('select[name="wager_id"]').value = 'all';
            window.location.href = "{{ url($user . '/site/ledger/' . $site->id) }}";
        }
    </script>

</x-app-layout>
