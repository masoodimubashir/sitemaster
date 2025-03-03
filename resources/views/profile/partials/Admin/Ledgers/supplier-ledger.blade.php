<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Suppliers', 'View Supplier', 'View Ledger']" :urls="[$user . '/suppliers', $user . '/suppliers/' . $id, $user . '/supplier/ledger/' . $id]" />

    <div class="row">

        <div class="col-sm-12">

            <div class="row">

                <div class="col-12 col-md-10 d-flex flex-column flex-md-row gap-2 align-items-center">

                    <form action="{{ url($user . '/supplier/ledger/' . $id) }}"
                        class="d-flex flex-column flex-md-row gap-2 w-100" method="GET" id="filterForm">

                        <!-- Site Selector -->
                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="site_id" onchange="document.getElementById('filterForm').submit();">
                            <option value="all"
                                {{ request('site_id', $sites[0]['site_id'] ?? 'all') === 'all' ? 'selected' : '' }}>
                                All Sites
                            </option>
                            @foreach ($sites as $site)
                                <option value="{{ $site['site_id'] }}"
                                    {{ request('site_id', $sites[0]['site_id'] ?? 'all') == $site['site_id'] ? 'selected' : '' }}>
                                    {{ $site['site'] }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Date Filter Selector -->
                        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                            name="date_filter" id="date_filter"
                            onchange="document.getElementById('filterForm').submit();">
                            <option value="today" {{ request('date_filter', 'today') === 'today' ? 'selected' : '' }}>
                                Today
                            </option>
                            <option value="yesterday"
                                {{ request('date_filter', 'today') === 'yesterday' ? 'selected' : '' }}>
                                Yesterday
                            </option>
                            <option value="this_week"
                                {{ request('date_filter', 'today') === 'this_week' ? 'selected' : '' }}>
                                This Week
                            </option>
                            <option value="this_month"
                                {{ request('date_filter', 'today') === 'this_month' ? 'selected' : '' }}>
                                This Month
                            </option>
                            <option value="this_year"
                                {{ request('date_filter', 'today') === 'this_year' ? 'selected' : '' }}>
                                This Year
                            </option>
                            <option value="lifetime"
                                {{ request('date_filter', 'today') === 'lifetime' ? 'selected' : '' }}>
                                All Data
                            </option>
                        </select>

                    </form>

                </div>



            </div>

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
                                            Effective Balance
                                        </b>
                                    </small>
                                    <h4 class="fw-bold">
                                        {{ Number::currency($effective_balance, 'INR') }}
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
                           




                        </tr>


                        <tr>
                            <th class="bg-info text-white fw-bold ">Date | Time</th>
                            <th class="bg-info fw-bold text-white">Transaction Type</th>
                            <th class="bg-info text-white fw-bold ">Site Name</th>
                            <th class="bg-info text-white fw-bold ">Phase</th>
                            <th class="bg-info text-white fw-bold ">Type</th>
                            <th class="bg-info text-white fw-bold">Information</th>
                            <th class="bg-info text-white fw-bold ">Debit</th>
                            <th class="bg-info fw-bold text-white">Credit</th>
                        </tr>
                    </thead>
                    <tbody>

                        @if (count($paginatedLedgers) > 0)
                            @foreach ($paginatedLedgers as $key => $ledger)
                                <tr>
                                    <td>
                                        {{ $ledger['created_at'] }}
                                    </td>
                                    <td>
                                        {{ $ledger['transaction_type'] }}
                                    </td>

                                  

                                    <td>
                                        {{ ucwords($ledger['site']) }}
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
                                <td class="text-danger text-center fw-bold" colspan="8">No Records Found</td>
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
