<x-app-layout>


    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    <x-breadcrumb :names="['Sites', $site->site_name, ' Ledger']" :urls="[$user . '/sites', $user . '/sites/' . base64_encode($site->id), $user . '/site/ledger/' . $site->id]" />

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


                                    <form class="col" action="{{ url($user . '/site/ledger/' . $site->id) }}"
                                        method="GET" id="filterForm">
                                        <select class="form-select form-select-sm bg-white text-dark" name="date_filter"
                                            id="date_filter" onchange="document.getElementById('filterForm').submit();"
                                            style="cursor: pointer;">
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
                                <tr>
                                    <td>
                                        {{ $ledger['created_at'] }}
                                    </td>
                                    <td>{{ $ledger['category'] === 'Daily Expense' ? $ledger['category'] : ucwords($ledger['supplier']) }}
                                    </td>
                                    <td>{{ ucwords($ledger['phase']) }}</td>
                                    <td>{{ ucwords($ledger['site']) }}</td>
                                    <td>{{ $ledger['category'] }}</td>
                                    <td>{{ ucwords($ledger['description']) }}</td>
                                    <td>{{ $ledger['category'] !== 'Payments' ? $ledger['total_amount_with_service_charge'] : 0 }}
                                    </td>
                                    <td>
                                        {{ $ledger['credit'] }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-danger fw-bold text-center" colspan="8">No Records Awailable...</td>
                            </tr>
                        @endif

                    </tbody>
                </table>

            </div>

            <div class="mt-4">

                {{ $paginatedLedgers }}

            </div>

        </div>
    </div>
</x-app-layout>
