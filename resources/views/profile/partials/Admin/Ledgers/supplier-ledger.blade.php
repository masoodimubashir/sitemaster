<x-app-layout>

    <x-breadcrumb :names="['Suppliers', 'View Supplier', 'View Ledger']" :urls="['admin/suppliers', 'admin/suppliers/' . $id]" />

    <div class="row">

        <div class="col-sm-12">

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


                            <td colspan="4" style="background: #F4F5F7; border:none">
                                <div class="row">
                                    <form class="col" action="{{ url('admin/supplier/ledger/' . $id) }}"
                                        method="GET" id="filterForm">
                                        <select class="form-select form-select-sm bg-white" name="date_filter" style="cursor: pointer"
                                            id="date_filter" onchange="document.getElementById('filterForm').submit();">
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
                                                All Data</option>
                                        </select>

                                    </form>


                                    {{-- <form class="col" action="{{ url('admin/ledger/report') }}"
                                                    method="GET" id="ledger-report">
                                                    <select class="form-select form-select-sm bg-white"
                                                        name="date_filter" id="date_filter"
                                                        onchange="document.getElementById('ledger-report').submit();">
                                                        <option value="today"
                                                            {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                                            Generate Today's Report</option>
                                                        <option value="yesterday"
                                                            {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                                            Generate Yesterday's Report</option>
                                                        <option value="last_week"
                                                            {{ request('date_filter') === 'last_week' ? 'selected' : '' }}>
                                                            Generate Last Week's Report</option>
                                                        <option value="last_month"
                                                            {{ request('date_filter') === 'last_month' ? 'selected' : '' }}>
                                                            Generate Last Month's Report</option>
                                                        <option value="last_year"
                                                            {{ request('date_filter') === 'last_year' ? 'selected' : '' }}>
                                                            Generate Last Year's Report</option>
                                                        <option value="lifetime"
                                                            {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                                            Generate Full Report</option>
                                                    </select>

                                                </form> --}}

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




                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-4">
                {{ $paginatedLedgers }}
            </div>

        </div>

    </div>



</x-app-layout>
