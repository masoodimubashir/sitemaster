{{-- <x-app-layout>

     <x-breadcrumb
        :names="[ 'View ' . $supplier->name , 'Supplier Payment History']"
        :urls="[ 'admin/suppliers/' . $supplier->id, 'admin/sites/supplier-payments/' . $supplier->id . '/edit' ]"
    />

    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card-body row g-2 text-left">


                <table class="table table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th class="bg-info text-white">Date</th>
                            <th class="bg-info text-white">Bill Image</th>
                            <th class="bg-info text-white">Site Name</th>
                            <th class="bg-info text-white">Site Owner</th>
                            <th class="bg-info text-white">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($paymentSuppliers->count() > 0)

                            @foreach ($paymentSuppliers as $payment_supplier)
                                <tr>

                                    <td>{{ $payment_supplier->created_at->format('d-M-Y') }}</td>
                                    <td>
                                        <img src="{{ asset($payment_supplier->screenshot) }}" alt="">
                                    </td>
                                    <td>{{ ucwords($payment_supplier->site->site_owner_name) }}</td>
                                    <td>{{ ucwords($payment_supplier->site->site_name) }}</td>
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



</x-app-layout> --}}

<x-app-layout>

    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    @if ($user === 'admin')
        <x-breadcrumb :names="['Dashboard', 'View ' . $supplier->name, 'View ' . $supplier->name . ' Payments']" :urls="['admin/dashboard', 'admin/suppliers/' . $supplier->id, 'admin/supplier/payments/' . $supplier->id]" />
    @else
        <x-breadcrumb :names="['supplier', $supplier->name, 'View ' . $supplier->name . ' Payments']" :urls="['user/dashboard', 'user/suppliers/' . $supplier->id, 'user/supplier/payments/' . $supplier->id]" />
    @endif

    <div class="row">


        <div class="col-lg-12 grid-margin stretch-card">



            <div class="card">


                @if ($payments)

                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Supplier Name</strong></td>
                                <td>{{ ucwords($supplier->name) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Location</strong></td>
                                <td>{{ ucwords($supplier->address) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact No</strong></td>
                                <td>{{ $supplier->contact_no }}</td>
                            </tr>

                        </tbody>
                    </table>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="fw-bold bg-info  text-white">Date</th>
                                <th class="fw-bold bg-info  text-white">Screenshot</th>
                                <th class="fw-bold bg-info  text-white">Site</th>
                                <th class="fw-bold bg-info  text-white">Site Owner</th>
                                <th class="fw-bold bg-info  text-white">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($payments->count() > 0)
                                @foreach ($payments as $payment_supplier)
                                    @if ($payment_supplier->supplier)
                                        <tr>


                                            <td>{{ $payment_supplier->created_at->format('d-M-Y') }}</td>


                                            <td>
                                                <img src="{{ asset('storage/' . $payment_supplier->screenshot) }}"
                                                    alt="">
                                            </td>

                                            <td>
                                                {{ Ucwords($payment_supplier->site->site_name) }}
                                            </td>

                                            <td>
                                                {{ Ucwords($payment_supplier->site->site_owner_name) }}
                                            </td>


                                            <td>{{ Number::currency($payment_supplier->amount, 'INR') }}</td>

                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-danger fw-bold">
                                                No Records Found..
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-danger fw-bold">
                                        No Records Found....
                                    </td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                @else
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <td colspan="3" class="text-center text-danger fw-bold">
                                    No Records Found....
                                </td>
                            </tr>

                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>

</x-app-layout>
