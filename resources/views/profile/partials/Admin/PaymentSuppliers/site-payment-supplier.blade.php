<x-app-layout>

    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    @if ($user === 'admin')
        <x-breadcrumb :names="['Dashboard', 'View' .$site->site_name, 'View ' . $site->name . ' Payments']"
            :urls="[
            'admin/dashboard',
            'admin/sites/'. base64_encode($site->id),
            'admin/sites/payments/' . $site->id,
        ]" />
    @else
        <x-breadcrumb :names="['Sites', 'View ' .$site->site_name ,'View ' .$site->site_name . ' Payments']"
            :urls="[
            'user/dashboard',
            'user/sites/' . base64_encode($site->id),
            'user/sites/payments/' . $site->id,
        ]" />
    @endif

    <div class="row">


        <div class="col-lg-12 grid-margin stretch-card">



            <div class="card">


                @if ($payments)

                    {{-- <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Site Name</strong></td>
                                    <td>{{ ucwords($supplier->site->site_name) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location</strong></td>
                                    <td>{{ ucwords($supplier->site->location) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact No</strong></td>
                                    <td>{{ $supplier->site->contact_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Service Charge</strong></td>
                                    <td>{{ $supplier->site->service_charge }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Site Owner Name</strong></td>
                                    <td>{{ ucwords($supplier->site->site_owner_name) }}</td>
                                </tr>

                            </tbody>
                        </table> --}}

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="fw-bold bg-info  text-white">Date</th>
                                <th class="fw-bold bg-info  text-white">Screenshot</th>
                                <th class="fw-bold bg-info  text-white">Site</th>
                                <th class="fw-bold bg-info  text-white">Site Owner</th>
                                <th class="fw-bold bg-info  text-white">Supplier</th>
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
                                                <img src="{{ asset($payment_supplier->screenshot) }}" alt="">
                                            </td>

                                            <td>
                                                {{ Ucwords($payment_supplier->site->site_name) }}
                                            </td>

                                              <td>
                                                {{ Ucwords($payment_supplier->site->site_owner_name) }}
                                            </td>

                                            <td>
                                                {{ Ucwords($payment_supplier->supplier->name) }}
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
                                <td colspan="6" class="text-center text-danger fw-bold">
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



