<x-app-layout>

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">


            <div class="card-body row g-2 text-left">

                <h3 class="mb-4 mt-2 text-info fw-bold">Payment History</h3>

                <table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th class="bg-info text-white">Date</th>
                            <th class="bg-info text-white">Site Name</th>
                            <th class="bg-info text-white">Site Owner</th>
                            <th class="bg-info text-white">Service Charge (%)</th>
                            <th class="bg-info text-white">Amount</th>
                            <th class="bg-info text-white">Screenshot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($paymentSuppliers->count() > 0)

                            @foreach ($paymentSuppliers as $payment_supplier)
                                <tr>

                                    <td>{{ $payment_supplier->created_at->format('d-M-Y') }}</td>
                                    <td>
                                        <img src="{{ $payment_supplier->screenshot }}" alt="">
                                    </td>
                                    <td>{{ ucwords($payment_supplier->site->site_owner_name) }}</td>
                                    <td>{{ ucwords($payment_supplier->site->site_name) }}</td>
                                    <td>{{ $payment_supplier->site->service_charge }}</td>
                                    <td>{{ Number::currency($payment_supplier->amount, 'INR') }}</td>


                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-danger fw-bold">No Records Found</td>
                            </tr>
                        @endif

                    </tbody>


                </table>

                {{ $paymentSuppliers->links() }}


            </div>


        </div>

    </div>



</x-app-layout>
