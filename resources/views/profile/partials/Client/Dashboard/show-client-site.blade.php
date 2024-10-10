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

                        <div class="col-md-4">
                            <i class="fa fa-building display-5 text-info me-2"></i> {{ ucwords($site->site_name) }}

                        </div>
                        <div class="col-md-4">
                            <i class="fa fa-user-circle display-5 text-info me-2"></i>
                            {{ ucwords($site->site_owner_name) }}
                        </div>
                        <div class="col-md-4">
                            <i class="fa fa-mobile display-5 text-info me-2"></i> <a
                                href="tel:+91-{{ $site->contact_no }}">91-{{ ucwords($site->contact_no) }}</a>
                        </div>
                        <div class="col-md-4">
                            <i class="fa fa-map-marker display-5 text-info me-2"></i> {{ ucwords($site->location) }}
                        </div>

                        <div class="col-md-4 ">
                            Service Charge : {{ $site->service_charge }} <i class="fa fa-percent  text-info me-2"></i>
                        </div>

                        <div class="col-md-4">
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
                <div class="row ">


                    <div class="col-lg-12 grid-margin stretch-card">

                        <div class="card">

                            <div class="card-body ">

                                <div class="col-sm-12">
                                    <div class="home-tab overflow-auto">
                                        <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                                            <ul class="nav nav-tabs" role="tablist">

                                                <li class="nav-item bg-info">
                                                    <a class="nav-link text-white"
                                                        id="commulative-cost-tab{{ $phase->id }}"
                                                        data-bs-toggle="tab" href="#commulative-cost{{ $phase->id }}"
                                                        role="tab"
                                                        aria-controls="commulative-cost{{ $phase->id }}"
                                                        aria-selected="false">
                                                        {{ $phase->phase_name }} Cost</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link active"
                                                        id="billing-material-tab{{ $phase->id }}"
                                                        data-bs-toggle="tab"
                                                        href="#billing-material{{ $phase->id }}" role="tab"
                                                        aria-controls="billing-material{{ $phase->id }}"
                                                        aria-selected="true">Raw Material</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="daily-attendance-tab{{ $phase->id }}"
                                                        data-bs-toggle="tab"
                                                        href="#daily-attendance{{ $phase->id }}" role="tab"
                                                        aria-controls="daily-attendance{{ $phase->id }}"
                                                        aria-selected="false">Square Footage</a>

                                                </li>

                                                <li class="nav-item">
                                                    <a class="nav-link" id="square-footage-tab{{ $phase->id }}"
                                                        data-bs-toggle="tab" href="#square-footage{{ $phase->id }}"
                                                        role="tab"
                                                        aria-controls="square-footage{{ $phase->id }}"
                                                        aria-selected="false">Expenses</a>
                                                </li>



                                                <li class="nav-item d  dropdown-button">

                                                    <a class="nav-link" style="border:none !important"
                                                        id="daily-expenses-tab{{ $phase->id }}" data-bs-toggle="tab"
                                                        href="#daily-expenses{{ $phase->id }}" role="tab"
                                                        aria-controls="daily-expenses{{ $phase->id }}"
                                                        aria-selected="false">
                                                        Wager
                                                        <i class="fa-solid fa-caret-down"></i>
                                                        <ul class="dropdown-content">

                                                            <li id="wager-attendance-tab{{ $phase->id }}"
                                                                data-bs-toggle="tab"
                                                                href="#wager-attendance{{ $phase->id }}"
                                                                role="tab"
                                                                aria-controls="wager-attendance{{ $phase->id }}"
                                                                aria-selected="false">View Attendance</li>

                                                        </ul>

                                                    </a>

                                                </li>


                                            </ul>

                                            <div class="btn-wrapper mt-3 mt-sm-0">


                                                <a href="{{ route('generate-report', [$site->id]) }}" class="btn text-info fw-bold">
                                                    Generate Report
                                                </a>

                                                <a href="#" class="btn btn-inverse-success btn-sm fw-bold">
                                                    Phase Total:
                                                    <i class="fa fa-indian-rupee"></i>
                                                    {{ ($site->service_charge / 100) * $phase->total_amount + $phase->total_amount }}
                                                </a>


                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                                    <li>
                                                        <button class=" btns dropdown-item"
                                                            data-modal="modal-construction-billings{{ $phase->id }}">
                                                            Construction
                                                        </button>
                                                    </li>

                                                    <li>

                                                        <button class="btns dropdown-item"
                                                            data-modal="modal-square-footage-bills{{ $phase->id }}">
                                                            Square Footage </button>

                                                    </li>

                                                    <li>
                                                        <button class="btns dropdown-item"
                                                            data-modal="modal-daily-expenses{{ $phase->id }}">
                                                            Expenses </button>
                                                    </li>

                                                </ul>

                                            </div>
                                        </div>

                                        <div class="tab-content mt-3">

                                            <div class="tab-pane fade show active"
                                                id="billing-material{{ $phase->id }}" role="tabpanel"
                                                aria-labelledby="billing-material-tab{{ $phase->id }}">
                                                <!-- Content for Construction Billing Material Tab -->
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th> Image </th>
                                                            <th> Name </th>
                                                            <th> Item Name </th>
                                                            <th> Price </th>
                                                            <th>
                                                                Edit
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- Construction Material --}}

                                                        @if (count($phase->constructionMaterialBillings))
                                                            @foreach ($phase->constructionMaterialBillings as $construction_material_billing)
                                                                <tr>

                                                                    <td>{{ $construction_material_billing->created_at->format('d-M-Y') }}</td>
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

                                                                    <td class="space-x-4">
                                                                        <a
                                                                            href="{{ route('construction-material-billings.edit', [base64_encode($construction_material_billing->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>




                                                                    </td>


                                                                </tr>

                                                                @if ($loop->last)
                                                                    <tr class="">
                                                                        <td colspan="3"
                                                                            class="text-right font-bold bg-info text-white fw-bold">
                                                                            Total Cost + Cost:</td>

                                                                        <td colspan="2"
                                                                            class="font-bold bg-info text-white fw-bold">
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

                                            <div class="tab-pane fade" id="daily-attendance{{ $phase->id }}"
                                                role="tabpanel"
                                                aria-labelledby="daily-attendance-tab{{ $phase->id }}">
                                                <!-- Content for Daily Attendance Tab -->
                                                <table class="table">

                                                    <thead>

                                                        <tr>

                                                            <th>Date</th>

                                                            <th> Image </th>

                                                            <th> Wager type </th>

                                                            <th>Supplier Name</th>

                                                            <th> Type </th>

                                                            <th> Price </th>

                                                            <th>Multiplier</th>

                                                            <th>
                                                                Total Price
                                                            </th>

                                                            <th>
                                                                Edit
                                                            </th>

                                                        </tr>

                                                    </thead>

                                                    <tbody>


                                                        {{-- Square Footage --}}



                                                        @if (count($phase->squareFootageBills))
                                                            @foreach ($phase->squareFootageBills as $sqft)
                                                                <tr>

                                                                    <td>{{ $sqft->created_at->format('d-M-Y') }}</td>
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

                                                                    <td>
                                                                        <a
                                                                            href="{{ route('square-footage-bills.edit', [base64_encode($sqft->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @if ($loop->last)
                                                                    <tr class="">
                                                                        <td colspan="6"
                                                                            class="text-right font-bold bg-info text-white fw-bold">

                                                                            Total Amount + Cost
                                                                        </td>

                                                                        <td colspan="2"
                                                                            class="font-bold bg-info text-white fw-bold">
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
                                                                <td colspan="5" class="text-center text-danger">
                                                                    No
                                                                    Records Found</td>
                                                            </tr>
                                                        @endif



                                                    </tbody>


                                                </table>
                                            </div>

                                            <div class="tab-pane fade" id="daily-expenses{{ $phase->id }}"
                                                role="tabpanel"
                                                aria-labelledby="daily-expenses-tab{{ $phase->id }}">
                                                <!-- Content for Wager Tab -->


                                                <table class="table ">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th> Wager Name </th>
                                                            <th> Price Per Day </th>
                                                            <th>
                                                                Edit
                                                            </th>


                                                        </tr>
                                                    </thead>
                                                    <tbody>


                                                        {{-- Square Footage --}}


                                                        @if (count($phase->dailyWagers))
                                                            @foreach ($phase->dailyWagers as $daily_wager)
                                                                <tr>

                                                                    <td>{{ $daily_wager->created_at->format('d-M-Y') }}</td>

                                                                    <td>
                                                                        {{ ucwords($daily_wager->wager_name) }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $daily_wager->price_per_day }}
                                                                    </td>
                                                                    <td>
                                                                        <a
                                                                            href="{{ route('dailywager.edit', [base64_encode($daily_wager->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                @if ($loop->last)
                                                                    <tr class="">
                                                                        <td colspan="1"
                                                                            class="text-right font-bold bg-info text-white fw-bold">
                                                                            Total Amount + Cost</td>
                                                                        <td colspan="2"
                                                                            class="text-right font-bold bg-info text-white fw-bold">
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

                                            <div class="tab-pane fade" id="square-footage{{ $phase->id }}"
                                                role="tabpanel"
                                                aria-labelledby="square-footage-tab{{ $phase->id }}">
                                                <!-- Daily Expenses Tab -->
                                                <table class="table ">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th> Item Name </th>
                                                            <th> Total Price </th>
                                                            <th>
                                                                Edit
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>


                                                        {{-- Square Footage --}}



                                                        @if (count($phase->dailyExpenses))
                                                            @foreach ($phase->dailyExpenses as $daily_expenses)
                                                                <tr>

                                                                    <td>{{ $daily_expenses->created_at->format('d-M-Y') }}</td>

                                                                    <td>
                                                                        {{ ucwords($daily_expenses->item_name) }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $daily_expenses->price }}
                                                                    </td>

                                                                    <td>
                                                                        <a
                                                                            href="{{ route('daily-expenses.edit', [base64_encode($daily_expenses->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>
                                                                    </td>

                                                                </tr>
                                                                @if ($loop->last)
                                                                    <tr class="">
                                                                        <td colspan="1"
                                                                            class="text-right font-bold bg-info text-white fw-bold">
                                                                            Total Amount:</td>
                                                                        <td colspan="2"
                                                                            class="font-bold bg-info text-white fw-bold">
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

                                            <div class="tab-pane fade" id="wager-attendance{{ $phase->id }}"
                                                role="tabpanel"
                                                aria-labelledby="wager-attendance{{ $phase->id }}">
                                                <!-- Daily Expenses Tab -->
                                                <table class="table ">
                                                    <thead>
                                                        <tr>

                                                            <th>Date</th>
                                                            <th> No Of Persons </th>
                                                            <th>Wager Name </th>
                                                            <th>
                                                                Supplier
                                                            </th>
                                                            <th>Date</th>
                                                            <th>
                                                                Edit
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        {{-- Square Footage --}}


                                                        @if (count($phase->wagerAttendances))
                                                            @foreach ($phase->wagerAttendances as $wager_attendance)
                                                                <tr aria-colspan="4">


                                                                    <td>{{ $wager_attendance->created_at->format('d-M-Y') }}</td>
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


                                                                    <td>
                                                                        <a
                                                                            href="{{ route('dailywager.edit', [base64_encode($wager_attendance->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>
                                                                    </td>


                                                                </tr>
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


                                            <div class="tab-pane fade" id="commulative-cost{{ $phase->id }}"
                                                role="tabpanel"
                                                aria-labelledby="commulative-cost-tab{{ $phase->id }}">
                                                <!-- Daily Expenses Tab -->
                                                <table class="table ">
                                                    <thead>
                                                        <tr>
                                                            <th>..</th>
                                                            <th>Amount</th>
                                                            <th> Service Charge {{ $site->service_charge }}%</th>
                                                            <th> Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            {{-- <td>
                                                                Service Charge + Total Amount
                                                            </td> --}}

                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                Raw Materials
                                                            </td>
                                                            <td>
                                                                {{ $phase->construction_total_amount }}
                                                            </td>
                                                            <td>
                                                                {{ ($site->service_charge / 100) * $phase->construction_total_amount }}
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
                                                                {{ ($site->service_charge / 100) * $phase->square_footage_total_amount }}
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
                                                                {{ ($site->service_charge / 100) * $phase->daily_expenses_total_amount }}
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
                                                                {{ ($site->service_charge / 100) * $phase->daily_wagers_total_amount }}
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

                                        </div>
                                    </div>
                                </div>


                            </div>



                        </div>


                    </div>

                </div>
            @endforeach
        @else
            <h3 class="text-danger">
                No Records Found...
            </h3>
        @endif
    @else
        <h1>
            No site Record Awailable
        </h1>
    @endif

</x-app-layout>
