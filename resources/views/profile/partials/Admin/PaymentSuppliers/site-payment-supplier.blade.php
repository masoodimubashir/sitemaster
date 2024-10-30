<x-app-layout>

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">

                <div class="card-body row g-2 text-left">

                    @if ($site)

                        <h3 class="text-info">Payment's History</h3>

                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Site Name</strong></td>
                                    <td>{{ ucwords($site->site_name) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location</strong></td>
                                    <td>{{ ucwords($site->location) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact No</strong></td>
                                    <td>{{ $site->contact_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Charge</strong></td>
                                    <td>{{ $site->service_charge }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Site Owner Name</strong></td>
                                    <td>{{ ucwords($site->site_owner_name) }}</td>
                                </tr>

                            </tbody>
                        </table>


                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="fw-bold bg-info  text-white">Date</th>
                                    <th class="fw-bold bg-info  text-white">Screenshot</th>
                                    <th class="fw-bold bg-info  text-white">Supplier</th>
                                    <th class="fw-bold bg-info  text-white">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($site->paymeentSuppliers->count() > 0)
                                    @foreach ($site->paymeentSuppliers as $payment_supplier)
                                        <tr>


                                            <td>{{ $payment_supplier->created_at->format('d-M-Y') }}</td>

                                            <td>
                                                <img src="{{ asset($payment_supplier->screenshot) }}" alt="">
                                            </td>

                                            <td>
                                                {{ Ucwords($payment_supplier->supplier->name) }}
                                            </td>

                                            <td>{{ Number::currency($payment_supplier->amount, 'INR') }}</td>
                                    @endforeach
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-danger fw-bold">
                                            No Records Found....
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    @else
                        <h3>No Records Found...</h3>
                    @endif
                </div>
            </div>
        </div>

    </div>


    </div>
    </div>

    </div>

</x-app-layout>
