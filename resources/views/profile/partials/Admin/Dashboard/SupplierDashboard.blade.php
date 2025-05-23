<x-app-layout>


    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }
    </style>

    <div class="row">

        <!-- Stats Overview -->
        <div class="row mb-4 align-items-center">
            <!-- Statistics -->


            <!-- Button aligned right -->
            <div class="col-md-2 text-end">
                <a class="btn btn-primary btn-sm w-100" href="{{ url('/admin/dashboard') }}">
                    <i class="menu-icon fa fa-inbox"></i> Switch Sites
                </a>
            </div>

            <!-- Create Site Button -->
            <div class="col-md-2 text-end">
                <a href="{{ url('/admin/suppliers/create') }}" class="btn btn-primary btn-sm w-100">
                    + Create Supplier
                </a>
            </div>

        </div>

        <!-- Summary + Charts -->
        <div class="col-12">
            <div class="card card-rounded mb-4">
                <div class="card-body p-0 d-flex flex-column">



                    <form method="GET" action="{{ url()->current() }}">
                        <div class="p-3 border-bottom d-flex gap-2">
                            <input type="text" name="search" class="form-control w-25"
                                placeholder="Search for Supplier" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary ">Search</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>


                    <!-- Customer List -->
                    <div class="p-3  bg-white rounded shadow-sm">
                        @foreach ($suppliers as $supplier)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong>
                                        <a href="{{ url('/admin/suppliers/' . $supplier->id) }}">
                                            {{ $supplier->name }}
                                        </a>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $supplier->contact_no }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $supplier->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                @php
                                    // Calculate supplier financials
                                    $supplierBaseAmount =
                                        ($supplier->total_material_billing ?? 0) +
                                        ($supplier->total_site_expenses_from_payments ?? 0) +
                                        ($supplier->total_square_footage ?? 0) +
                                        ($supplier->total_daily_wagers ?? 0);

                                    $supplierServicePercentage = $supplier->service_charge ?? 0; // Assuming this is stored as a percentage (e.g., 5 for 5%)
                                    $supplierServiceAmount = ($supplierBaseAmount * $supplierServicePercentage) / 100;
                                    $supplierTotalCost = $supplierBaseAmount + $supplierServiceAmount;

                                    $supplierPaid = $supplier->total_income_payments ?? 0;
                                    $supplierBalance = $supplierTotalCost - $supplierPaid;
                                @endphp

                                <div class="text-end">
                                    <!-- Paid Amount -->
                                    <strong class="text-success">
                                        ₹{{ number_format($supplierPaid, 2) }}
                                    </strong><br>
                                    <small class="text-muted">Paid</small><br>

                                    <!-- Balance -->
                                    <strong class="{{ $supplierBalance >= 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format(abs($supplierBalance), 2) }}
                                    </strong><br>
                                    <small
                                        class="text-muted">{{ $supplierBalance >= 0 ? 'Balance Due' : 'Advance Paid' }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

    </div>


</x-app-layout>
