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

    <x-breadcrumb :names="['Ledger']" :urls="[$user . '/wager-attendance']"></x-breadcrumb>

    <div class="row">

        <div class="col-sm-12">

            <div class="home-tab">



                <div class="tab-pane fade show active" id="workforce" role="tabpanel" aria-labelledby="workforce-tab">

                    <div class="row">

                        <div
                            class=" col-md-6 d-flex flex-column flex-md-row gap-2 align-items-center justify-content-end">

                            <form class="d-flex flex-column flex-md-row gap-2 w-100"
                                action="{{ url($user . '/wager-attendance') }}" method="GET" id="filterForm">

                                <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                                    name="site_id" onchange="document.getElementById('filterForm').submit();">
                                    <option value="all" {{ request('site_id') === 'all' ? 'selected' : '' }}>
                                        All Sites
                                    </option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site['site_id'] }}" {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
                                            {{ $site['site'] }}
                                        </option>
                                    @endforeach
                                </select>


                                <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                                    name="wager_id" onchange="document.getElementById('filterForm').submit();">
                                    <option value="all" {{ request('wager_id') == 'all' ? 'selected' : '' }}>
                                        All Wager
                                    </option>
                                    @foreach ($wagers as $wager)
                                        <option value="{{ $wager['id'] }}" {{ request('wager_id') == $wager['id'] ? 'selected' : '' }}>
                                            {{ $wager['wager_name'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm"
                                    name="date_filter" id="date_filter"
                                    onchange="document.getElementById('filterForm').submit();">
                                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>
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

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success  text-white mt-2"
                                        onclick="resetForm()">Reset</button>
                                </div>

                            </form>
                        </div>


                    </div>




                    <div class="table-responsive mt-4">

                        <table class="table table-bordered">
                            <thead>

                                <tr>
                                    <th class="bg-info text-white fw-bold ">Date | Time</th>
                                    <th class="bg-info text-white fw-bold ">Wager</th>
                                    <th class="bg-info text-white fw-bold ">Phase</th>
                                    <th class="bg-info text-white fw-bold ">No Of Persons</th>
                                    <th class="bg-info text-white fw-bold">Site</th>

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
                                                {{ $ledger['wager_name'] }}
                                            </td>


                                            <td>
                                                {{ ucwords($ledger['phase']) }}
                                            </td>

                                            <td>
                                                {{ $ledger['no_of_persons'] }}
                                            </td>



                                            <td>
                                                {{ ucwords($ledger['site']) }}
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

            </div>
        </div>
    </div>



    <script>
        function resetForm() {
            // Reset all select elements to their default values
            document.querySelector('select[name="site_id"]').value = 'all';
            document.querySelector('select[name="wager_id"]').value = 'all';
            document.querySelector('select[name="date_filter"]').value = 'today';

            // Submit the form to apply the reset
            document.getElementById('filterForm').submit();
        }
    </script>
</x-app-layout>
