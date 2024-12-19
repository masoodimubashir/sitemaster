<x-app-layout>


    <style>
        /* Basic reset and styling */


        .d {
            position: relative;
            display: inline-block;
        }

        .dropdown-button {
            color: black;
            border: none;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            border: 1px solid lightgray;
            border-radius: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
            min-width: 160px;
            background: white;
        }

        .dropdown-content li {
            padding: 10px 20px;
            cursor: pointer;
        }

        .dropdown-content li:hover {
            background-color: lightgray;
            color: black;
            /* Ensure text is readable on hover */
        }

        .d:hover .dropdown-content {
            display: block;
        }
    </style>


    @if ($site)

        <div class="row">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card">

                    <div class="card-body row g-2 text-left">

                        <div class="col-md-3">
                            <i class="fa fa-building display-5 text-info me-2"></i> {{ ucwords($site->site_name) }}

                        </div>
                        <div class="col-md-3">
                            <i class="fa fa-user-circle display-5 text-info me-2"></i>
                            {{ ucwords($site->site_owner_name) }}
                        </div>
                        <div class="col-md-3">
                            <i class="fa fa-mobile display-5 text-info me-2"></i> <a
                                href="tel:+91-{{ $site->contact_no }}">91-{{ ucwords($site->contact_no) }}</a>
                        </div>
                        <div class="col-md-3">
                            <i class="fa fa-map-marker display-5 text-info me-2"></i> {{ ucwords($site->location) }}
                        </div>

                        <div class="col-md-3">
                            Service Charge : {{ $site->service_charge }} <i class="fa fa-percent  text-info me-2"></i>
                        </div>

                         <div class="col-md-3">
                            Grand Total:
                            {{ $grand_total_amount }}
                        </div>

                        <div class="col-md-3">
                            <i class="fa fa-money display-5 text-info me-2"></i>
                            {{-- {{ $grand_total_amount }} + {{ $site->service_charge }} = --}}
                            {{ ($site->service_charge / 100) * $grand_total_amount + $grand_total_amount }}
                        </div>




                    </div>


                </div>
            </div>

        </div>

        @if ($site->phases->count() > 0)

            @foreach ($site->phases as $phase)

                <div class="mt-5">

                    <div class="card">

                        <div class="card-body row g-5 text-left">

                            <div class="table-responsive ">

                                <table class="table table-bordered ">

                                    <h5 class="fw-bold text-info mb-3">{{ ucwords($phase->phase_name) }} Costing</h5>

                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">{{ ucwords($phase->phase_name) }}
                                            </th>
                                            <th class="bg-info fw-bold text-white">Amount</th>
                                            <th class="bg-info fw-bold text-white"> Service Charge
                                                {{ $site->service_charge }}%</th>
                                            <th class="bg-info fw-bold text-white"> Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>

                                            <td>
                                                Raw Materials
                                            </td>
                                            <td>
                                                {{ $phase->construction_total_amount }}
                                            </td>
                                            <td>
                                                ....
                                                {{-- {{ ($site->service_charge / 100) * $phase->construction_total_amount }} --}}
                                            </td>
                                            <td>

                                                {{ ($site->service_charge / 100) * $phase->construction_total_amount + $phase->construction_total_amount }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                Square Footage
                                            </td>

                                            <td>
                                                {{ $phase->square_footage_total_amount }}
                                            </td>

                                            <td>
                                                ...
                                                {{-- {{ ($site->service_charge / 100) * $phase->square_footage_total_amount }} --}}
                                            </td>

                                            <td>

                                                {{ ($site->service_charge / 100) * $phase->square_footage_total_amount + $phase->square_footage_total_amount }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                Expenses
                                            </td>

                                            <td>
                                                {{ $phase->daily_expenses_total_amount }}
                                            </td>

                                            <td>
                                                ....
                                                {{-- {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount }} --}}
                                            </td>

                                            <td>

                                                {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount }}
                                            </td>

                                        </tr>


                                        <tr>
                                            <td>
                                                Wager
                                            </td>

                                            <td>
                                                {{ $phase->daily_wagers_total_amount }}
                                            </td>

                                            <td>
                                                ....
                                                {{-- {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount }} --}}
                                            </td>

                                            <td>

                                                {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount }}
                                            </td>
                                        </tr>


                                        <tr>
                                            <td>Sub Total</td>

                                            <td>
                                                {{ Number::currency(
                                                    $phase->construction_total_amount +
                                                        $phase->square_footage_total_amount +
                                                        $phase->daily_expenses_total_amount +
                                                        $phase->daily_wagers_total_amount,
                                                    'INR',
                                                ) }}

                                            </td>

                                            <td>
                                                {{ Number::currency(
                                                    ($site->service_charge / 100) * $phase->construction_total_amount +
                                                        ($site->service_charge / 100) * $phase->square_footage_total_amount +
                                                        ($site->service_charge / 100) * $phase->daily_expenses_total_amount +
                                                        ($site->service_charge / 100) * $phase->daily_wagers_total_amount,
                                                    'INR',
                                                ) }}

                                            </td>
                                            <td>
                                                {{ Number::currency(
                                                    ($site->service_charge / 100) * $phase->construction_total_amount +
                                                        $phase->construction_total_amount +
                                                        ($site->service_charge / 100) * $phase->square_footage_total_amount +
                                                        $phase->square_footage_total_amount +
                                                        ($site->service_charge / 100) * $phase->daily_expenses_total_amount +
                                                        $phase->daily_expenses_total_amount +
                                                        ($site->service_charge / 100) * $phase->daily_wagers_total_amount +
                                                        $phase->daily_wagers_total_amount,
                                                    'INR',
                                                ) }}

                                            </td>
                                        </tr>







                                    </tbody>


                                </table>

                            </div>

                            <div class="table-responsive">
                                <!-- Content for Construction Billing Material Tab -->
                                <h5 class="fw-bold text-info mb-3">Construction Material</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>
                                            <th class="bg-info fw-bold text-white"> Image </th>
                                            <th class="bg-info fw-bold text-white"> Name </th>
                                            <th class="bg-info fw-bold text-white"> Item Name </th>
                                            <th class="bg-info fw-bold text-white"> Price </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Construction Material --}}

                                        @if (count($phase->constructionMaterialBillings))
                                            @foreach ($phase->constructionMaterialBillings as $construction_material_billing)
                                                <tr>

                                                    <td>
                                                        {{ $construction_material_billing->created_at->format('d-M-Y') }}
                                                    </td>

                                                    <td>
                                                        <img src="{{ asset($construction_material_billing->item_image_path) }}"alt=""
                                                            class="w-20 h-20 rounded-full">
                                                    </td>
                                                    <td>
                                                        <a class="fw-bold link-offset-2 link-underline link-underline-opacity-0"
                                                            href="{{ route('suppliers.show', [base64_encode($construction_material_billing->supplier->id)]) }}">
                                                            <mark>
                                                                {{ $construction_material_billing->supplier->name ?? '' }}
                                                            </mark>
                                                        </a>
                                                    </td>

                                                    <td>
                                                        {{ $construction_material_billing->item_name }}
                                                    </td>
                                                    <td>
                                                        {{ $construction_material_billing->amount }}
                                                        <br>
                                                    </td>




                                                </tr>

                                                @if ($loop->last)
                                                    <tr class="">
                                                        <td colspan="3" class="">
                                                            Total Cost + Cost:</td>

                                                        <td colspan="2" class="">
                                                            {{ $site->service_charge }}%
                                                            +
                                                            {{ $phase->construction_total_amount }}
                                                            =
                                                            {{ ($site->service_charge / 100) * $phase->construction_total_amount + $phase->construction_total_amount }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-danger">No
                                                    Records Found..</td>
                                            </tr>
                                        @endif

                                    </tbody>
                                </table>

                            </div>

                            <div class="table-responsive">
                                <!-- Square Footage Tab -->

                                <h5 class="fw-bold text-info mb-3">Square Footage</h5>
                                <table class="table table-bordered ">

                                    <thead>

                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>

                                            <th class="bg-info fw-bold text-white"> Image </th>

                                            <th class="bg-info fw-bold text-white"> Wager type </th>

                                            <th class="bg-info fw-bold text-white">Supplier Name</th>

                                            <th class="bg-info fw-bold text-white"> Type </th>

                                            <th class="bg-info fw-bold text-white"> Price </th>

                                            <th class="bg-info fw-bold text-white">Multiplier</th>

                                            <th class="bg-info fw-bold text-white">
                                                Total Price
                                            </th>


                                        </tr>

                                    </thead>

                                    <tbody>


                                        {{-- Square Footage --}}



                                        @if (count($phase->squareFootageBills))
                                            @foreach ($phase->squareFootageBills as $sqft)
                                                <tr>

                                                    <td>
                                                        {{ $sqft->created_at->format('d-M-y') }}
                                                    </td>


                                                    <td>
                                                        <img src="{{ asset($sqft->image_path) }}"alt=""
                                                            class="w-20 h-20 rounded-full">
                                                    </td>
                                                    <td>
                                                        {{ ucwords($sqft->wager_name) }}
                                                    </td>

                                                    <td>
                                                        {{ ucwords($sqft->supplier->name) }}

                                                    </td>


                                                    <td>
                                                        {{ ucwords($sqft->type) }}
                                                    </td>

                                                    <td>
                                                        {{ Number::currency($sqft->price ?? 0, 'INR') }}
                                                    </td>

                                                    <td>
                                                        {{ $sqft->multiplier }}
                                                    </td>



                                                    <td>
                                                        {{ $sqft->multiplier * $sqft->price }}
                                                    </td>


                                                </tr>
                                                @if ($loop->last)
                                                    <tr class="">
                                                        <td colspan="6" class="">

                                                            Total Amount + Cost
                                                        </td>

                                                        <td colspan="2" class="">
                                                            {{ $site->service_charge }}%
                                                            +
                                                            {{ $phase->square_footage_total_amount }}
                                                            =
                                                            {{ ($site->service_charge / 100) * $phase->square_footage_total_amount + $phase->square_footage_total_amount }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center text-danger">
                                                    No
                                                    Records Found</td>
                                            </tr>
                                        @endif



                                    </tbody>


                                </table>
                            </div>

                            <div class="table-responsive">
                                <!-- Content for Wager Tab -->


                                <h5 class="fw-bold text-info mb-3">Daily Wager</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>
                                            <th class="bg-info fw-bold text-white"> Wager Name </th>
                                            <th class="bg-info fw-bold text-white"> Price Per Day </th>

                                        </tr>
                                    </thead>
                                    <tbody>


                                        {{-- Square Footage --}}


                                        @if (count($phase->dailyWagers))
                                            @foreach ($phase->dailyWagers as $daily_wager)
                                                <tr>

                                                    <td>
                                                        {{ $daily_wager->created_at->format('d-M-Y') }}
                                                    </td>


                                                    <td>
                                                        {{ ucwords($daily_wager->wager_name) }}
                                                    </td>

                                                    <td>
                                                        {{ $daily_wager->price_per_day }}
                                                    </td>

                                                </tr>
                                                @if ($loop->last)
                                                    <tr>
                                                        <td colspan="1">
                                                            Total Amount + Cost</td>
                                                        <td colspan="2" class="">
                                                            {{ $site->service_charge }}%
                                                            +
                                                            {{ $phase->daily_wagers_total_amount }}
                                                            =
                                                            {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount + $phase->daily_wagers_total_amount }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="2" class="text-center text-danger">
                                                    No
                                                    Records Found</td>
                                                <td>
                                            </tr>
                                        @endif






                                    </tbody>


                                </table>


                            </div>

                            <div class="table-responsive">

                                <h5 class="fw-bold text-info mb-3">Expenses</h5>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>
                                            <th class="bg-info fw-bold text-white"> Item Name </th>
                                            <th class="bg-info fw-bold text-white"> Total Price </th>

                                        </tr>
                                    </thead>
                                    <tbody>

                                        @if (count($phase->dailyExpenses))
                                            @foreach ($phase->dailyExpenses as $daily_expenses)
                                                <tr>

                                                    <td>
                                                        {{ $daily_expenses->created_at->format('d-M-Y') }}
                                                    </td>


                                                    <td>
                                                        {{ ucwords($daily_expenses->item_name) }}
                                                    </td>

                                                    <td>
                                                        {{ $daily_expenses->price }}
                                                    </td>



                                                </tr>
                                                @if ($loop->last)
                                                    <tr class="">
                                                        <td colspan="1" class="">
                                                            Total Amount:</td>
                                                        <td colspan="2" class="">
                                                            {{ $site->service_charge }}%
                                                            +
                                                            {{ $phase->daily_expenses_total_amount }}
                                                            =
                                                            {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount + $phase->daily_expenses_total_amount }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center text-danger">
                                                    No Records Found
                                                </td>
                                            </tr>
                                        @endif



                                    </tbody>


                                </table>

                            </div>

                            <div class="table-responsive">

                                <h5 class="fw-bold text-info mb-3">Attendance</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="bg-info fw-bold text-white">Date</th>
                                            <th class="bg-info fw-bold text-white"> No Of Persons </th>
                                            <th class="bg-info fw-bold text-white">Wager Name </th>
                                            <th class="bg-info fw-bold text-white">
                                                Supplier
                                            </th>
                                            <th class="bg-info fw-bold text-white">Date</th>

                                        </tr>
                                    </thead>
                                    <tbody>

                                        {{-- Square Footage --}}


                                        @if (count($phase->wagerAttendances))
                                            @foreach ($phase->wagerAttendances as $wager_attendance)
                                                <tr aria-colspan="4">

                                                    <td>
                                                        {{ $wager_attendance->created_at->format('d-M-Y') }}
                                                    </td>

                                                    <td>
                                                        {{ $wager_attendance->no_of_persons }}
                                                    </td>

                                                    <td>
                                                        {{ ucwords($wager_attendance->dailyWager->wager_name) }}
                                                    </td>

                                                    <td>
                                                        {{ ucwords($wager_attendance->dailyWager->supplier->name) }}
                                                    </td>

                                                    <td>
                                                        {{ \Carbon\Carbon::parse($wager_attendance->created_at)->format('d-M-Y') }}

                                                    </td>

                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-danger">
                                                    No Records Found
                                                </td>
                                            </tr>
                                        @endif



                                    </tbody>


                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach
        @else
            <h3 class="text-danger">
                No Records Found...
                </h5>
        @endif
    @else
        <h1>
            No site Record Available
        </h1>
    @endif

</x-app-layout>
