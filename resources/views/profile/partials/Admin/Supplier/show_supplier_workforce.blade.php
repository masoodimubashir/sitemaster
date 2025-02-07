<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp




    {{-- Accordian --}}
    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }

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
    </style>


    <x-breadcrumb :names="['Suppliers', $supplier->name]"
                  :urls="[$user . '/suppliers', $user . '/suppliers/' . $supplier->id]"/>


    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-start gap-2">

                <button class="btn btn-info btns" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Make Payment
                </button>


                <a href="{{ url($user . '/supplier/ledger', [$supplier->id]) }}" class="btn btn-info btns"
                   data-modal="payment-supplier">
                    View Ledger
                </a>

                <a href="{{ url($user . '/supplier-payment/report', ['id' => base64_encode($supplier->id)]) }}"
                   class="btn btn-info">
                    Generate Payment Report
                </a>

                <a href="{{ url($user . '/supplier/payments', [$supplier->id]) }}" class="btn btn-info btns"
                   data-modal="payment-supplier">
                    View Payments
                </a>

                @if ($user === 'admin')
                    <a href="{{ url($user . '/unverified-supplier-payments/' . $supplier->id) }}" class="btn btn-info">
                        Unverified Payments
                    </a>
                @endif


            </div>
        </div>
    </div>


    <div class="row g-4">
        <div class="col-12 col-md-4 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">

                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-user text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Supplier</h6>
                            {{ ucfirst($supplier->name) }}

                        </div>
                    </div>


                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">

                            <i class="fa-solid fa-phone text-info fs-3  p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">

                                <a href="tel:+91-{{ $supplier->contact_no }}"
                                   class="text-decoration-none">91-{{ ucfirst($supplier->contact_no) }}</a>
                            </h5>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-map-marker-alt text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            {{ ucfirst($supplier->address) }}

                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-money-bill text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Debit</h6>
                            {{ Number::currency($totalDebit ?? 0, 'INR') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-{{ $balance >= 0 ? '' : '' }} fs-3 p-2">
                            <i class="fas fa-balance-scale text-{{ $balance >= 0 ? 'info' : 'danger' }}"></i>
                        </div>
                        <div>
                            <h6 class="text-{{ $balance >= 0 ? 'info' : 'danger' }} mb-1">Balance</h6>
                            <h5 class="mb-0 text-{{ $balance >= 0 ? 'info' : 'danger' }}">
                                {{ Number::currency($balance, 'INR') }}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" fs-3 p-2">
                            <i class="fas fa-credit-card text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Credit</h6>
                            {{ Number::currency($totalCredit ?? 0, 'INR') }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (count($data) >= 0)

        <div class="row ">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card-body ">

                    <div class="table-responsive mt-4">

                        <!-- Content for Construction Billing Material Tab -->
                        <table class="table table-bordered">

                            <thead>

                            <tr>

                                <th class="bg-info fw-bold text-white">
                                    Date
                                </th>

                                <th class="bg-info fw-bold text-white"> Bill Proof</th>

                                <th class="bg-info fw-bold text-white"> Item</th>

                                {{--                                    <th class="bg-info fw-bold text-white">--}}
                                {{--                                        Site Name--}}
                                {{--                                    </th>--}}

                                {{--                                    <th class="bg-info fw-bold text-white">--}}
                                {{--                                        Site Owner--}}
                                {{--                                    </th>--}}


                                <th class="bg-info fw-bold text-white">
                                    Price Per Unit
                                </th>

                                <th class="bg-info fw-bold text-white">
                                    Total Amount
                                </th>

                            </tr>

                            </thead>

                            <tbody>

                            @if (count($data) > 0)
                                @foreach ($data as $d)
                                    <tr>

                                        <td>
                                            {{ $d['created_at'] }}
                                        </td>

                                        <td>

                                            @if ($d['image'] !== null)
                                                <img src="{{ asset('storage/' . $d['image']) }}" alt="">
                                            @else
                                                NA
                                            @endif

                                        </td>

                                        <td>
                                            {{ $d['item'] }}
                                        </td>


                                        {{--                                            <td>--}}
                                        {{--                                                {{ $d['site'] }}--}}
                                        {{--                                            </td>--}}

                                        {{--                                            <td>--}}
                                        {{--                                                {{ $d['site_owner'] }}--}}
                                        {{--                                            </td>--}}

                                        <td>
                                            {{ $d['price_per_unit'] }}
                                        </td>

                                        <td>
                                            {{ $d['total_price'] }}
                                        </td>


                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-danger fw-bold text-center">No Records Found...
                                    </td>
                                </tr>
                            @endif


                            </tbody>

                        </table>

                    </div>

                </div>


            </div>

        </div>

    @endif


    <div id="messageContainer">

    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">


                    <form id="payment_form" class="forms-sample material-form" enctype="multipart/form-data">
                        @csrf

                        {{-- Amount Input --}}
                        <div class="form-group">
                            <input type="number" min="0" name="amount"/>
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2"/>
                        </div>

                        {{-- Hidden Supplier ID --}}
                        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}"/>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-2"/>

                        {{-- Dropdown to Select Payee --}}
                        <select name="payment_initiator" id="payment_initiator" class="form-select text-black form-select-sm" onchange="togglePayeeOptions()">
                            <option value="" selected>Select Payee</option>
                            <option value="0">Pay Admin</option>
                            <option value="1">Pay Site</option>
                        </select>

                        {{-- Options for Paying to Admin --}}
                        <div id="adminOptions" style="display: none;" class="mt-4">
                            {{-- Sent or Received Radio Options --}}
                            <div class="row g-3">
                                <div class="col-auto">
                                    <label for="sent">
                                        <input type="radio" name="transaction_type" id="sent" value="0">
                                        Sent
                                    </label>
                                </div>
                                <div class="col-auto">
                                    <label for="received">
                                        <input type="radio" name="transaction_type" id="received" value="1">
                                        Received
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Options for Paying to a Site --}}
                        <div id="siteOptions" style="display: none;" class="mt-4">
                            <select name="site_id" id="supplier_id" class="form-select text-black form-select-sm">
                                <option value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site['site_id'] }}">
                                        {{$site['site']}} - {{ $site['site_owner'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- File Upload for Screenshot --}}
                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file" name="screenshot">
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Pay') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>


    <div id="messageContainer">

    </div>


    <script>

        function togglePayeeOptions() {
            const payeeSelector = document.getElementById('payment_initiator').value; // Get selected value
            const adminOptions = document.getElementById('adminOptions'); // Admin section
            const siteOptions = document.getElementById('siteOptions'); // Site section

            // Show/Hide options based on the selected value
            if (payeeSelector === "1") {
                adminOptions.style.display = 'none'; // Hide Admin options
                siteOptions.style.display = 'block'; // Show Site options
            } else if (payeeSelector === "0") {
                adminOptions.style.display = 'block'; // Show Admin options
                siteOptions.style.display = 'none'; // Hide Site options
            } else {
                // Hide all sections if "Select Payee" is chosen
                adminOptions.style.display = 'none';
                siteOptions.style.display = 'none';
            }
        }

        $(document).ready(function () {

            $('form[id="payment_form"]').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url($user . '/supplier/payments') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        messageContainer.append(`
                        <div class="alert align-items-center text-white bg-success border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                    `);
                        form[0].reset();

                        setTimeout(function () {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function (response) {
                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                ${response.responseJSON.errors}
                            </div>
                        `);
                        } else {
                            messageContainer.append(`
                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                An unexpected error occurred. Please try again later.
                            </div>
                        `);
                        }

                        setTimeout(function () {
                            messageContainer.find('.alert').alert('close');
                        }, 2000);
                    }
                });
            });

        });
    </script>

</x-app-layout>
