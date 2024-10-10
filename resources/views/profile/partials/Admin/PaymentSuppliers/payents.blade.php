<x-app-layout>
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

                    <a href="{{ route('payments.create') }}" class="btn btn-sm btn-info text-white">Make Payment</a>


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
                                                    {{ Number::currency($final_total_balance, 'INR') }}
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
                                                    {{ Number::currency($total_debit, 'INR') }}
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
                                                    {{ Number::currency($total_credit, 'INR') }}
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
                                            <div>

                                                <form action="{{ route('payments.index') }}" method="GET"
                                                    id="filterForm">
                                                    <select class="form-select form-select-sm bg-white" name="date_filter" id="date_filter"
                                                        onchange="document.getElementById('filterForm').submit();">
                                                        <option value="today"
                                                            {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                                            Today</option>
                                                        <option value="yesterday"
                                                            {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                                            Yesterday</option>
                                                        <option value="last_week"
                                                            {{ request('date_filter') === 'last_week' ? 'selected' : '' }}>
                                                            Last Week</option>
                                                        <option value="last_month"
                                                            {{ request('date_filter') === 'last_month' ? 'selected' : '' }}>
                                                            Last Month</option>
                                                        <option value="last_year"
                                                            {{ request('date_filter') === 'last_year' ? 'selected' : '' }}>
                                                            Last Year</option>
                                                        <option value="lifetime"
                                                            {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                                            All Data</option>
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
                                        <th class="bg-info text-white fw-bold ">Balance</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paginatedLedgers as $key => $ledger)
                                        @php

                                            // $balance =  $ledger['amount'] - $ledger['payment_amounts'];
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $ledger['created_at'] }}
                                            </td>
                                            <td>{{ $ledger['category'] === 'Daily Expense' ? $ledger['category'] : ucwords($ledger['supplier']) }}
                                            </td>
                                            <td>{{ ucwords($ledger['phase']) }}</td>
                                            <td>{{ ucwords($ledger['site']) }}</td>
                                            {{-- <td>{{ $ledger['service_charge'] }}</td> --}}
                                            <td>{{ $ledger['category'] }}</td>
                                            <td>{{ ucwords($ledger['description']) }}</td>
                                            <td>{{ $ledger['debit'] }}</td>
                                            <td>
                                                {{ $ledger['credit'] }}
                                            </td>

                                            <td>
                                                {{ $ledger['balance'] }}
                                                {{-- @foreach ($balance as $k => $b)
                                                    {{ $key === $k ? $b  : null }}
                                                @endforeach --}}
                                                {{-- {{ $balance }} --}}
                                            </td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                        <div class="mt-4">
                            {{ $paginatedLedgers }}

                        </div>
                    </div>
                    <div class="tab-pane fade" id="material" role="tabpanel" aria-labelledby="material-tab">

                        <div class="table-responsive mt-4">
                            @if (count($payments))

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

                                        @foreach ($payments as $payment)
                                            <tr>

                                                <td>{{ $payment->created_at->format('d-M-Y') }}</td>
                                                <td>{{ ucwords($payment->supplier->name) }}</td>
                                                <td>{{ ucwords($payment->site->site_name) }}</td>
                                                <td>{{ ucwords($payment->site->site_owner_name) }}</td>
                                                <td>{{ $payment->site->contact_no }}</td>
                                                <td>{{ Number::currency($payment->amount, 'INR') }}</td>

                                            </tr>
                                        @endforeach

                                    </tbody>


                                </table>
                            @else
                                <h1 class="display-4">No records found..</h1>

                            @endif
                        </div>

                        <div class="mt-4">
                            {{ $payments->links() }}

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>