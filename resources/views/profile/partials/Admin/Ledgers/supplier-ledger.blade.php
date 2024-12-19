<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Suppliers', 'View Supplier', 'View Ledger']" :urls="[$user . '/suppliers', $user . '/suppliers/' . $id, $user . '/supplier/ledger/' . $id]" />

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

                                    <form action="{{ url($user . '/supplier/ledger/' . $id) }}"
                                        class="d-flex flex-column flex-md-row gap-2 w-100" method="GET"
                                        id="filterForm">


                                        <select style="cursor: pointer"
                                            class="bg-white text-black form-select form-select-sm mt-2" name="site_id"
                                            onchange="document.getElementById('filterForm').submit();">
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

                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success  text-white mt-2"
                                                onclick="resetForm()">Reset</button>
                                        </div>

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

                        @if (count($paginatedLedgers) > 0)
                            @foreach ($paginatedLedgers as $key => $ledger)
                                <tr>
                                    <td>
                                        {{ $ledger['created_at'] }}
                                    </td>
                                    <td>{{ ucwords($ledger['supplier']) }}
                                    </td>
                                    <td>{{ ucwords($ledger['phase']) }}</td>
                                    <td>{{ ucwords($ledger['site']) }}</td>
                                    <td>{{ $ledger['category'] }}</td>
                                    <td>{{ ucwords($ledger['description']) }}</td>
                                    <td>{{ $ledger['debit'] }}</td>
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

    <script>
        function resetForm() {
            // Reset select fields to default values
            document.querySelector('select[name="site_id"]').value = 'all';

            window.location.href = "{{ url($user . '/supplier/ledger/' . $id) }}";
        }
    </script>

</x-app-layout>
