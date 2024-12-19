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

                                <form class="d-flex flex-column flex-md-row gap-2 w-100"
                                    action="{{ url($user . '/site/ledger/' . $site->id) }}" method="GET"
                                    id="filterForm">

                                    <select style="cursor: pointer"
                                        class="bg-white text-black form-select form-select-sm mt-2" name="supplier_id"
                                        onchange="document.getElementById('filterForm').submit();">
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

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success text-white mt-2"
                                            onclick="resetForm()">Reset</button>
                                    </div>

                                </form>

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
                                <td class="text-danger fw-bold text-center" colspan="8">No Records Available...</td>
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
            document.querySelector('select[name="supplier_id"]').value = 'all';
            window.location.href = "{{ url($user . '/site/ledger/' . $site->id) }}";
        }
    </script>

</x-app-layout>
