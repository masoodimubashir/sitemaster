<x-app-layout>




    {{-- Modal --}}
    <style>
        /* Styles for the modal */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.7);

            /* Black w/ opacity */
        }

        /* .modal-content input[type="text"],
        .modal-content input[type="number"] {
            width: 100%;
            border: 0;
            outline: 1px solid #dee2e6;
            font-weight: 400;
            border-radius: 4px;
            display: block;
            background: none;
            font-size: 0.875rem;
            border-width: 0;
            border-color: transparent;
            line-height: 1.9;
            -webkit-transition: all 0.28s ease;
            transition: all 0.28s ease;
            box-shadow: none;
        } */

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: 50% auto;
            /* Centered on the screen */
            padding: 15px;
            width: 90%;
            /* Default width for mobile */
            max-width: 600px;
            /* Max width for larger screens */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }




        /* Responsive design */
        @media screen and (min-width: 768px) {
            .modal-content {
                margin: 10% auto;
                /* Adjust margins for larger screens */
                width: 80%;
                /* Slightly wider on larger screens */
            }
        }

        @media screen and (min-width: 992px) {
            .modal-content {
                margin: 15% auto;
                /* Further adjustment for very large screens */
                width: 60%;
                /* Even wider on very large screens */
            }
        }
    </style>


    <style>
        /* Accordion container */
        .accordion {
            margin: 0 auto;
        }

        /* Accordion item */
        .accordion-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        /* Accordion header */
        .accordion-header {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            display: block;
            outline: none;
            border-radius: 4px;
        }

        /* Accordion content */
        .accordion-content {
            display: none;
            /* Hidden by default */
            padding: 15px;
            background-color: #f1f1f1;
            border-top: 1px solid #ddd;
        }

        /* Transition for smooth expansion */
        .accordion-content {
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-content.show {
            display: block;
            max-height: 500px;
            /* Arbitrary large value */
        }

          #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 999;
        }
    </style>


    @if ($site)


        <div class="row">
            <div class="col-12 text-center mb-3"> <!-- Center align buttons -->
                <div class="d-flex flex-wrap"> <!-- Use flex for responsive layout -->
                    <button class="btn btn-info btns mx-2 mb-2" data-modal="phase">
                        Phase
                    </button>

                    <button class="btn btn-info btns mx-2 mb-2" data-modal="payment-supplier">
                        Supplier Payment
                    </button>

                    <a href="{{ url('user/site/payments', [$site->id]) }}" class="btn btn-info mx-2 mb-2">
                        View Payments
                    </a>

                    <a href="{{ url('user/site/ledger', $site->id) }}" class="btn btn-info mx-2 mb-2">
                        View Ledger
                    </a>

                    <a href="{{ url('user/download-site/report', ['id' => base64_encode($site->id)]) }}"
                        class="btn btn-info mx-2 mb-2">
                        Download Site PDF
                    </a>

                    <a href="{{ url('user/site-payment/report', ['id' => base64_encode($site->id)]) }}"
                        class="btn btn-info mx-2 mb-2">
                        Generate Site Payments
                    </a>
                </div>
            </div>
        </div>




        <div class="row mt-4">

            <div class=" col-12 col-md-6 col-lg-3 mb-1"> <!-- 2 on small, 3 on medium, 4 on large -->
                <x-general-detail>
                    <i class=" display-4 fa fa-building text-info me-2 fw-bold"></i>
                    {{ ucwords($site->site_name) }}
                </x-general-detail>

                <x-general-detail>
                    <i class=" display-4 fa fa-user-circle text-info me-2 fw-bold"></i>
                    {{ ucwords($site->site_owner_name) }}
                </x-general-detail>
            </div>

            <div class=" col-12 col-md-6 col-lg-3 mb-1">
                <x-general-detail>
                    <i class=" display-4 fa fa-mobile text-info me-2 fw-bold"></i>
                    <a href="tel:+91-{{ $site->contact_no }}">91-{{ ucwords($site->contact_no) }}</a>
                </x-general-detail>
                <x-general-detail>
                    <i class=" display-4 fa fa-map-marker text-info me-2 fw-bold"></i>
                    {{ ucwords($site->location) }}
                </x-general-detail>
            </div>

            <div class=" col-12 col-md-6 col-lg-3 mb-1">
                <x-general-detail>
                    <i class=" display-4 fa fa-percent text-info me-2 fw-bold"></i>
                    Service Charge: {{ $site->service_charge }}
                </x-general-detail>
                <x-general-detail>
                    <i class=" display-4 fa fa-money text-info me-2 fw-bold"></i>
                    Debit:
                    @php
                        $service_charge_total_amount =
                            ($site->service_charge / 100) * $grand_total_amount + $grand_total_amount;
                    @endphp
                    {{ Number::currency($service_charge_total_amount, 'INR') }}
                </x-general-detail>
            </div>

            <div class=" col-12 col-md-6 col-lg-3 mb-1">


                @php
                    $balance = $service_charge_total_amount - $totalPaymentSuppliersAmount;
                @endphp

                <x-general-detail :balance="$balance">
                    <i
                        class=" display-4 fa fa-balance-scale  me-2 {{ $balance >= 0 ? 'text-info' : 'text-danger' }} fw-bold"></i>
                    Balance:
                    {{ Number::currency($balance, 'INR') }}
                </x-general-detail>

                <x-general-detail>
                    <i class=" display-4 fa fa-credit-card text-info me-2 fw-bold"></i>
                    Credit: {{ Number::currency($totalPaymentSuppliersAmount, 'INR') }}
                </x-general-detail>
            </div>

        </div>







        @if ($site->phases->count() > 0)

            @foreach ($site->phases as $phase)
                <div class="row mt-2">

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
                                                        data-bs-toggle="tab"
                                                        href="#commulative-cost{{ $phase->id }}" role="tab"
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
                                                        aria-selected="false">
                                                        Expenses</a>
                                                </li>
                                                <li class="nav-item">

                                                </li>

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

                                                <li class="nav-item d  dropdown-button">

                                                    <a class="nav-link" style="border:none !important"
                                                        id="daily-expenses-tab{{ $phase->id }}" data-bs-toggle="tab"
                                                        href="#daily-expenses{{ $phase->id }}" role="tab"
                                                        aria-controls="daily-expenses{{ $phase->id }}"
                                                        aria-selected="false">
                                                        Wager
                                                        <i class="fa-solid fa-caret-down"></i>
                                                        <ul class="dropdown-content">
                                                            <li class="btns dropdown-item"
                                                                data-modal="modal-daily-wager{{ $phase->id }}">
                                                                Create Wager
                                                            </li>
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

                                                <a href="{{ url('user/download-phase/report', ['id' => base64_encode($phase->id)]) }}"
                                                    class="btn btn-info btn-sm text-white">
                                                    Generate Phase PDF
                                                </a>

                                                <a href="#" class="btn btn-inverse-success btn-sm fw-bold">
                                                    Phase Total:
                                                    <i class="fa fa-indian-rupee"></i>
                                                    {{ ($site->service_charge / 100) * $phase->total_amount + $phase->total_amount }}
                                                </a>

                                                <button class="btn btn-sm btn-info text-white dropdown-toggle"
                                                    type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    Make Entry
                                                </button>

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

                                                                    <td class="space-x-4">

                                                                        <a
                                                                            href="{{ route('construction-material-billings.edit', [base64_encode($construction_material_billing->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1">

                                                                            </i>
                                                                        </a>

                                                                    </td>


                                                                </tr>

                                                                @if ($loop->last)
                                                                    <tr class="">
                                                                        <td colspan="4"
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
                                                                <td colspan="6" class="text-center text-danger">No
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
                                                <table class="table ">
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

                                                                    <td>{{ $sqft->created_at->format('d-M-Y') }}
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
                                                                        <td colspan="7"
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
                                                                <td colspan="9" class="text-center  text-danger">
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

                                                                    <td>
                                                                        {{ $daily_wager->created_at->format('d-M-Y') }}

                                                                    </td>

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
                                                                        <td colspan="2"
                                                                            class="text-right font-bold bg-info text-white fw-bold">
                                                                            Total Amount + Cost</td>
                                                                        <td colspan="2"
                                                                            class=" font-bold bg-info text-white fw-bold">
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
                                                                <td colspan="4" class="text-center text-danger">
                                                                    No Records Found
                                                                </td>
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


                                                                    <td>{{ $daily_expenses->created_at->format('d-M-Y') }}
                                                                    </td>

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
                                                                        <td colspan="2"
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


                                                                    <td>{{ $wager_attendance->created_at->format('d-M-Y') }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $wager_attendance->no_of_persons }}
                                                                    </td>

                                                                    <td>
                                                                        {{ ucwords($wager_attendance->dailyWager->wager_name) }}
                                                                    </td>

                                                                    <td>
                                                                        {{ ucwords($wager_attendance->dailyWager->supplier->name ?? '') }}
                                                                    </td>


                                                                    <td>
                                                                        <a
                                                                            href="{{ route('daily-expenses.edit', [base64_encode($wager_attendance->id)]) }}">
                                                                            <i
                                                                                class="fa-regular fa-pen-to-square text-xl bg-white rounded-full px-2 py-1"></i>
                                                                        </a>
                                                                    </td>


                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="5" class="text-center  text-danger">
                                                                    No Records Found
                                                                </td>
                                                            </tr>
                                                        @endif


                                                        <tr>
                                                            <form class="forms-sample material-form"
                                                                id="wagerAttendance">

                                                                @csrf

                                                                <td>
                                                                    <!-- No Of Persons -->
                                                                    <div style="">
                                                                        <input id="no_of_persons" type="number"
                                                                            name="no_of_persons"
                                                                            placeholder="No Of Persons"
                                                                            style="width: 100%; border: 0; outline: 1px solid #dee2e6; border-radius: 5px;" />

                                                                        @error('no_of_persons')
                                                                            <x-input-error :messages="$message"
                                                                                class="mt-2" />
                                                                        @enderror
                                                                    </div>

                                                                </td>

                                                                <td>
                                                                    <!-- Wager -->
                                                                    <select class="form-select form-select-sm"
                                                                        id="daily_wager_id" name="daily_wager_id">
                                                                        <option value="">Select Wager
                                                                        </option>

                                                                        @foreach ($wagers as $wager)
                                                                            <option value="{{ $wager->id }}">
                                                                                {{ $wager->wager_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('daily_wager_id')
                                                                        <x-input-error :messages="$message" class="mt-2" />
                                                                    @enderror
                                                                </td>

                                                                <td>
                                                                    <input id="phase_id" type="hidden"
                                                                        name="phase_id" placeholder="Phase"
                                                                        value="{{ $phase->id }}" />
                                                                    @error('phase_id')
                                                                        <x-input-error :messages="$message" class="mt-2" />
                                                                    @enderror

                                                                    <x-primary-button>
                                                                        {{ __('Make Attendance') }}
                                                                    </x-primary-button>


                                                                </td>


                                                            </form>
                                                        </tr>

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
                                                                ....
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

                                        </div>
                                    </div>
                                </div>



                                <div>

                                    <!-- Modal 1 -->
                                    <div id="modal-construction-billings{{ $phase->id }}" class="modal">
                                        <div class="modal-content">

                                            {{-- Create Construction Billings Here --}}
                                            <form enctype="multipart/form-data" class="forms-sample material-form"
                                                id="constructionBillingForm">

                                                @csrf


                                                <!-- Amount -->
                                                <div class="form-group">
                                                    <input id="amount" type="number" name="amount"
                                                        placeholder="Material Price" />

                                                    @error('amount')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <div class="row">
                                                    <!-- Item Name -->
                                                    <div class="col-md-6">


                                                        <select class="form-select form-select-sm"
                                                            id="exampleFormControlSelect3" name="item_name">
                                                            <option value="">Select Item
                                                            </option>
                                                            @foreach ($items as $item)
                                                                <option value="{{ $item->item_name }}">
                                                                    {{ $item->item_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('item_name')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Supplier -->
                                                    <div class="col-md-6">
                                                        <select class="form-select form-select-sm"
                                                            id="exampleFormControlSelect3" name="supplier_id">
                                                            <option value="">Select Supplier
                                                            </option>
                                                            @foreach ($raw_material_providers as $supplier)
                                                                <option value="{{ $supplier->id }}">
                                                                    {{ $supplier->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('supplier_id')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Phases -->
                                                    <div class=" col-md-6 mt-3">
                                                        <input id="phase_id" type="hidden" name="phase_id"
                                                            placeholder="Phase" value="{{ $phase->id }}" />
                                                        @error('phase_id')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Item Bill Photo -->
                                                <div class="mt-3">
                                                    <input class="form-control form-control-md" id="image"
                                                        type="file" name="image">
                                                </div>

                                                <div class="flex items-center justify-end mt-4">
                                                    <x-primary-button class="ms-4">
                                                        {{ __('Create Billing') }}
                                                    </x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>


                                    <!-- Modal 2 -->
                                    <div id="modal-square-footage-bills{{ $phase->id }}" class="modal">

                                        <div class="modal-content">
                                            {{-- Create Square Footage Bills --}}
                                            <div>
                                                <form id="squareFootageBills" enctype="multipart/form-data"
                                                    class="forms-sample material-form">


                                                    @csrf

                                                    <!-- Wager Name -->
                                                    <div class="form-group">
                                                        <input id="wager_name" type="text" name="wager_name" />
                                                        <label for="wager_name" class="control-label" />Work
                                                        Type</label><i class="bar"></i>

                                                        @error('wager_name')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Price -->
                                                    <div class="form-group">
                                                        <input id="price" type="number" name="price" />
                                                        <label for="price" class="control-label" />Price</label><i
                                                            class="bar"></i>

                                                        @error('price')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Number Of Days -->
                                                    <div class="form-group">
                                                        <input id="multiplier" type="number" name="multiplier" />
                                                        <label for="multiplier"
                                                            class="control-label">Multiplier</label><i
                                                            class="bar"></i>

                                                        @error('multiplier')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>



                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            <!-- Type -->
                                                            <select class="form-select form-select-sm"
                                                                id="exampleFormControlSelect3" name="type">
                                                                <option value="">Select Type</option>
                                                                <option value="per_sqr_ft">Per Square Feet</option>
                                                                <option value="per_unit">Per Unit</option>
                                                                <option value="full_contract">Full Contract
                                                                </option>
                                                            </select>
                                                            @error('type')
                                                                <x-input-error :messages="$message" class="mt-2" />
                                                            @enderror
                                                        </div>

                                                        <div class="col-md-6">
                                                            <!-- Select Supplier -->
                                                            <select class="form-select form-select-sm"
                                                                id="supplier_id" name="supplier_id">
                                                                <option value="">Select Supplier</option>
                                                                @foreach ($workforce_suppliers as $supplier)
                                                                    <option value="{{ $supplier->id }}">
                                                                        {{ $supplier->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('supplier_id')
                                                                <x-input-error :messages="$message" class="mt-2" />
                                                            @enderror
                                                        </div>

                                                        <div class=" col-md-6 mt-3">
                                                            <input id="phase_id" type="hidden" name="phase_id"
                                                                placeholder="Phase" value="{{ $phase->id }}" />
                                                            @error('phase_id')
                                                                <x-input-error :messages="$message" class="mt-2" />
                                                            @enderror
                                                        </div>
                                                    </div>


                                                    <!-- Image -->
                                                    <div class="mt-3">
                                                        <label for="image">Item Bill</label>
                                                        <input class="form-control form-control-md" id="image"
                                                            type="file" name="image_path">
                                                        @error('image_path')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <div class="flex items-center justify-end mt-4">

                                                        <x-primary-button class="ms-4">
                                                            {{ __('Create Bill') }}
                                                        </x-primary-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal 3 -->
                                    <div id="modal-daily-wager{{ $phase->id }}" class="modal">
                                        <div class="modal-content">

                                            <form class="forms-sample material-form" id="dailyWager">

                                                @csrf

                                                <!-- Wager Name -->
                                                <div class="form-group">
                                                    <input id="wager_name" type="text" name="wager_name" />
                                                    <label for="wager_name" class="control-label">Wager
                                                        Name</label><i class="bar"></i>

                                                    @error('wager_name')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <!-- Price Per day -->
                                                <div class="form-group">
                                                    <input id="price_per_day" type="number" name="price_per_day" />
                                                    <label for="price_per_day" class="control-label">Price Per
                                                        Day</label><i class="bar"></i>

                                                    @error('price_per_day')
                                                        <x-input-error :messages="$message" class="mt-2" />
                                                    @enderror
                                                </div>

                                                <div class="row">
                                                    <!-- Select Supplier -->
                                                    <div class="">
                                                        <select class="form-select form-select-sm" id="supplier_id"
                                                            name="supplier_id">
                                                            <option value="">Select Supplier</option>
                                                            @foreach ($workforce_suppliers as $supplier)
                                                                <option value="{{ $supplier->id }}">
                                                                    {{ $supplier->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('supplier_id')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Select Phase -->
                                                    <div class=" col-md-6 mt-3">
                                                        <input id="phase_id" type="hidden" name="phase_id"
                                                            placeholder="Phase" value="{{ $phase->id }}" />
                                                        @error('phase_id')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>
                                                </div>



                                                <div class="flex items-center justify-end mt-4">



                                                    <x-primary-button>
                                                        {{ __('Create Wager') }}
                                                    </x-primary-button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Modal 4 -->
                                    <div id="modal-daily-expenses{{ $phase->id }}" class="modal">
                                        <div class="modal-content">
                                            {{-- Daily Expenses  --}}

                                            <div>
                                                <form id="dailyExpenses" class="forms-sample material-form">

                                                    @csrf

                                                    <!-- Wager Name -->
                                                    <div class="form-group">
                                                        <input id="item_name" type="text" name="item_name" />
                                                        <label for="item_name" class="control-label">Item
                                                            Name</label><i class="bar"></i>
                                                        @error('item_name')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Price -->
                                                    <div class="form-group">
                                                        <input id="price" type="number" name="price" />
                                                        <label for="price" class="control-label">Price</label><i
                                                            class="bar"></i>
                                                        @error('price')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <!-- Select Phase -->
                                                    <div class=" col-md-6 mt-3">
                                                        <input id="phase_id" type="hidden" name="phase_id"
                                                            placeholder="Phase" value="{{ $phase->id }}" />
                                                        @error('phase_id')
                                                            <x-input-error :messages="$message" class="mt-2" />
                                                        @enderror
                                                    </div>

                                                    <div class="flex items-center justify-end mt-4">

                                                        <x-primary-button class="ms-4">
                                                            {{ __('Create Bill') }}
                                                        </x-primary-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Model 5 --}}
                                    <div id="modal-daily-wager-attendance{{ $phase->id }}" class="modal">
                                        <div class="modal-content">

                                            <div>

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



        {{-- Phase Form --}}
        <div id="phase" class="modal">

            <div class="modal-content">

                <form class="forms-sample material-form" id="phaseForm">

                    @csrf

                    {{-- Phase Name --}}
                    <div class="form-group">
                        <input type="text" name="phase_name" id="phase_name" />
                        <label for="phase_name" class="control-label">Phase Name</label>
                        <i class="bar"></i>
                        <x-input-error :messages="$errors->get('phase_name')" class="mt-2" />
                    </div>

                    <!-- Site -->
                    <div class="form-group">
                        <input type="hidden" name="site_id" value="{{ $site->id }}" />
                        <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Create Phase') }}
                        </x-primary-button>
                    </div>

                </form>

            </div>

        </div>



        {{-- Payment Supplier --}}
        <div id="payment-supplier" class="modal">

            <div class="modal-content">

                <form id="payment_supplierForm" class="forms-sample material-form"  enctype="multipart/form-data">

                    @csrf

                    {{-- Phase Name --}}
                    <div class="form-group">
                        <input type="number" min="0" name="amount" />
                        <label for="input" class="control-label">Amount</label><i class="bar"></i>
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <!-- Site -->
                    <div class="form-group">
                        <input type="hidden" name="site_id" value="{{ $site->id }}" />
                        <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                    </div>

                    {{-- Supplier --}}

                    <select class="form-select form-select-sm" id="supplier_id" name="supplier_id">
                        <option value="">Select Supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror


                    <!-- Is Verified -->
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="is_verified"> Verify
                        </label>
                        @error('is_verified')
                            <x-input-error :messages="$message" class="mt-2" />
                        @enderror
                    </div>

                    {{-- Screenshot --}}
                    <div class="mt-3">
                        <input class="form-control form-control-md" id="image" type="file" name="screenshot">
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Pay') }}
                        </x-primary-button>
                    </div>


                </form>

            </div>
        </div>

    <div id="messageContainer"> </div>




    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get all buttons with class 'btn'
            const buttons = document.querySelectorAll('.btns');

            // Add click event listeners to each button
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    // Get the modal ID from the button's data attribute
                    const modalId = button.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);

                    // Show the modal
                    if (modal) {
                        modal.style.display = 'block';
                    }
                });
            });

            // Close modals when the close button is clicked
            const closeButtons = document.querySelectorAll('.modal .close');
            closeButtons.forEach(closeButton => {
                closeButton.addEventListener('click', () => {
                    const modal = closeButton.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Close modals when clicking outside the modal content
            window.addEventListener('click', (event) => {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });
        });

        // Get all accordion headers
        var headers = document.querySelectorAll('.accordion-header');

        // Add click event listener to each header
        headers.forEach(header => {
            header.addEventListener('click', function() {
                // Toggle the active class on the clicked header
                this.classList.toggle('active');

                // Get the content panel associated with the clicked header
                var content = this.nextElementSibling;

                // If the content panel is open, close it
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    content.classList.remove('show');
                } else {
                    // Otherwise, close all other content panels
                    document.querySelectorAll('.accordion-content').forEach(panel => {
                        panel.style.display = 'none';
                        panel.classList.remove('show');
                    });

                    // Open the clicked content panel
                    content.style.display = 'block';
                    content.classList.add('show');
                }
            });
        });
    </script>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function() {

            $('form[id="phaseForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove(); // Clear previous error messages

                $.ajax({
                    url: '{{ url('user/user-phase') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });

            //  Script For Construction Form
            $('form[id^="constructionBillingForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/construction-material-billings') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reolord();

                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });

            // Script For square Footage Bills
            $('form[id^="squareFootageBills"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/user-square-footage-bills') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });

            $('form[id^="dailyWager"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/user-daily-wager') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });

            $('form[id^="wagerAttendance"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/user-wager-attendance') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        location.reload();
                    },
                    error: function(xhr) {

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                // Display errors next to the respective input fields
                                $(`[name="${key}"]`).after(
                                    `<p class="text-danger mt-2">${value[0]}</p>`);

                            });
                        }
                    }
                });
            });

            $('form[id^="dailyExpenses"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').text('');

                $.ajax({
                    url: '{{ url('user/user-daily-expenses') }} ',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();

                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });


            $('form[id="payment_supplierForm"]').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove(); // Clear previous error messages

                $.ajax({
                    url: '{{ url('user/site/payments') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                        <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.relord();
                        }, 3000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                        ${response.responseJSON.errors}

                        </div>`)

                        } else {
                            messageContainer.append(`
                        <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                            An unexpected error occurred. Please try again later.

                        </div>
                    `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 5000);
                    }
                });
            });


        });
    </script>


</x-app-layout>





{{-- <form id="delete-form-{{ $construction_material_billing->id }}"
                                                                        action="{{ route('construction-material-billings.destroy', [ base64_encode($construction_material_billing->id) ]) }}"
                                                                        method="POST" style="display: none;">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                    </form>
                                                                    <a href="#"
                                                                        onclick="event.preventDefault(); if (confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-form-{{ $construction_material_billing->id }}').submit();">
                                                                        <i
                                                                            class="fa-solid fa-trash-o text-xl text-red-600 bg-white rounded-full px-2 py-1"></i>
                                                                    </a> --}}
